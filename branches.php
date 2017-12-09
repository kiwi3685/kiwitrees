<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2017 kiwitrees.net
 *
 * Derived from webtrees (www.webtrees.net)
 * Copyright (C) 2010 to 2012 webtrees development team
 *
 * Derived from PhpGedView (phpgedview.sourceforge.net)
 * Copyright (C) 2002 to 2010 PGV Development Team
 *
 * Kiwitrees is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

define('KT_SCRIPT_NAME', 'branches.php');
require './includes/session.php';

//-- args
$surn = safe_GET('surname', '[^<>&%{};]*');
$soundex_std = safe_GET_bool('soundex_std');
$soundex_dm = safe_GET_bool('soundex_dm');
$ged = safe_GET('ged');
if (empty($ged)) {
	$ged = $GEDCOM;
}

$user_ancestors=array();
if (KT_USER_GEDCOM_ID) {
	load_ancestors_array(KT_Person::getInstance(KT_USER_GEDCOM_ID), 1);
}

$controller = new KT_Controller_Page();
if ($surn) {
	$controller->setPageTitle(/* I18N: %s is a surname */ KT_I18N::translate('Branches of the %s family', htmlspecialchars($surn)));
} else {
	$controller->setPageTitle(KT_I18N::translate('Branches'));
}
$controller
	->restrictAccess(KT_Module::isActiveList(KT_GED_ID, 'list_branches', KT_USER_ACCESS_LEVEL))
	->pageHeader()
	->addExternalJavascript(KT_JQUERY_TREEVIEW_JS_URL)
	->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
	->addInlineJavascript('
		autocomplete();
		jQuery("#branch-list").treeview({
			collapsed: true,
			animated: "slow",
			control:"#treecontrol"
		});
		jQuery("#branch-list").css("visibility", "visible");
		jQuery(".loading-image").css("display", "none");
	');
?>
<div id="branches-page">
	<h2><?php echo $controller->getPageTitle(); ?></h2>
	<form name="surnlist" id="surnlist" method="get" action="?">
		<div class="chart_options">
			<label for = "SURN"><?php echo KT_Gedcom_Tag::getLabel('SURN'); ?></label>
			<input data-autocomplete-type="SURN" type="text" name="surname" id="SURN" value="<?php echo KT_Filter::escapeHtml($surn); ?>" dir="auto">
		</div>
		<div class="chart_options branches">
			<label><?php echo KT_I18N::translate('Phonetic search'); ?></label>
					<?php
						echo '
							<label class="branches" for="soundex_std">' ,KT_I18N::translate('Russell'), '</label>
							<input type="checkbox" name="soundex_std" id="soundex_std" value="1" ';
								if ($soundex_std) echo ' checked="checked"'; echo '>
							<label for="soundex_dm">' ,KT_I18N::translate('Daitch-Mokotoff'), '</label>
							<input style="margin: auto 10px; width: initial;" type="checkbox" name="soundex_dm" id="soundex_dm" value="1" ';
								if ($soundex_dm) echo ' checked="checked"'; echo '>
						';
					?>
		</div>
		<button class="btn btn-primary show" type="submit">
			<i class="fa fa-eye"></i>
			<?php echo KT_I18N::translate('Show'); ?>
		</button>
	</form>
	<hr style="clear:both;">
	<!-- end of form -->

<?php
//-- results
if ($surn) {
	echo '
		<div id="treecontrol">
			<a href="#">', KT_I18N::translate('Collapse all'), '</a> | <a href="#">', KT_I18N::translate('Expand all'), '</a>
		</div>
		<div class="loading-image"></div>
	';

	$indis = indisArray($surn, $soundex_std, $soundex_dm);
	usort($indis, array('KT_Person', 'CompareBirtDate'));
	echo '<ul id="branch-list">';
	foreach ($indis as $person) {
		$famc = $person->getPrimaryChildFamily();
		// Don't show INDIs with parents in the list, as they will be shown twice.
		if ($famc) {
			foreach ($famc->getSpouses() as $parent) {
				if (in_array($parent, $indis, true)) {
					continue 2;
				}
			}
		}
		print_fams($person);
	}
	echo '</ul>';

}
echo '</div>'; // close branches-page

if (false) {
	// These messages were added (briefly) and translated.
	// Keep them in the translation template, as we will want them in the future
	KT_I18N::translate('Collapse all');
	KT_I18N::translate('Expand all');
}

function print_fams($person, $famid=null) {
	global $surn, $soundex_std, $soundex_dm, $user_ancestors;
	// select person name according to searched surname
	$person_name = "";
	foreach ($person->getAllNames() as $name) {
		list($surn1) = explode(",", $name['sort']);
		if (
			// one name is a substring of the other
			stripos($surn1, $surn)!==false ||
			stripos($surn, $surn1)!==false ||
			// one name sounds like the other
			$soundex_std && KT_Soundex::compare(KT_Soundex::soundex_std($surn1), KT_Soundex::soundex_std($surn)) ||
			$soundex_dm  && KT_Soundex::compare(KT_Soundex::soundex_dm ($surn1), KT_Soundex::soundex_dm ($surn))
		) {
			$person_name = $name['full'];
			break;
		}
	}
	if (empty($person_name)) {
		echo '<li title="', strip_tags($person->getFullName()), '">', $person->getSexImage(), '…</li>';
		return;
	}
	// current indi
	echo '<li>';
	$class = '';
	$sosa = array_search($person->getXref(), $user_ancestors, true);
	if ($sosa) {
		$class = 'search_hit';
		$sosa = '<a target="_blank" rel="noopener noreferrer" dir="ltr" class="details1 '.$person->getBoxStyle().'" title="'.KT_I18N::translate('Sosa').'" href="relationship.php?pid2='.KT_USER_ROOT_ID.'&amp;pid1='.$person->getXref().'">&nbsp;'.$sosa.'&nbsp;</a>'.sosa_gen($sosa);
	}
	$current = $person->getSexImage().
		'<a target="_blank" rel="noopener noreferrer" class="'.$class.'" href="'.$person->getHtmlUrl().'">'.$person_name.'</a> '.
		$person->getLifeSpan().' '.$sosa;
	if ($famid && $person->getChildFamilyPedigree($famid)) {
		$sex = $person->getSex();
		$famcrec = get_sub_record(1, '1 FAMC @'.$famid.'@', $person->getGedcomRecord());
		$pedi = get_gedcom_value('PEDI', 2, $famcrec);
		if ($pedi) {
			$label = KT_Gedcom_Code_Pedi::getValue($pedi, $person);
		}
		$current = '<span class="red">'.$label.'</span> '.$current;
	}
	// spouses and children
	if (count($person->getSpouseFamilies())<1) {
		echo $current;
	}
	foreach ($person->getSpouseFamilies() as $family) {
		$txt = $current;
		$spouse = $family->getSpouse($person);
		if ($spouse) {
			$class = '';
			$sosa2 = array_search($spouse->getXref(), $user_ancestors, true);
			if ($sosa2) {
				$class = 'search_hit';
				$sosa2 = '<a target="_blank" rel="noopener noreferrer" dir="ltr" class="details1 '.$spouse->getBoxStyle().'" title="'.KT_I18N::translate('Sosa').'" href="relationship.php?pid2='.KT_USER_ROOT_ID.'&amp;pid1='.$spouse->getXref().'">&nbsp;'.$sosa2.'&nbsp;</a>'.sosa_gen($sosa2);
			}
			$marriage_year=$family->getMarriageYear();
			if ($marriage_year) {
				$txt .= ' <a href="'.$family->getHtmlUrl().'">';
				$txt .= '<span class="details1" title="'.strip_tags($family->getMarriageDate()->Display()).'"><i class="icon-rings"></i>'.$marriage_year.'</span></a>';
			}
			else if ($family->getMarriage()) {
				$txt .= ' <a href="'.$family->getHtmlUrl().'">';
				$txt .= '<span class="details1" title="'.KT_I18N::translate('yes').'"><i class="icon-rings"></i></span></a>';
			}
		$txt .=
			$spouse->getSexImage().
			' <a class="'.$class.'" href="'.$spouse->getHtmlUrl().'">'.$spouse->getFullName().'</a> '.$spouse->getLifeSpan().' '.$sosa2;
		}
		echo $txt;
		echo '<ul>';
		foreach ($family->getChildren() as $c=>$child) {
			print_fams($child, $family->getXref());
		}
		echo '</ul>';
	}
	echo '</li>';
}

function load_ancestors_array($person, $sosa=1) {
	global $user_ancestors;
	if ($person) {
		$user_ancestors[$sosa]=$person->getXref();
		foreach ($person->getChildFamilies() as $family) {
			foreach ($family->getSpouses() as $parent) {
				load_ancestors_array($parent, $sosa*2+($parent->getSex()=='F'));
			}
		}
	}
}

function indisArray($surn, $soundex_std, $soundex_dm) {
	$sql=
		"SELECT DISTINCT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec".
		" FROM `##individuals`".
		" JOIN `##name` ON (i_id=n_id AND i_file=n_file)".
		" WHERE n_file=?".
		" AND n_type!=?".
		" AND (n_surn=? OR n_surname=?";
	$args=array(KT_GED_ID, '_MARNM', $surn, $surn);
	if ($soundex_std) {
		foreach (explode(':', KT_Soundex::soundex_std($surn)) as $value) {
			$sql .= " OR n_soundex_surn_std LIKE CONCAT('%', ?, '%')";
			$args[]=$value;
		}
	}
	if ($soundex_dm) {
		foreach (explode(':', KT_Soundex::soundex_dm($surn)) as $value) {
			$sql .= " OR n_soundex_surn_dm LIKE CONCAT('%', ?, '%')";
			$args[]=$value;
		}
	}
	$sql .= ')';
	$rows=
		KT_DB::prepare($sql)
		->execute($args)
		->fetchAll(PDO::FETCH_ASSOC);
	$data=array();
	foreach ($rows as $row) {
		$data[]=KT_Person::getInstance($row);
	}
	return $data;
}

function sosa_gen($sosa) {
	$gen = (int)log($sosa, 2)+1;
	return '<sup title="'.KT_I18N::translate('Generation').'">'.$gen.'</sup>';
}
