<?php
// Check a family tree for structural errors.
//
// Note that the tests and error messages are not yet finalised.  Wait until the code has stabilised before
// adding I18N.
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2002-2010 PGV Development Team.
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License or,
// at your discretion, any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
//

define('WT_SCRIPT_NAME', 'admin_trees_sanity.php');
require './includes/session.php';
require WT_ROOT.'includes/functions/functions_edit.php';
require WT_ROOT.'includes/functions/functions_print_facts.php';
global $MAX_ALIVE_AGE;

$controller = new WT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(WT_I18N::translate('Sanity check'))
	->pageHeader();


	// default ages
	$bap_age	= 5;
	$marr_age	= 14;
	$oldage		= $MAX_ALIVE_AGE;
	$spouseage	= 30;
	$child_y	= 15;
	$child_o	= 50;

	if (WT_Filter::postBool('reset')) {
		set_gedcom_setting(WT_GED_ID, 'bap_age', $bap_age);
		set_gedcom_setting(WT_GED_ID, 'marr_age', $marr_age);
		set_gedcom_setting(WT_GED_ID, 'oldage', $oldage);
		set_gedcom_setting(WT_GED_ID, 'spouseage', $spouseage);
		set_gedcom_setting(WT_GED_ID, 'child_y', $child_y);
		set_gedcom_setting(WT_GED_ID, 'child_o', $child_o);

		AddToLog($controller->getPageTitle() .' set to default values', 'config');
	}

	// save new values
	if (WT_Filter::postBool('save')) {
		set_gedcom_setting(WT_GED_ID, 'bap_age',	WT_Filter::post('bap_age', WT_REGEX_INTEGER, $bap_age));
		set_gedcom_setting(WT_GED_ID, 'marr_age',	WT_Filter::post('marr_age', WT_REGEX_INTEGER, $marr_age));
		set_gedcom_setting(WT_GED_ID, 'marr_age',	WT_Filter::post('marr_age', WT_REGEX_INTEGER, $oldage));
		set_gedcom_setting(WT_GED_ID, 'spouseage',	WT_Filter::post('spouseage', WT_REGEX_INTEGER, $spouseage));
		set_gedcom_setting(WT_GED_ID, 'child_y',	WT_Filter::post('child_y', WT_REGEX_INTEGER, $child_y));
		set_gedcom_setting(WT_GED_ID, 'child_o',	WT_Filter::post('child_o', WT_REGEX_INTEGER, $child_o));

		AddToLog($controller->getPageTitle() .' set to new values', 'config');
	}
?>

<div id="sanity_check">
	<a class="current faq_link" href="http://kiwitrees.net/faqs/general/sanity-check/" target="_blank" rel="noopener noreferrer" title="<?php echo WT_I18N::translate('View FAQ for this page.'); ?>"><?php echo WT_I18N::translate('View FAQ for this page.'); ?><i class="fa fa-comments-o"></i></a>
	<h2><?php echo $controller->getPageTitle(); ?></h2>
	<p class="warning">
		<?php echo WT_I18N::translate('This process can be slow. If you have a large family tree or suspect large numbers of errors you should only select a few checks each time.'); ?>
	</p>
	<form method="post" action="<?php echo WT_SCRIPT_NAME; ?>">
		<input type="hidden" name="save" value="1">
		<div class="admin_options">
			<div class="input">
				<label><?php echo WT_I18N::translate('Family tree'); ?></label>
				<?php echo select_edit_control('ged', WT_Tree::getNameList(), null, WT_GEDCOM); ?>
			</div>
		</div>
		<div id="sanity_options">
			<ul>
				<h3><?php echo WT_I18N::translate('Date discrepancies'); ?></h3>
				<li class="facts_value" name="baptised" id="baptised">
					<input type="checkbox" name="baptised" value="baptised"
						<?php if (WT_Filter::post('baptised')) echo ' checked="checked"'?>
					>
					<?php echo WT_I18N::translate('Birth after baptism or christening'); ?>
				</li>
				<li class="facts_value" name="died" id="died">
					<input type="checkbox" name="died" value="died"
						<?php if (WT_Filter::post('died')) echo ' checked="checked"'?>
					>
					<?php echo WT_I18N::translate('Birth after death or burial'); ?>
				</li>
				<li class="facts_value" name="birt_marr" id="birt_marr">
					<input type="checkbox" name="birt_marr" value="birt_marr"
						<?php if (WT_Filter::post('birt_marr')) echo ' checked="checked"'?>
					>
					<?php echo WT_I18N::translate('Birth after marriage'); ?>
				</li>
				<li class="facts_value" name="birt_chil" id="birt_chil">
					<input type="checkbox" name="birt_chil" value="birt_chil"
						<?php if (WT_Filter::post('birt_chil')) echo ' checked="checked"'?>
					>
					<?php echo WT_I18N::translate('Birth after their children'); ?>
				</li>
				<li class="facts_value" name="buri" id="buri">
					<input type="checkbox" name="buri" value="buri"
						<?php if (WT_Filter::post('buri')) echo ' checked="checked"'?>
					>
					<?php echo WT_I18N::translate('Burial before death'); ?>
				</li>
			</ul>
<!-- NEW -->
			<ul>
				<h3><?php echo WT_I18N::translate('Age related queries'); ?></h3>
				<li class="facts_value" name="bap_late" id="bap_late">
					<input type="checkbox" name="bap_late" value="bap_late"
						<?php if (WT_Filter::post('')) echo ' checked="checked"'?>
					>
					<?php echo WT_I18N::translate('Baptised after a certain age'); ?>
					<input name="bap_age" id="bap_age" type="text" value="<?php echo WT_Filter::post('bap_age', WT_REGEX_INTEGER, '5'); ?>" >
				</li>
<div style="opacity: 0.5;">COMING SOON !
				<li class="facts_value" name="marr_yng" id="marr_yng">
					<input type="checkbox" name="marr_yng" value="marr_yng"
						<?php if (WT_Filter::post('')) echo ' checked="checked"'?>
					disabled>
					<?php echo WT_I18N::translate('Married before a certain age'); ?>
					<input name="marr_age" id="marr_age" type="text" value="<?php echo WT_Filter::post('marr_age', WT_REGEX_INTEGER, '14'); ?>" disabled>
				</li>
				<li class="facts_value" name="old_age" id="old_age">
					<input type="checkbox" name="old_age" value="old_age"
						<?php if (WT_Filter::post('')) echo ' checked="checked"'?>
					disabled>
					<?php echo WT_I18N::translate('Alive after a certain age'); ?>
					<input name="oldage" id="oldage" type="text" value="<?php echo WT_Filter::post('oldage', WT_REGEX_INTEGER, $MAX_ALIVE_AGE); ?>"disabled>
				</li>
				<li class="facts_value" name="spouse_age" id="spouse_age">
					<input type="checkbox" name="spouse_age" value="spouse_age"
						<?php if (WT_Filter::post('')) echo ' checked="checked"'?>
					disabled>
					<?php echo WT_I18N::translate('Being much older than spouse'); ?>
					<input name="spouseage" id="spouseage" type="text" value="<?php echo WT_Filter::post('spouseage', WT_REGEX_INTEGER, '30'); ?>"disabled>
				</li>
				<li class="facts_value" name="child_yng" id="child_yng">
					<input type="checkbox" name="child_yng" value="child_yng"
						<?php if (WT_Filter::post('')) echo ' checked="checked"'?>
					disabled>
					<?php echo WT_I18N::translate('Having children before a certain age'); ?>
					<input name="child_y" id="child_y" type="text" value="<?php echo WT_Filter::post('child_y', WT_REGEX_INTEGER, '15'); ?>"disabled>
				</li>
				<li class="facts_value" name="child_old" id="child_old">
					<input type="checkbox" name="child_old" value="child_old"
						<?php if (WT_Filter::post('')) echo ' checked="checked"'?>
					disabled>
					<?php echo WT_I18N::translate('Mothers having children past a certain age'); ?>
					<input name="child_o" id="child_o" type="text" value="<?php echo WT_Filter::post('child_o', WT_REGEX_INTEGER, '50'); ?>"disabled>
				</li>
</div>
			</ul>
<!-- END NEW -->
			<ul>
				<h3><?php echo WT_I18N::translate('Missing data'); ?></h3>
				<li class="facts_value" name="sex" id="sex" >
					<input type="checkbox" name="sex" value="sex"
						<?php if (WT_Filter::post('sex')) echo ' checked="checked"'?>
					>
					<?php echo WT_I18N::translate('No gender recorded'); ?>
				</li>
			</ul>
			<ul>
				<h3><?php echo WT_I18N::translate('Duplicated data'); ?></h3>
				<li class="facts_value" name="dupe_birt" id="dupe_birt" >
					<input type="checkbox" name="dupe_birt" value="dupe_birt"
						<?php if (WT_Filter::post('dupe_birt')) echo ' checked="checked"'?>
					>
					<?php echo WT_I18N::translate('Birth'); ?>
				</li>
				<li class="facts_value" name="dupe_deat" id="dupe_deat" >
					<input type="checkbox" name="dupe_deat" value="dupe_deat"
						<?php if (WT_Filter::post('dupe_deat')) echo ' checked="checked"'?>
					>
					<?php echo WT_I18N::translate('Death'); ?>
				</li>
				<li class="facts_value" name="dupe_buri" id="dupe_buri" >
					<input type="checkbox" name="dupe_buri" value="dupe_buri"
						<?php if (WT_Filter::post('dupe_buri')) echo ' checked="checked"'?>
					>
					<?php echo WT_I18N::translate('Burial'); ?>
				</li>
				<li class="facts_value" name="dupe_sex" id="dupe_sex" >
					<input type="checkbox" name="dupe_sex" value="dupe_sex"
						<?php if (WT_Filter::post('dupe_sex')) echo ' checked="checked"'?>
					>
					<?php echo WT_I18N::translate('Gender'); ?>
				</li>
				<li class="facts_value" name="dupe_name" id="dupe_name" >
					<input type="checkbox" name="dupe_name" value="dupe_name"
						<?php if (WT_Filter::post('dupe_name')) echo ' checked="checked"'?>
					>
					<?php echo WT_I18N::translate('Name'); ?>
				</li>
			</ul>
		</div>
		<button type="submit" class="btn btn-primary clearfloat" >
			<i class="fa fa-check"></i>
			<?php echo $controller->getPageTitle(); ?>
		</button>
	</form>
	<form method="post" name="rela_form" action="#">
		<input type="hidden" name="reset" value="1">
		<button class="btn btn-primary reset" type="submit">
			<i class="fa fa-refresh"></i>
			<?php echo WT_I18N::translate('reset'); ?>
		</button>
	</form>
	<hr class="clearfloat">

	<?php
	if (!WT_Filter::post('save')) {
		exit;
	}
	?>

	<div id="sanity_accordion">
		<?php
			if (WT_Filter::post('baptised')) {
				$data = birth_comparisons(array('BAPM', 'CHR'));
				echo '
					<div class="result">
						<h5>' . WT_I18N::translate('%s born after baptism or christening', $data['count']) . '</h5>
						<div>' . $data['html'] . '</div>
					</div>';
			}
			if (WT_Filter::post('died')) {
				$data = birth_comparisons(array('DEAT'));
				echo '
					<div class="result">
						<h5>' . WT_I18N::translate('%s born after death or burial', $data['count']) . '</h5>
						<div>' . $data['html'] . '</div>
					</div>';
			}
			if (WT_Filter::post('birt_marr')) {
				$data = birth_comparisons(array('FAMS'), 'MARR');
				echo '
					<div class="result">
						<h5>' . WT_I18N::translate('%s born after marriage', $data['count']) . '</h5>
						<div>' . $data['html'] . '</div>
					</div>';
			}
			if (WT_Filter::post('birt_chil')) {
				$data = birth_comparisons(array('FAMS'), 'CHIL');
				echo '
					<div class="result">
						<h5>' . WT_I18N::translate('%s born after their children', $data['count']) . '</h5>
						<div>' . $data['html'] . '</div>
					</div>';
			}
			if (WT_Filter::post('buri')) {
				$data = death_comparisons(array('BURI'));
				echo '
					<div class="result">
						<h5>' . WT_I18N::translate('%s buried before death', $data['count']) . '</h5>
						<div>' . $data['html'] . '</div>
					</div>';
			}
			if (WT_Filter::post('bap_age')) {
				$data = query_age(array('BAPM', 'CHR'), $bap_age);
				echo '
					<div class="result">
						<h5>' . WT_I18N::translate('%1s baptised more than %2s years after birth', $data['count'], $bap_age) . '</h5>
						<div>' . $data['html'] . '</div>
					</div>';
			}
			if (WT_Filter::post('sex')) {
				$data = missing_tag('SEX');
				echo '
					<div class="result">
						<h5>' . WT_I18N::translate('%s with no gender recorded', $data['count']) . '</h5>
						<ul>' . $data['html'] . '</ul>
					</div>';
			}
			if (WT_Filter::post('dupe_deat')) {
				$data = duplicate_tag('DEAT');
				echo '
					<div class="result">
						<h5>' . WT_I18N::translate('%s with duplicated death record', $data['count']) . '</h5>
						<ul>' . $data['html'] . '</ul>
					</div>';
			}
			if (WT_Filter::post('dupe_birt')) {
				$data = duplicate_tag('BIRT');
				echo '
					<div class="result">
						<h5>' . WT_I18N::translate('%s with duplicated birth record', $data['count']) . '</h5>
						<ul>' . $data['html'] . '</ul>
					</div>';
			}
			if (WT_Filter::post('dupe_sex')) {
				$data = duplicate_tag('SEX');
				echo '
					<div class="result">
						<h5>' . WT_I18N::translate('%s with duplicated gender record', $data['count']) . '</h5>
						<ul>' . $data['html'] . '</ul>
					</div>';
			}
			if (WT_Filter::post('dupe_buri')) {
				$data = duplicate_tag('BURI');;
				echo '
					<div class="result">
						<h5>' . WT_I18N::translate('%s with duplicated burial record', $data['count']) . '</h5>
						<ul>' . $data['html'] . '</ul>
					</div>';
			}
			if (WT_Filter::post('dupe_name')) {
				$data = identical_name();
				echo '
					<div class="result">
						<h5>' . WT_I18N::translate('%s with identical name records', $data['count']) . '</h5>
						<ul>' . $data['html'] . '</ul>
					</div>';
			}
		?>
	</div>
</div> <!-- close sanity_check page div -->

<?php

// sanity functions
function birth_comparisons($tag_array, $tag2 = '') {
	$html = '';
	$count = 0;
	$tag_count = count($tag_array);
	for ($i = 0; $i < $tag_count; $i ++) {
		$rows = WT_DB::prepare(
			"SELECT i_id AS xref, i_gedcom AS gedrec FROM `##individuals` WHERE `i_file` = ? AND `i_gedcom` LIKE CONCAT('%1 ', ?, '%') AND `i_gedcom` NOT LIKE CONCAT('%1 ', ?, ' Y%')"
		)->execute(array(WT_GED_ID, $tag_array[$i], $tag_array[$i]))->fetchAll();
		foreach ($rows as $row) {
			$person		= WT_Person::getInstance($row->xref);
			$birth_date = $person->getBirthDate();
			switch ($tag_array[$i]) {
				case ('FAMS'):
					switch ($tag2) {
						case 'MARR':
							foreach ($person->getSpouseFamilies() as $family) {
								$event_date	= $family->getMarriageDate();
								$age_diff	= WT_Date::Compare($event_date, $birth_date);
								if ($event_date->MinJD() && $birth_date->MinJD() && ($age_diff < 0)) {
									$html .= '
										<p>
											<div class="first"><a href="'. $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">'. $person->getFullName(). '</a></div>
											<div class="second"><span class="label">' . WT_Gedcom_Tag::getLabel('BIRT') . '</span>' . $birth_date->Display() . '</div>
											<div class="third"><span class="label">' . WT_Gedcom_Tag::getLabel($tag2) . '</span>' . $event_date->Display() . '</div>
										</p>';
									$count ++;
								}
							}
						break;
						case 'CHIL':
							foreach ($person->getSpouseFamilies() as $family) {
								$children = $family->getChildren();
								foreach ($children as $child) {
									$event_date	= $child->getBirthDate();
									$age_diff	= WT_Date::Compare($event_date, $birth_date);
									if ($event_date->MinJD() && $birth_date->MinJD() && ($age_diff < 0)) {
										$html .= '
											<p>
												<div class="first"><a href="'. $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">'. $person->getFullName(). '</a></div>
												<div class="second"><span class="label">' . WT_Gedcom_Tag::getLabel('BIRT') . '</span>' . $birth_date->Display() . '</div>
												<div class="third"><span class="label">' . WT_Gedcom_Tag::getLabel($tag2) . '<a href="'. $child->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">'. $child->getFullName(). '</a>' . WT_Gedcom_Tag::getLabel('BIRT') . '</span>' . $event_date->Display() . '</div>
											</p>';
										$count ++;
									}
								}
							}
						break;
					}
				break;
				case 'FAMC':
				break;
				default:
					$event = $person->getFactByType($tag_array[$i]);
					if ($event) {
						$event_date = $person->getFactByType($tag_array[$i])->getDate();
						$age_diff	= WT_Date::Compare($event_date, $birth_date);
						if ($event_date->MinJD() && $birth_date->MinJD() && ($age_diff < 0)) {
							$html .= '
								<p>
									<div class="first"><a href="'. $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">'. $person->getFullName(). '</a></div>
									<div class="second"><span class="label">' . WT_Gedcom_Tag::getLabel('BIRT') . '</span>' . $birth_date->Display() . '</div>
									<div class="third"><span class="label">' . WT_Gedcom_Tag::getLabel($tag_array[$i]) . '</span>' . $event_date->Display() . '</div>
								</p>';
							$count ++;
						}
					}
				break;
			}
		}
	}
	return array('html' => $html, 'count' => $count);
}

function death_comparisons($tag_array) {
	$html		= '';
	$count		= 0;
	$tag_count	= count($tag_array);
	for ($i = 0; $i < $tag_count; $i ++) {
		$rows = WT_DB::prepare(
			"SELECT i_id AS xref, i_gedcom AS gedrec FROM `##individuals` WHERE `i_file` = ? AND `i_gedcom` LIKE CONCAT('%1 ', ?, '%') AND `i_gedcom` NOT LIKE CONCAT('%1 ', ?, ' Y%')"
		)->execute(array(WT_GED_ID, $tag_array[$i], $tag_array[$i]))->fetchAll();
		foreach ($rows as $row) {
			$person			= WT_Person::getInstance($row->xref);
			$death_date 	= $person->getDeathDate();
			$event			= $person->getFactByType($tag_array[$i]);
			if ($event) {
				$event_date = $event->getDate();
				$age_diff	= WT_Date::Compare($event_date, $death_date);
				if ($event_date->MinJD() && $death_date->MinJD() && ($age_diff < 0)) {
					$html .= '
						<p>
							<div class="first"><a href="'. $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">'. $person->getFullName(). '</a></div>
							<div class="second"><span class="label">' . WT_Gedcom_Tag::getLabel($tag_array[$i]) . '</span>' . $event_date->Display() . '</div>
							<div class="third"><span class="label">' . WT_Gedcom_Tag::getLabel('DEAT') . '</span>' . $death_date->Display() . '</div>
						</p>';
					$count ++;
				}
			}
		}
	}
	return array('html' => $html, 'count' => $count);
}

function missing_tag($tag) {
	$html	= '';
	$count	= 0;
	$rows	= WT_DB::prepare(
		"SELECT i_id AS xref, i_gedcom AS gedrec FROM `##individuals` WHERE `i_file` = ? AND `i_gedcom` NOT REGEXP CONCAT('\n[0-9] ' , ?)"
	)->execute(array(WT_GED_ID, $tag))->fetchAll();
	foreach ($rows as $row) {
		$person = WT_Person::getInstance($row->xref);
		$html .= '<li><a href="'. $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">'. $person->getFullName(). '</a></li>';
		$count ++;
	}
	return array('html' => $html, 'count' => $count);
}

function duplicate_tag($tag) {
	$html	= '';
	$count	= 0;
	$rows	= WT_DB::prepare(
		"SELECT i_id AS xref, i_gedcom AS gedrec FROM `##individuals` WHERE `i_file`= ? AND `i_gedcom` REGEXP '(\n1 " . $tag . ")((.*\n.*)*)(\n1 " . $tag . ")(.*)'"
 	)->execute(array(WT_GED_ID))->fetchAll();
	foreach ($rows as $row) {
		$person = WT_Person::getInstance($row->xref);
		$html .= '<li><a href="'. $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">'. $person->getFullName(). '</a></li>';
		$count ++;
	}
	return array('html' => $html, 'count' => $count);
}

function identical_name() {
	$html	= '';
	$count	= 0;
	$rows	= WT_DB::prepare(
		"SELECT n_id AS xref, COUNT(*) as count  FROM `##name` WHERE `n_file`= ? AND `n_type`= 'NAME' GROUP BY `n_id`, `n_sort` HAVING COUNT(*) > 1 "
 	)->execute(array(WT_GED_ID))->fetchAll();
	foreach ($rows as $row) {
		$person = WT_Person::getInstance($row->xref);
		$html .= '<li><a href="'. $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">'. $person->getFullName(). '</a></li>';
		$count ++;
	}
	return array('html' => $html, 'count' => $count);
}

function query_age($tag_array, $age) {
	$html		= '';
	$count		= 0;
	$tag_count	= count($tag_array);
	$sql		= "
		SELECT
		 tag.d_gid AS xref,
		 tag.d_year - birth.d_year AS age
		 FROM
			 `##dates` AS tag,
			 `##dates` AS birth,
			 `##individuals` AS indi
		 WHERE
			 indi.i_id = birth.d_gid AND
			 birth.d_gid = tag.d_gid AND
			 tag.d_file = ? AND
			 birth.d_file = tag.d_file AND
			 birth.d_file = indi.i_file AND
			 birth.d_fact = 'BIRT' AND
			 tag.d_fact = ? AND
			 birth.d_julianday1 <> 0 AND
			 tag.d_julianday1 > birth.d_julianday2 AND
			 tag.d_year-birth.d_year > ?
		 GROUP BY xref
		 ORDER BY age DESC
	";

	for ($i = 0; $i < $tag_count; $i ++) {
		$rows = WT_DB::prepare($sql)->execute(array(WT_GED_ID, $tag_array[$i], $age))->fetchAll();
		foreach ($rows as $row) {
			$person = WT_Person::getInstance($row->xref);
			$html .= '
				<li>
					<a href="'. $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">'. $person->getFullName(). '</a>
					<span> (' . WT_I18N::translate('age %1s', $row->age) . ')</span>
				</li>';
			$count ++;
		}
	}
	return array('html' => $html, 'count' => $count);
}
