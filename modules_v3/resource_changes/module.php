<?php
// Classes and libraries for module system
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
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

class resource_changes_WT_Module extends WT_Module implements WT_Module_Resources {

	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module. Tasks that need further research. */ WT_I18N::translate('Changes');
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
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Extend class WT_Module
	public function defaultAccessLevel() {
		return WT_PRIV_USER;
	}

	// Implement WT_Module_Menu
	public function defaultMenuOrder() {
		return 25;
	}

	// Implement WT_Module_Menu
	public function MenuType() {
		return 'main';
	}

	// Implement WT_Module_Menu
	public function getMenu() {
		return false;
	}

	// Implement WT_Module_Resources
	public function getResourceMenus() {

		$menus	= array();
		$menu	= new WT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&mod_action=show',
			'menu-resources-' . $this->getName()
		);
		$menus[] = $menu;

		return $menus;
	}

	// Implement class WT_Module_Menu
	public function show() {
		global $controller, $DATE_FORMAT, $GEDCOM;
		require_once WT_ROOT.'includes/functions/functions_print_lists.php';
		require_once WT_ROOT.'includes/functions/functions_edit.php';
		$controller = new WT_Controller_Page();
		$controller
			->setPageTitle($this->getTitle())
			->pageHeader();

		//Configuration settings ===== //
		$action		= WT_Filter::post('action');
		$set_days	= WT_Filter::post('set_days');
		$pending	= WT_Filter::post('pending','' , 0);
		$from		= '';
		$to			= '';

		$earliest	= WT_DB::prepare("SELECT DATE(MIN(change_time)) FROM `##change` WHERE status NOT LIKE 'pending' ")->execute(array())->fetchOne();
		$latest		= WT_DB::prepare("SELECT DATE(MAX(change_time)) FROM `##change` WHERE status NOT LIKE 'pending' ")->execute(array())->fetchOne();

		if (!$set_days){
			$from		= WT_Filter::post('date1');
			$to			= WT_Filter::post('date2');
			$earliest	= WT_Filter::post('date1', '\d\d\d\d-\d\d-\d\d', $earliest);
			$latest		= WT_Filter::post('date2', '\d\d\d\d-\d\d\-\d\d', $latest);
			$date1		= new DateTime($earliest);
			$date2		= new DateTime($latest);
			$days		= $date1->diff($date2)->format("%a") + 1;
			$disp_from	= $earliest ? strtoupper(date('d M Y', strtotime($earliest))) : '';
			$disp_to	= $latest ? strtoupper(date('d M Y', strtotime($latest))) : '';
		}

		$rows = WT_DB::prepare(
			"SELECT xref".
			" FROM `##change`" .
			" WHERE status='pending' AND gedcom_id=?" .
			" GROUP BY xref"
		)->execute(array(WT_GED_ID))->fetchAll();
		$pending_changes = array();
		foreach ($rows as $row) {
			$pending_changes[] = $row->xref;
		}

		if ($set_days){
			$recent_changes = get_recent_changes(WT_CLIENT_JD - $set_days);
		} else {
			$rows = WT_DB::prepare(
				"SELECT xref".
				" FROM `##change`" .
				" WHERE status='accepted' AND gedcom_id=?" .
				" AND change_time >= ?" .
				" AND change_time <= ?" .
				" GROUP BY xref"
			)->execute(array(WT_GED_ID, date('Y-m-d', strtotime($from)), date('Y-m-d', strtotime($to))))->fetchAll();
			$recent_changes = array();
			foreach ($rows as $row) {
				$recent_changes[] = $row->xref;
			}

		}

		// Prepare table headers and footers
		$table_header = '
			<table class="changes width100">
				<thead>
					<tr>
						<th>&nbsp;</th>
						<th>' . WT_I18N::translate('Record') . '</th>
						<th>' . WT_Gedcom_Tag::getLabel('CHAN') . '</th>
						<th>' . WT_I18N::translate('Username') . '</th>
						<th>DATE</th>
						<th>SORTNAME</th>
					</tr>
				</thead>
				<tbody>
		';

		$table_footer = '
			</tbody></table>
		';

		// Common settings
		$content = '
			<div id="resource-page" class="recent_changes" style="margin: auto; width: 90%;">
				<h2>' . $this->getTitle() . '</h2>
				<h5>' . $this->getDescription() . '</h5>
				<div class="noprint">
					<form name="changes" id="changes" method="post" action="module.php?mod=' . $this->getName() . '&mod_action=show">
						<input type="hidden" name="action" value="?">
						<div class="chart_options">
							<label for = "DATE1">' . WT_I18N::translate('Starting range of change dates') . '</label>
							<input type="text" name="date1" id="DATE1" value="' . ($set_days ? '' : $disp_from) . '">' . print_calendar_popup("DATE1") . '
						</div>
						<div class="chart_options">
							<label for = "DATE2">' . WT_I18N::translate('Ending range of change dates') . '</label>
							<input type="text" name="date2" id="DATE2" value="' . ($set_days ? '' : $disp_to) . '">' . print_calendar_popup("DATE2") . '
						</div>
						<div class="chart_options">
							<label for = "DAYS">' . WT_I18N::translate('Number of days to show') . '</label>
							<input type="text" name="set_days" id="DAYS" value="' . ($set_days ? $set_days : '') . '">
						</div>
						<div class="chart_options">
						<label>' . WT_I18N::translate('Show pending changes') . '</label>' .
							edit_field_yes_no('pending', $pending) .'
						</div>
						<button class="btn btn-primary show" type="submit">
							<i class="fa fa-eye"></i>' . WT_I18N::translate('show') . '
						</button>
					</form>
				</div>
				<hr style="clear:both;">
		';

		if (($recent_changes || $pending_changes) && $action) {
			$controller
				->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
				->addInlineJavascript('
					jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
					jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
					jQuery(".changes").dataTable({
						dom: \'<"H"pf<"dt-clear">irl>t<"F"pl>\',
						' . WT_I18N::datatablesI18N() . ',
						autoWidth: false,
						paging: true,
						pagingType: "full_numbers",
						lengthChange: true,
						filter: true,
						info: true,
						jQueryUI: true,
						sorting: [[4,"desc"], [5,"asc"]],
						displayLength: 20,
						"aoColumns": [
							/* 0-Type */     {"bSortable": false, "sClass": "center"},
							/* 1-Record */   {"iDataSort": 5},
							/* 2-Change */   {"iDataSort": 4},
							/* 3-User */       null,
							/* 4-DATE */     {"bVisible": false},
							/* 5-SORTNAME */ {"sType": "unicode", "bVisible": false}
						]
					});
				');
			// Print pending changes
			if ($pending_changes && $pending) {
				$content .= '<h3>' . WT_I18N::translate('Pending changes') . '</h3>';
				// table headers
				$content .= $table_header;
				//-- table body
				$content .= $this->change_data($pending_changes);
				//-- table footer
				$content .= $table_footer;
			}
			// Print approved changes
			if ($recent_changes) {
				$content .= '
					<h3>' .
						($set_days ? WT_I18N::plural('Changes in the last day', 'Changes in the last %s days', $set_days, WT_I18N::number($set_days)) : WT_I18N::translate('%1$s - %2$s (%3$s days)', $disp_from, $disp_to, WT_I18N::number($days))) . '
					</h3>';
				// table headers
				$content .= $table_header;
				//-- table body
				$content .= $this->change_data($recent_changes);
				//-- table footer
				$content .= $table_footer;
			} else {
				$content .= WT_I18N::translate('There have been no changes within the last %s days.', WT_I18N::number($days));
			}
		}
		$content .= '</div>';

		echo $content;
	}

	private function change_data ($type) {
		$change_data = '';
		foreach ($type as $change_id) {
			$record = WT_GedcomRecord::getInstance($change_id);
			if (!$record || !$record->canDisplayDetails()) {
				continue;
			}
			$change_data .= '
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
						$change_data .= '<a href="'. $record->getHtmlUrl() .'">'. $icon . '</a>
					</td>';
					//-- Record name(s)
					$name = $record->getFullName();
					$change_data .= '<td class="wrap">
						<a href="'. $record->getHtmlUrl() .'">'. $name . '</a>';
						if ($indi) {
							$change_data .= '<p>' . $record->getLifeSpan() . '</p>';
							$addname = $record->getAddName();
							if ($addname) {
								$change_data .= '
									<div class="indent">
										<a href="'. $record->getHtmlUrl() .'">'. $addname . '</a>
									</div>';
							}
						}
					$change_data .= '</td>';
					//-- Last change date/time
					$change_data .= '<td class="wrap">' . $record->LastChangeTimestamp() . '</td>';
					//-- Last change user
					$change_data .= '<td class="wrap">' . $record->LastChangeUser() . '</td>';
					//-- change date (sortable) hidden by datatables code
					$change_data .= '<td>' . $record->LastChangeTimestamp(true) . '</td>';
					//-- names (sortable) hidden by datatables code
					$change_data .= '<td>' . $record->getSortName() . '</td>
				</tr>
			';
		}
		return $change_data;
	}

}
