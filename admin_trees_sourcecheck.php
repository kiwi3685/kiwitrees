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
// Copyright (C) 2006-2009 Greg Roach
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
//$Id$

define('WT_SCRIPT_NAME', 'admin_trees_sourcecheck.php');
require './includes/session.php';
require WT_ROOT.'includes/functions/functions_edit.php';
require WT_ROOT.'includes/functions/functions_print_facts.php';

$controller=new WT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(WT_I18N::translate('Source check'))
	->pageHeader()
	->addExternalJavascript(WT_STATIC_URL.'js/autocomplete.js')
	->addInlineJavascript('
		autocomplete();
	')
	->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
	->addInlineJavascript('
		jQuery("#citation_table").dataTable({
			dom: \'<"H"pf<"dt-clear">irl>t<"F"pl>\',
			' . WT_I18N::datatablesI18N() . ',
			autoWidth: true,
			paging: true,
			pagingType: "full_numbers",
			lengthChange: true,
			filter: true,
			info: true,
			jQueryUI: true,
			sorting: [[2,"asc"]],
			displayLength: 20,
			columns: [
				/* 0-type   */ { "sWidth": "200px" },
				/* 1-record */ null,
				/* 2-cite   */ null
			]
		});
	');

$sid = WT_Filter::post('source');
?>

<div id="source_check">
	<h2><?php echo $controller->getPageTitle(); ?></h2>
	<form method="post" action="<?php echo WT_SCRIPT_NAME; ?>">
		<input type="hidden" name="go" value="1">
		<div id="source_options">
			<div class="input">
				<label><?php echo WT_I18N::translate('Family tree'); ?></label>
				<?php echo select_edit_control('ged', WT_Tree::getNameList(), null, WT_GEDCOM); ?>
			</div>
			<div class="input">
				<label><?php echo WT_I18N::translate('Source'); ?></label>
				<input type="text" id="source" name="source" value="<?php echo $sid ? $sid : ''; ?>" dir="ltr" class="" data-autocomplete-type="SOUR" autocomplete="off">
			</div>
			<div class="input">
				<button type="submit" class="btn btn-primary" style="float: none; margin: 0;">
					<i class="fa fa-check"></i>
					<?php echo $controller->getPageTitle(); ?>
				</button>
			</div>
		</div>
	</form>
	<hr class="clearfloat">

	<?php if (WT_Filter::post('go')) { 	?>
		<div id="source_list">
			<?php

			$source		= WT_Source::getInstance($sid);
			$indi_data	= indi_citations($sid);
			$fam_data	= fam_citations($sid);
			$data		= array_merge($indi_data, $fam_data);
			?>
			<h3>
				<span><?php echo WT_I18N::translate('Source'); ?><span>: <a href="<?php echo $source->getHtmlUrl(); ?>"><?php echo $source->getFullName(); ?></a>
			</h3>
			<table id="citation_table" style="width: 100%;">
				<thead>
					<tr>
						<th><?php echo WT_I18N::translate('Edit raw GEDCOM record'); ?></th>
						<th><?php echo WT_I18N::translate('Record'); ?></th>
						<th><?php echo WT_I18N::translate('Citation'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($data as $row) {
						$needle		= '2 SOUR @' . $sid . '@';
						$pos1		= strpos($row->gedrec, $needle) + strlen($needle);
						$start_pos	= strpos($row->gedrec, '3 PAGE ', $pos1);
						$end_pos	= strpos($row->gedrec, "\n", $start_pos);
						$length		= $end_pos - $start_pos - 7;
						$indi		= WT_Person::getInstance($row->xref);
						$fam_data	= WT_Family::getInstance($row->xref);
						$record		= WT_Person::getInstance($row->xref) ? WT_Person::getInstance($row->xref) : WT_Family::getInstance($row->xref);
						?>
						<tr>
							<td>
								<?php
								switch ($record->getType()) {
									case "INDI":
										$type = WT_I18N::translate('Individual');
										break;
									case "FAM":
										$type = WT_I18N::translate('Family');
										break;
									case "OBJE":
										$type = WT_I18N::translate('Media');
										break;
									case "NOTE":
										$type = WT_I18N::translate('Note');
										break;
									case "SOUR":
										$type = WT_I18N::translate('Source');
										break;
									case "REPO":
										$type = WT_I18N::translate('Repository');
										break;
									default:
										$type = '&nbsp;';
										break;
								}
								?>
								<a href="#" onclick="return edit_raw('<?php echo $row->xref; ?>');"><?php echo $type; ?></a>
							</td>
							<td>
								<a href="<?php echo $record->getHtmlUrl(); ?>" target="_blank"><?php echo $record->getFullName(); ?></a>
							<td>
								<?php echo substr($row->gedrec, $start_pos + 7, $length); ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	<?php } ?>
</div> <!-- close source_check page div -->

<?php

// source functions
function indi_citations($sid) {
	$rows = WT_DB::prepare(
		"SELECT i_id AS xref, i_gedcom AS gedrec FROM `##individuals` WHERE `i_file` = ? AND `i_gedcom` REGEXP '2 SOUR @" . $sid . "@\n3 PAGE (.*)\n'"
	)->execute(array(WT_GED_ID))->fetchAll();

	return $rows;
}

function fam_citations($sid) {
	$rows = WT_DB::prepare(
		"SELECT f_id AS xref, f_gedcom AS gedrec FROM `##families` WHERE `f_file` = ? AND `f_gedcom` REGEXP '2 SOUR @" . $sid . "@\n3 PAGE (.*)\n'"
	)->execute(array(WT_GED_ID))->fetchAll();

	return $rows;
}
