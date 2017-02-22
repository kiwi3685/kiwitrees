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
 * along with Kiwitrees.  If not, see <http://www.gnu.org/licenses/>.
 */

define('WT_SCRIPT_NAME', 'admin_trees_source.php');
require './includes/session.php';
require WT_ROOT.'includes/functions/functions_edit.php';
require WT_ROOT.'includes/functions/functions_print_facts.php';

$sid	= WT_Filter::post('source');
$stype	= WT_Filter::post('stype');

$options = array(
	'facts'		=> WT_I18N::translate('Facts or events'),
	'records'	=> WT_I18N::translate('Records')
);

$controller = new WT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(WT_I18N::translate('Review source'))
	->pageHeader()
	->addExternalJavascript(WT_AUTOCOMPLETE_JS_URL)
	->addInlineJavascript('
		autocomplete();
	')
	->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
	->addExternalJavascript(WT_JQUERY_DT_HTML5)
	->addExternalJavascript(WT_JQUERY_DT_BUTTONS)
	->addInlineJavascript('
		jQuery("#source_list").css("visibility", "visible");
		jQuery(".loading-image").css("display", "none");
	');

?>

<div id="source_check">
	<h2><?php echo $controller->getPageTitle(); ?></h2>
	<div class="help_text">
		<p class="help_content">
			<?php echo WT_I18N::translate('Display a list of facts, events or records where the selected source is used. Facts or events are items like birth, marriage, death. Records are items like individuals, families, media.'); ?>
		</p>
	</div>
	<form method="post" action="<?php echo WT_SCRIPT_NAME; ?>">
		<input type="hidden" name="go" value="1">
		<div id="admin_options">
			<div class="input">
				<label><?php echo WT_I18N::translate('Family tree'); ?></label>
				<?php echo select_edit_control('ged', WT_Tree::getNameList(), null, WT_GEDCOM); ?>
			</div>
			<div class="input">
				<label><?php echo WT_I18N::translate('Source'); ?></label>
				<input type="text" id="source" name="source" value="<?php echo $sid ? $sid : ''; ?>" dir="ltr" class="" data-autocomplete-type="SOUR" autocomplete="off">
			</div>
			<div class="input">
				<label><?php echo WT_I18N::translate('Source type'); ?></label>
				<?php echo select_edit_control('stype', $options, null, $stype); ?>
			</div>
			<button type="submit" class="btn btn-primary">
				<i class="fa fa-check"></i>
				<?php echo $controller->getPageTitle(); ?>
			</button>
		</div>
	</form>
	<hr class="clearfloat">

	<?php if (WT_Filter::post('go')) { 	?>
		<div id="source_list" style="visibility: hidden;">
			<?php
			$source	= WT_Source::getInstance($sid);
			$data	= facts($sid, $stype);
			?>
			<h3>
				<span><?php echo WT_I18N::translate('Source'); ?></span>&nbsp;-&nbsp;<a href="<?php echo $source->getHtmlUrl(); ?>"><?php echo $source->getFullName(); ?></a>&nbsp;-&nbsp;<span><?php echo $options[$stype]; ?></span>
			</h3>
			<?php switch ($stype) {
				case 'facts' :
					$controller
						->addInlineJavascript('
							jQuery("#facts_table").dataTable({
								dom: \'<"H"pBf<"dt-clear">irl>t<"F"pl>\',
								' . WT_I18N::datatablesI18N() . ',
								buttons: [{extend: "csv", exportOptions: {columns: ":visible"}}],
								autoWidth: false,
								paging: true,
								pagingType: "full_numbers",
								lengthChange: true,
								filter: true,
								info: true,
								jQueryUI: true,
								sorting: [[0,"desc"]],
								displayLength: 20,
								columns: [
									/* 0-type */		null,
									/* 1-record */		{ "className": "nowrap" },
									/* 2-birthdate */	{ dataSort: 3 },
									/* 3-BIRT:DATE */	{ visible: false },
									/* 4-event_tag */	null,
									/* 5-eventdate */	{ dataSort: 6 },
									/* 6-EVEN:DATE */	{ visible: false },
									/* 7-cite */ 		null
								],
								stateSave: true,
								stateDuration: -1
							});
						'); ?>
					<table id="facts_table" style="width: 100%;">
						<thead>
							<tr>
								<th style="min-width: 200px;"><?php echo WT_I18N::translate('Edit raw GEDCOM record'); ?></th>
								<th><?php echo WT_I18N::translate('Record'); ?></th>
								<th style="min-width: 120px;"><?php echo WT_I18N::translate('Birth'); ?></th>
								<th><?php //SORT_BIRT ?></th>
								<th style="min-width: 120px;"><?php echo WT_I18N::translate('Event'); ?></th>
								<th style="min-width: 120px;"><?php echo WT_I18N::translate('Event date'); ?></th>
								<th><?php //SORT_EVENT ?></th>
								<th><?php echo WT_I18N::translate('Citation text'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($data as $row) {
								$facts = WT_GedcomRecord::getInstance($row->xref)->getFacts();
								foreach ($facts as $event) {
									if (strpos($event->getGedComRecord(), 'SOUR @' . $sid . '@') !== false && $event->getTag() != 'SOUR') {
										preg_match('/\n\d SOUR @' . $sid . '@(?:\n[3-9].*)*\n/i', $row->gedrec, $match);
										$record = WT_Person::getInstance($row->xref) ? WT_Person::getInstance($row->xref) : WT_Family::getInstance($row->xref) ? WT_Family::getInstance($row->xref) : WT_Media::getInstance($row->xref);
										?>
										<tr>
											<td>
												<?php
												if ($record) {
													switch ($record->getType()) {
														case "INDI":
															$icon = $record->getSexImage('small', '', '', false);
															$type = WT_I18N::translate('Individual');
															break;
														case "FAM":
															$icon = '<i class="icon-button_family"></i>';
															$type = WT_I18N::translate('Family');
															break;
														case "OBJE":
															$icon = '<i class="icon-button_media"></i>';
															$type = WT_I18N::translate('Media');
															break;
														default:
															$type = '&nbsp;';
															break;
													}
												}
												?>
												<span>
													<?php echo $icon; ?>
												</span>
												<a href="#" onclick="return edit_raw('<?php echo $row->xref; ?>');">
													<?php echo $type; ?>
												</a>
											</td>
											<td>
												<a href="<?php echo $record->getHtmlUrl(); ?>" target="_blank" rel="noopener noreferrer"><?php echo $record->getFullName(); ?></a>
											</td>
											<td>
												<?php echo $record->getType() == "INDI" ? $record->getBirthDate()->Display() : ''; ?>
											</td>
											<td>
												<?php echo $record->getType() == "INDI" ? $record->getBirthDate()->JD() : ''; ?>
											</td>
											<td>
												<?php echo WT_Gedcom_Tag::getLabel($event->getTag()); ?>
											</td>
											<td>
												<?php echo $event->getDate()->Display(); ?>
											</td>
											<td>
												<?php echo $event->getDate()->JD(); ?>
											</td>
											<td>
												<?php if ($match){
													$text = '';
													foreach (getSourceStructure($match[0])['TEXT'] as $text_list) {
														$text = expand_urls($text_list);
													}
													if (!empty($text)) {echo $text;}
												} ?>
											</td>
										</tr>
									<?php }
								}
							} ?>
						</tbody>
					</table>
				<?php break;
				case 'records' :
					$controller
						->addInlineJavascript('
							jQuery("#records_table").dataTable({
								dom: \'<"H"pBf<"dt-clear">irl>t<"F"pl>\',
								' . WT_I18N::datatablesI18N() . ',
								buttons: [{extend: "csv", exportOptions: {columns: ":visible"}}],
								autoWidth: false,
								paging: true,
								pagingType: "full_numbers",
								lengthChange: true,
								filter: true,
								info: true,
								jQueryUI: true,
								sorting: [[0,"desc"]],
								displayLength: 20,
								columns: [
									/* 0-type */		null,
									/* 1-record */		{ "className": "nowrap" },
									/* 2-birthdate */	{ dataSort: 3 },
									/* 3-BIRT:DATE */	{ visible: false },
									/* 4-birthplace */	null,
									/* 5-deathdate */	{ dataSort: 6 },
									/* 6-DEAT:DATE */	{ visible: false },
									/* 7-deathplace */	null,
									/* 8-cite */ 		null
								],
								stateSave: true,
								stateDuration: -1
							});
						'); ?>
					<table id="records_table" style="width: 100%;">
						<thead>
							<tr>
								<th style="min-width: 200px;"><?php echo WT_I18N::translate('Edit raw GEDCOM record'); ?></th>
								<th><?php echo WT_I18N::translate('Record'); ?></th>
								<th style="min-width: 120px;"><?php echo WT_I18N::translate('Birth'); ?></th>
								<th><?php //SORT_BIRT ?></th>
								<th style="min-width: 120px;"><?php echo WT_I18N::translate('Place'); ?></th>
								<th style="min-width: 120px;"><?php echo WT_I18N::translate('Death'); ?></th>
								<th><?php //SORT_DEAT ?></th>
								<th style="min-width: 120px;"><?php echo WT_I18N::translate('Place'); ?></th>
								<th><?php echo WT_I18N::translate('Citation text'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($data as $row) {
								preg_match('/\n\d SOUR @' . $sid . '@(?:\n[2-9].*)*\n/i', $row->gedrec, $match);
								$record = WT_Person::getInstance($row->xref) ? WT_Person::getInstance($row->xref) : WT_Family::getInstance($row->xref) ? WT_Family::getInstance($row->xref) : WT_Media::getInstance($row->xref);
								?>
								<tr>
									<td>
										<?php
										if ($record) {
											switch ($record->getType()) {
												case "INDI":
													$icon = $record->getSexImage('small', '', '', false);
													$type = WT_I18N::translate('Individual');
													break;
												case "FAM":
													$icon = '<i class="icon-button_family"></i>';
													$type = WT_I18N::translate('Family');
													break;
												case "OBJE":
													$icon = '<i class="icon-button_media"></i>';
													$type = WT_I18N::translate('Media');
													break;
												default:
													$type = '&nbsp;';
													break;
											}
										}
										?>
										<span>
											<?php echo $icon; ?>
										</span>
										<a href="#" onclick="return edit_raw('<?php echo $row->xref; ?>');">
											<?php echo $type; ?>
										</a>
									</td>
									<td>
										<a href="<?php echo $record->getHtmlUrl(); ?>" target="_blank" rel="noopener noreferrer"><?php echo $record->getFullName(); ?></a>
									</td>
									<td>
										<?php echo $record->getType() == "INDI" ? $record->getBirthDate()->Display() : ''; ?>
									</td>
									<td>
										<?php echo $record->getType() == "INDI" ? $record->getBirthDate()->JD() : ''; ?>
									</td>
									<td>
										<?php echo $record->getType() == "INDI" ? $record->getBirthPlace() : ''; ?>
									</td>
									<td>
										<?php echo $record->getType() == "INDI" ? $record->getDeathDate()->Display() : ''; ?>
									</td>
									<td>
										<?php echo $record->getType() == "INDI" ? $record->getDeathDate()->JD() : ''; ?>
									</td>
									<td>
										<?php echo $record->getType() == "INDI" && $record->getDeathPlace() ? $record->getDeathPlace() : ''; ?>
									</td>
									<td>
										<?php if ($match){
											foreach (getSourceStructure($match[0])['TEXT'] as $text_list) {
												$text = expand_urls($text_list);
											}
											if (!empty($text)) {echo $text;}
										} ?>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				<?php break; ?>
			<?php } ?>
		</div>
	<?php } ?>
</div> <!-- close source_check page div -->

<?php

// source functions
function facts($sid, $stype = 'facts') {
	if ($stype == 'records') {
		$stype = 1;
	} else {
		$stype = 2;
	};

	$rows = WT_DB::prepare("
		SELECT i_id AS xref, i_gedcom AS gedrec
		FROM `##individuals`
		WHERE `i_file` = ?
		AND `i_gedcom`
		REGEXP '" . $stype . " SOUR @" . $sid . "@'
		UNION
		SELECT f_id AS xref, f_gedcom AS gedrec
		FROM `##families`
		WHERE `f_file` = ?
		AND `f_gedcom`
		REGEXP '2 SOUR @" . $sid . "@'
		UNION
		SELECT o_id AS xref, o_gedcom AS gedrec
		FROM `##other`
		WHERE `o_file` = ?
		AND `o_gedcom`
		REGEXP '2 SOUR @" . $sid . "@'
	")->execute(array(WT_GED_ID, WT_GED_ID, WT_GED_ID))->fetchAll();

	return $rows;
}
