<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2018 kiwitrees.net
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

/** @var Controller/Page $controller */
global $controller;

/** @var Tree $KT_TREE */
global $KT_TREE;

$xref	= KT_Filter::get('xref', KT_REGEX_XREF);
$census = KT_Filter::get('census');
$head	= KT_Person::getInstance($xref, $KT_TREE);
$controller->restrictAccess(class_exists($census));

/** @var KT_Census_CensusInterface */
$census = new $census;
$controller->restrictAccess($census instanceof KT_Census_CensusInterface);
$date = new KT_Date($census->censusDate());
$year = $date->minimumDate()->format('%Y');

$headImg = '<i class="icon-button_head"></i>';

$controller
	->setPageTitle(KT_I18N::translate('Create a shared note using the census assistant'))
	->addInlineJavascript('jQuery("#tblSample").on("click", ".icon-delete", function() { jQuery(this).closest("tr").remove(); });')
	->pageHeader();

$modules = KT_Module::getActiveModules(); // necessary to avoid error if no favorites menu and no favorites.
?>

<div id="census_assist-page">
    <h2><?php echo $controller->getPageTitle(); ?>
        <a class="faq_link" href="http://kiwitrees.net/faqs/modules/census-assistant/" alt="<?php echo KT_I18N::translate('View FAQ for this page.'); ?>" target="_blank" rel="noopener noreferrer">
            <?php echo KT_I18N::translate('View FAQ for this page.'); ?>
            <i class="fa fa-comments-o"></i>
        </a>
    </h2>
    <div>
		<form method="post" action="edit_interface.php" onsubmit="updateCensusText();">
			<input type="hidden" name="action" value="addnoteaction_assisted">
			<input id="pid_array" type="hidden" name="pid_array" value="none">
			<input type="hidden" name="NOTE" id="NOTE">

			<?php echo KT_Filter::getCsrf(); ?>
			<!-- Header of assistant window ===================================================== -->
			<div class="cens_left">
				<div class="cens_header">
					<h3>
						<?php echo KT_I18N::translate('Head of Household:') . '&nbsp;' . $head->getFullName(); ?>
					</h3>
					<div class="head_summary">
						<?php echo $head->format_first_major_fact(KT_EVENTS_BIRT, 2);
						if (!$head->isDead()) {
							// If alive display age
							$bdate = $head->getBirthDate();
							$age = KT_Date::GetAgeGedcom($bdate);
							if ($age != '') { ?>
								<dl>
									<dt class="label"><?php echo KT_I18N::translate('Age'); ?></dt>
									<dd class="field"><?php echo get_age_at_event($age, true); ?></dd>
								</dl>
							<?php }
						}
						echo $head->format_first_major_fact(KT_EVENTS_DEAT, 2); ?>
						<dl>
							<dt class="label"><?php echo KT_I18N::translate('Census date'); ?></dt>
							<dd class="field"><span class="date"><?php echo $date->display(); ?></span></dd>
						</dl>
					</div>
				</div>
				<!-- Census source -->
				<div class="cens_container">
					<div class="input_group">
						<label for="Titl"><?php echo KT_I18N::translate('Title'); ?></label>
						<input id="Titl" type="text" value="<?php echo $year . ' ' . $census->censusPlace() . ' - ' . KT_I18N::translate('Census transcript') . ' - ' . strip_tags($head->getFullName()) . ' - ' . KT_I18N::translate('Household'); ?>">
					</div>
					<div class="input_group">
						<label for="citation"><?php echo KT_Gedcom_Tag::getLabel('PAGE'); ?></label>
						<input id="citation" type="text">
					</div>
					<div class="input_group">
						<label for="locality"><?php echo KT_I18N::translate('Place'); ?></label>
						<input id="locality" type="text">
					</div>
					<div class="input_group">
						<label for="notes"><?php echo KT_I18N::translate('Notes'); ?></label>
						<input id="notes" type="text">
					</div>
				</div>
				<!--  Census data -->
				<div class="cens_data">
					<table id="tblSample" class="table table-census-inputs">
						<thead>
							<?php echo census_assistant_KT_Module::censusTableHeader($census); ?>
						</thead>
						<tbody>
							<?php echo census_assistant_KT_Module::censusTableRow($census, $head, $head); ?>
						</tbody>
					</table>
				</div>
				<button class="btn btn-primary" type="submit" onclick="caSave();" >
					<i class="fa fa-save"></i>
					<?php echo KT_I18N::translate('Save'); ?>
				</button>
				<button class="btn btn-primary" type="button" onclick="window.close();">
					<i class="fa fa-times"></i>
					<?php echo KT_I18N::translate('close'); ?>
				</button>
			</div>
			<div class="cens_right">
				<!-- Search  and Add Family Members Area ============================================ -->
				<h3>
					<?php echo KT_I18N::translate('Add individuals'); ?>
				</h3>

				<div class="census-assistant-search">
					<table>
						<tr>
							<td colspan="3">
								<input id="personid" type="text" placeholder="<?php echo /* I18N: Placeholder for census assistant search field */ KT_I18N::translate('Search for other people'); ?>">
								<button type="button" onclick="findindi()">
									<i class="fa fa-search" title="<?php echo /* I18N: A button label. */ KT_I18N::translate('search'); ?>"></i>
								</button>
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<button type="button" onclick="return appendCensusRow('<?php echo KT_Filter::escapeHtml(census_assistant_KT_Module::censusTableEmptyRow($census)); ?>');">
									<?php echo KT_I18N::translate('Add a blank row'); ?>
								</button>
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<?php $headImg  = '<i class="icon-button_head"></i>';
								echo KT_I18N::translate('Click %s to choose person as Head of family.', $headImg); ?>
							</td>
						</tr>
						<?php foreach ($head->getChildFamilies() as $family) {
							census_assistant_KT_Module::censusNavigatorFamily($census, $family, $head);
						}

						foreach ($head->getChildStepFamilies() as $family) {
							census_assistant_KT_Module::censusNavigatorFamily($census, $family, $head);
						}

						foreach ($head->getSpouseFamilies() as $family) {
							census_assistant_KT_Module::censusNavigatorFamily($census, $family, $head);
						}
						?>
					</table>
				</div>
			</div>
		</form>
	</div>
</div>
<script>

	function findindi() {
		var findInput = document.getElementById('personid');
		var txt = findInput.value;
		if (txt === "") {
			alert("<?php echo KT_I18N::translate('You must enter a name'); ?>");
		} else {
			var win02 = window.open(
				"module.php?mod=census_assistant&mod_action=census_find&callback=paste_id&census=<?php echo KT_Filter::escapeJs(get_class($census)); ?>&action=filter&filter=" + txt, "win02", "resizable=1, menubar=0, scrollbars=1, top=180, left=600, height=500, width=450 ");
			if (window.focus) {
				win02.focus();
			}
		}
	}

	/* Add an HTML row to the table */
	function appendCensusRow(row) {
		jQuery("#tblSample tbody").append(row);

		return false;
	}

	/* Update the census text from the various input fields */
	function updateCensusText() {
		var html        = "";
		var title       = jQuery("#Titl").val();
		var citation    = jQuery("#citation").val();
		var locality    = jQuery("#locality").val();
		var notes       = jQuery("#notes").val();
		var table       = jQuery("#tblSample");
		var max_col_ndx = table.find("thead th").length - 1;
		var line        = "";

		if (title !== "") {
			html += title + "\n";
		}
		if (citation !== "") {
			html += citation + "\n";
		}
		if (locality !== "") {
			html += locality + "\n";
		}

		html += "\n.start_formatted_area.\n";

		table.find("thead th").each(function (n, el) {
			if (n === 0 || n === max_col_ndx) { // Skip prefix & suffix cells
			 return true;
			 }
			line += "|.b." + jQuery(el).html();
		});
		html += line.substr(1) + "\n";

		table.find("tbody tr").each(function(n, el) {
			line = "";
			jQuery("input", jQuery(el)).each(function(n, el) {
				line += "|" + jQuery(el).val();
			});
			html += line.substr(1) + "\n";
		});

		html += ".end_formatted_area.\n";

		if (notes !== "") {
			html += "\n" + notes + "\n";
		}

		jQuery("#NOTE").val(html);

		var pid_array = '';
		table.find("tbody td:first-child").each(function(n, el) {
			if (n > 0) {
				pid_array += ',';
			}
			pid_array += jQuery(el).html();
		});
		jQuery("#pid_array").val(pid_array);

		return false;
	}
</script>
