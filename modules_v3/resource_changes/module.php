<?php
// Classes and libraries for module system
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2015 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2010 John Finlay
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class resource_changes_WT_Module extends WT_Module implements WT_Module_menu {

	const DEFAULT_DAYS = 30;
	const MAX_DAYS = 90;

	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module. Tasks that need further research. */ WT_I18N::translate('Recent changes');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of “Research tasks” module */ WT_I18N::translate('A report of recent and pending changes.');
	}

	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'show':
			$this->show();
			break;
			unset($_GET['action']);
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement WT_Module_Menu
	public function defaultMenuOrder() {
		return 25;
	}

	// Implement WT_Module_Menu
	public function MenuType() {
		return 'other';
	}

	// Implement WT_Module_Menu
	public function getMenu() {
		return null;
	}

	// Implement class WT_Module_Menu
	public function show() {
		global $controller, $GEDCOM;
		$controller = new WT_Controller_Page();
		$controller
			->setPageTitle($this->getTitle())
			->pageHeader();

		//Configuration settings ===== //
		$days			= self::DEFAULT_DAYS; // Number of days to show
		$sortStyle		= 'date_desc'; // Sort by date, newest first
		$aaSorting		= "[4,'desc'], [5,'asc']";
		// ============================ //

		require_once WT_ROOT.'includes/functions/functions_print_lists.php';

		$pending_changes = WT_DB::prepare(
			"SELECT *".
			" FROM `##change`".
			" WHERE status='pending' AND gedcom_id=?".
			" GROUP BY xref"
		)->execute(array(WT_GED_ID))->fetchAll();

		$recent_changes = get_recent_changes(WT_CLIENT_JD - $days);

		$n = 0;
		$table_id = "ID" . (int)(microtime() * 1000000); // create a unique ID

		// Print block header
		$id		= $this->getName();
		$class	= $this->getName();
		$title	= /* I18N: title for list of recent changes */ WT_I18N::plural('Changes in the last day', 'Changes in the last %s days', $days, WT_I18N::number($days));
		$content = '
			<style>#research_tasks-page table th, #research_tasks-page table td {padding:8px;}</style>
			<div id="research_tasks-page" style="margin: auto; width: 90%;">
			<h2>' . $title . '</h2>
		';

		// Print changes
		if ($recent_changes || $pending_changes) {
			$controller
				->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
				->addInlineJavascript('
					jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
					jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
					jQuery("#'.$table_id.'").dataTable({
						dom: \'<"H"pf<"dt-clear">irl>t<"F"pl>\',
						' . WT_I18N::datatablesI18N() . ',
						autoWidth: false,
						paging: true,
						pagingType: "full_numbers",
						lengthChange: true,
						filter: true,
						info: true,
						jQueryUI: true,
						sorting: ['.$aaSorting.'],
						displayLength: 20,
						"aoColumns": [
							/* 0-Type */    {"bSortable": false, "sClass": "center"},
							/* 1-Record */  {"iDataSort": 5},
							/* 2-Change */  {"iDataSort": 4},
							/* 3-By */      null,
							/* 4-DATE */    {"bVisible": false},
							/* 5-SORTNAME */{"sType": "unicode", "bVisible": false}
						]
					});
				');
		}
		// Print pending changes
		if ($pending_changes) {
			$content .= '
				<h3>' . WT_I18N::translate('Pending changes') . '</h3>
				<table id="' . $table_id . '" class="width100">
					<thead>
						<tr>
							<th style="width: 30px;">&nbsp;</th>
							<th>' . WT_I18N::translate('Record') . '</th>
							<th>' . WT_Gedcom_Tag::getLabel('CHAN') . '</th>
							<th>' . WT_Gedcom_Tag::getLabel('_WT_USER') . '</th>
							<th>DATE</th>
							<th>SORTNAME</th>
						</tr>
					</thead>
					<tbody>';
						//-- table body
						foreach ($pending_changes as $change_id) {
							$record = WT_GedcomRecord::getInstance($change_id);
							if (!$record || !$record->canDisplayDetails()) {
								continue;
							}
							$content .= '
								<tr>
									<td>';
									$indi = false;
									switch ($record->getType()) {
										case "INDI":
											$icon = $record->getSexImage('small', '', '', false);
											$indi = true;
											break;
										case "FAM":
											$icon = '<i class="icon-button_family"></i>';
											break;
										case "OBJE":
											$icon = '<i class="icon-button_media"></i>';
											break;
										case "NOTE":
											$icon = '<i class="icon-button_note"></i>';
											break;
										case "SOUR":
											$icon = '<i class="icon-button_source"></i>';
											break;
										case "REPO":
											$icon = '<i class="icon-button_repository"></i>';
											break;
										default:
											$icon = '&nbsp;';
											break;
									}
									$content .= '<a href="'. $record->getHtmlUrl() .'">'. $icon . '</a>';
								$content .= '</td>';
								++$n;
								//-- Record name(s)
								$name = $record->getFullName();
								$content .= '<td class="wrap">
									<a href="'. $record->getHtmlUrl() .'">'. $name . '</a>';
									if ($indi) {
										$content .= '<p style="display: inline; font-size: 80%; padding: 0 10px;">' . $record->getLifeSpan() . '</p>';
										$addname = $record->getAddName();
										if ($addname) {
											$content .= '
												<div class="indent">
													<a href="'. $record->getHtmlUrl() .'">'. $addname . '</a>
												</div>';
										}
									}
								$content .= '</td>';
								//-- Last change date/time
								$content .= '<td class="wrap">' . $record->LastChangeTimestamp() . '</td>';
								//-- Last change user
								$content .= '<td class="wrap">' . $record->LastChangeUser() . '</td>';
								//-- change date (sortable) hidden by datatables code
								$content .= '<td>' . $record->LastChangeTimestamp(true) . '</td>';
								//-- names (sortable) hidden by datatables code
								$content .= '<td>' . $record->getSortName() . '</td>
							</tr>
						';
					}
				$content .= '</tbody>
			</table>
		';
	}
		// Print approved changes
		if ($recent_changes) {
			$content .= '
				<h3>' . WT_I18N::translate('Recent changes') . '</h3>
				<table id="' . $table_id . '" class="width100">
					<thead>
						<tr>
							<th style="width: 30px;">&nbsp;</th>
							<th>' . WT_I18N::translate('Record') . '</th>
							<th>' . WT_Gedcom_Tag::getLabel('CHAN') . '</th>
							<th>' . WT_Gedcom_Tag::getLabel('_WT_USER') . '</th>
							<th>DATE</th>
							<th>SORTNAME</th>
						</tr>
					</thead>
					<tbody>';
						//-- table body
						foreach ($recent_changes as $change_id) {
							$record = WT_GedcomRecord::getInstance($change_id);
							if (!$record || !$record->canDisplayDetails()) {
								continue;
							}
							$content .= '
								<tr>
									<td>';
										$indi = false;
										switch ($record->getType()) {
											case "INDI":
												$icon = $record->getSexImage('small', '', '', false);
												$indi = true;
												break;
											case "FAM":
												$icon = '<i class="icon-button_family"></i>';
												break;
											case "OBJE":
												$icon = '<i class="icon-button_media"></i>';
												break;
											case "NOTE":
												$icon = '<i class="icon-button_note"></i>';
												break;
											case "SOUR":
												$icon = '<i class="icon-button_source"></i>';
												break;
											case "REPO":
												$icon = '<i class="icon-button_repository"></i>';
												break;
											default:
												$icon = '&nbsp;';
												break;
										}
										$content .= '<a href="'. $record->getHtmlUrl() .'">'. $icon . '</a>';
									$content .= '</td>';
									++$n;
									//-- Record name(s)
									$name = $record->getFullName();
									$content .= '<td class="wrap">
										<a href="'. $record->getHtmlUrl() .'">'. $name . '</a>';
										if ($indi) {
											$content .= '<p style="display: inline; font-size: 80%; padding: 0 10px;">' . $record->getLifeSpan() . '</p>';
											$addname = $record->getAddName();
											if ($addname) {
												$content .= '
													<div class="indent">
														<a href="'. $record->getHtmlUrl() .'">'. $addname . '</a>
													</div>';
											}
										}
									$content .= '</td>';
									//-- Last change date/time
									$content .= '<td class="wrap">' . $record->LastChangeTimestamp() . '</td>';
									//-- Last change user
									$content .= '<td class="wrap">' . $record->LastChangeUser() . '</td>';
									//-- change date (sortable) hidden by datatables code
									$content .= '<td>' . $record->LastChangeTimestamp(true) . '</td>';
									//-- names (sortable) hidden by datatables code
									$content .= '<td>' . $record->getSortName() . '</td>
								</tr>
							';
						}
					$content .= '</tbody>
				</table>
			';
		} else {
			$content .= WT_I18N::translate('There have been no changes within the last %s days.', WT_I18N::number($days));
		}

		$content .= '</div>';

		echo $content;
	}

}
