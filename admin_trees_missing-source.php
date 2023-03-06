<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net
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

define('KT_SCRIPT_NAME', 'admin_trees_missing-source.php');
require './includes/session.php';
require KT_ROOT.'includes/functions/functions_edit.php';
require KT_ROOT.'includes/functions/functions_print_facts.php';
global $DEFAULT_PEDIGREE_GENERATIONS;

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Missing fact or event sources'))
	->pageHeader()
	->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
	->addInlineJavascript('autocomplete();');

//-- set list of all configured individual tags (level 1)
$indifacts		= preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'INDI_FACTS_ADD'), -1, PREG_SPLIT_NO_EMPTY);
$uniqueIndfacts	= preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'INDI_FACTS_UNIQUE'), -1, PREG_SPLIT_NO_EMPTY);
$indifacts		= array_merge($indifacts, $uniqueIndfacts);

$famfacts   	= preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'FAM_FACTS_ADD'),     -1, PREG_SPLIT_NO_EMPTY);
$uniqueFamfacts	= preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'FAM_FACTS_UNIQUE'),  -1, PREG_SPLIT_NO_EMPTY);
$famfacts		= array_merge($famfacts, $uniqueFamfacts);

$facts =  array_merge($indifacts, $famfacts);

$translated_facts	= array();
foreach ($facts as $addfact) {
	$translated_facts[$addfact] = KT_Gedcom_Tag::getLabel($addfact);
}
uasort($translated_facts, 'factsort');

//-- variables
$fact	= KT_Filter::post('fact');
$go 	= KT_Filter::post('go');
$ged	= KT_Filter::post('ged') ? KT_Filter::post('ged') : KT_GEDCOM;

// prepare result list
$list	= array_unique(KT_Query_Name::individuals('', '', '', false, false, KT_GED_ID));
$result	= array();
$check	= array();
$count	= 0;
$countr	= 0;

foreach ($list as $person) {
	if (in_array($fact, $after_death) && !$person->isDead()) {
		continue;
	}
	if (in_array($fact, $famfacts)) {
		$fam_record	= array();
		// collect FAMS records for this person
		$ct = preg_match_all('/\n1 FAMS @(.+)@/', $person->getGedcomRecord(), $matches, PREG_SET_ORDER); // collect family info for FAM records ($matches)
		foreach ($matches as $match) {
			if (!in_array($match[1], $check)) {
				$check[] = $match[1]; // avoid duplicate data from both spouses
				$fam_record[] = KT_Family::getInstance($match[1]);
			}
		}
		foreach ($fam_record as $family) {
			$event = $family->getFactByType($fact);
			if ($event) {
				$count ++;
				$ct_s = preg_match_all("/\d SOUR @(.*)@/", $event->getGedcomRecord(), $match);
				if ($ct_s == 0) {
					$countr ++;
					$result[$family->getXref()]['name']	= '<a style="cursor:pointer;" href="' . $family->getHtmlUrl() . '" target="_blank;">' . $family->getFullName() . '</a>';
				}
			}
		}
	} else {
		$event = $person->getFactByType($fact);
		if ($event) {
			$count ++;
			$ct_s = preg_match_all("/\d SOUR @(.*)@/", $event->getGedcomRecord(), $match);
			if ($ct_s == 0) {
				$countr ++;
				$result[$person->getXref()]['name']	= '<a style="cursor:pointer;" href="' . $person->getHtmlUrl() . '" target="_blank;">' . $person->getLifespanName() . '</a>';
			}
		}
	}
} ?>

<!-- input form -->
<div id="missing_data-page">
	<div class="noprint">
		<h2><?php echo $controller->getPageTitle(); ?></h2>
		<div class="help_text">
			<p class="helpcontent">
				<?php echo /* I18N: Sub-title for missing data admin page */ KT_I18N::translate('A list of individuals who have the selected fact or event in their family tree data, but without a source reference'); ?>
				<br>
				<?php echo /* I18N: Help content for missing data admin page */ KT_I18N::translate('Whenever possible names are followed by the individual\'s lifespan dates for ease of identification. Note that these may include dates of baptism, christening, burial and cremation if birth and death dates are missing.<br>The list also ignores any estimates of dates or ages, so living people will be listed as missing death dates and places.<br>Some facts such as "Religion" do not commonly have sub-tags like date, place or source, so here only the fact itself is checked for.'); ?>
			</p>
		</div>
		<form name="resource" id="resource" method="post" action="<?php echo KT_SCRIPT_NAME; ?>">
			<input type="hidden" name="go" value="1">
			<div id="admin_options">
				<div class="input">
					<label><?php echo KT_I18N::translate('Family tree'); ?></label>
					<?php echo select_edit_control('ged', KT_Tree::getNameList(), null, KT_GEDCOM); ?>
				</div>
				<div class="input">
					<label for = "fact"><?php echo KT_I18N::translate('Fact or event'); ?></label>
					<select name="fact" id="fact">
						<option value="fact" disabled selected ><?php echo /* I18N: first/default option in a drop-down listbox */ KT_I18N::translate('Select'); ?></option>
						<?php foreach ($translated_facts as $key=>$fact_name) {
							if ($key !== 'EVEN' && $key !== 'FACT') {
								echo '<option value="' . $key . '"' . ($key == $fact ? ' selected ' : '') . '>' . $fact_name . '</option>';
							}
						}
						echo '<option value="EVEN"' . ($fact == 'EVEN'? ' selected ' : '') . '>' . KT_I18N::translate('Custom event') . '</option>';
						echo '<option value="FACT"' . ($fact == 'FACT'? ' selected ' : '') . '>' . KT_I18N::translate('Custom fact') . '</option>';
						?>
					</select>
				</div>
				<button class="btn btn-primary" type="submit" value="<?php echo KT_I18N::translate('show'); ?>">
					<i class="fa fa-check"></i>
					<?php echo $controller->getPageTitle(); ?>
				</button>
			</div>
		</form>
	</div>
	<hr class="noprint" style="clear:both;">
	<!-- end of form -->

	<!-- output results as table -->
	<?php if ($go == 1) {
		$controller
			->addExternalJavascript(KT_JQUERY_DATATABLES_URL)
			->addExternalJavascript(KT_JQUERY_DT_HTML5)
			->addExternalJavascript(KT_JQUERY_DT_BUTTONS)
			->addInlineJavascript('
				jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
				jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
				jQuery("#missing_data").dataTable({
					dom: \'<"H"pBf<"dt-clear">irl>t<"F"pl>\',
					' . KT_I18N::datatablesI18N() . ',
					buttons: [{extend: "csv", exportOptions: {columns: ":visible"}}],
					autoWidth: false,
					paging: true,
					pagingType: "full_numbers",
					lengthChange: true,
					filter: true,
					info: true,
					jQueryUI: true,
					sorting: [0,"asc"],
					displayLength: 20,
					stateSave: true,
					stateDuration: -1
				});

				jQuery("#missing_data").css("visibility", "visible");
				jQuery(".loading-image").css("display", "none");
			'); ?>

		<div class="loading-image">&nbsp;</div>
		<?php if($countr > 0 && $count > 0) {
			$percent = round((float)$countr / $count * 100) . '%'; ?>
			<h3><?php echo /* I18N: sub-heading for report on missing data listing selected event types */ KT_I18N::translate('Missing source references for <u>%s</u> data', KT_Gedcom_Tag::getLabel($fact)); ?></h3>
			<h4><?php echo /* I18N: example << 10 out of 50, or 20% >> */ KT_I18N::translate('(%s out of %s = %s)', KT_I18N::number($countr), KT_I18N::number($count), $percent); ?></h4>
			<table id="missing_data">
				<thead>
					<tr>
						<th><?php echo KT_I18N::translate('Name'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($result as $output) { ?>
						<tr>
							<td><?php  echo $output['name']; ?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		<?php } else { ?>
			<p><?php echo KT_I18N::translate('No missing source references found'); ?></p>
		<?php }
	} ?>
</div> <!-- close missing_data page div -->

<?php
