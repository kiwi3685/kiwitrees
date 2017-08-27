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

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class report_todo_WT_Module extends WT_Module implements WT_Module_Report {

	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module. Tasks that need further research. */ WT_I18N::translate('Research tasks');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of “Research tasks” module */ WT_I18N::translate('A list of tasks and activities that are linked to the family tree.');
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

	// Implement WT_Module_Report
	public function getReportMenus() {
		global $controller;

		$menus	= array();
		$menu	= new WT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&mod_action=show',
			'menu-report-' . $this->getName()
		);
		$menus[] = $menu;

		return $menus;
	}

	// Implement class WT_Module_Report
	public function show() {
		global $controller, $GEDCOM;
		require_once WT_ROOT.'includes/functions/functions_edit.php';
		$controller = new WT_Controller_Page();

		// Configuration settings ===== //
		$action				= WT_Filter::post('action');
		$show_unassigned	= WT_Filter::post('show_unassigned', '', 1);
		$show_other			= WT_Filter::post('show_other', '', 1);
		$show_future		= WT_Filter::post('show_future', '', 1);

		$table_id = 'ID'.(int)(microtime(true)*1000000); // create a unique ID
		$controller
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
			->addInlineJavascript('
				jQuery("#' .$table_id. '").dataTable( {
					dom: \'<"H"pf<"dt-clear">irl>t<"F"pl>\',
					' . WT_I18N::datatablesI18N() . ',
					autoWidth: false,
					paging: true,
					pagingType: "full_numbers",
					lengthChange: true,
					filter: true,
					info: true,
					jQueryUI: true,
					sorting: [[0,"asc"]],
					displayLength: 20,
					"aoColumns": [
						/* 0-DATE */  		{ "bVisible": false },
						/* 1-Date */		{ "iDataSort": 0 },
						/* 1-Record */ 		{},
						/* 2-Username */	{},
						/* 3-Text */		{}
					]
				});
			jQuery("#' .$table_id. '").css("visibility", "visible");
			jQuery(".loading-image").css("display", "none");
			');
		$content = '
			<div id="page" class="research_tasks">
				<h2>' . $this->getTitle() . '</h2>
				<div class="noprint">
					<h5>' . $this->getDescription() . '</h5>
					<form name="changes" id="changes" method="post" action="module.php?mod=' . $this->getName() . '&mod_action=show">
						<input type="hidden" name="action" value="?">
						<div class="chart_options">
							<label>' . WT_I18N::translate('Show tasks not assigned to any user') . '</label>' .
							edit_field_yes_no('show_unassigned', $show_unassigned) .'
						</div>
						<div class="chart_options">
							<label>' . WT_I18N::translate('Show tasks assigned to other users') . '</label>' .
							edit_field_yes_no('show_other', $show_other) .'
						</div>
						<div class="chart_options">
							<label>' . WT_I18N::translate('Show tasks that have a date in the future') . '</label>' .
							edit_field_yes_no('show_future', $show_future) .'
						</div>
						<button class="btn btn-primary show" type="submit">
							<i class="fa fa-eye"></i>' . WT_I18N::translate('show') . '
						</button>
					</form>
				</div>
				<hr style="clear:both;">
		';
			($show_unassigned) ? $filter1 = '<p>' . /* I18N: A filter on the research tasks report page */ WT_I18N::translate('Show tasks not assigned to any user') . '</p>' : $filter1 = '';
			($show_other) ? $filter2 = '<p>' . /* I18N: A filter on the research tasks report page */ WT_I18N::translate('Show tasks assigned to other users') . '</p>' : $filter2 = '';
			($show_other) ? $filter3 = '<p>' . /* I18N: A filter on the research tasks report page */ WT_I18N::translate('Show tasks that have a date in the future') . '</p>' : $filter3 = '';

			$filter_list = $filter1 . $filter2 . $filter3;

			// Display results
			if ($action){
				$content .= '<div id="report_header">
					<h4>' . WT_I18N::translate('Listing research tasks based on these filters') . '</h4>
					<p>' .  $filter_list . '</p>
				</div>
				<div class="loading-image">&nbsp;</div>
				<table id="' .$table_id. '" style="visibility:hidden; width:100%;">
					<thead>
						<tr>
							<th>DATE</th>
							<th>' . WT_Gedcom_Tag::getLabel('DATE') . '</th>
							<th>' . WT_I18N::translate('Record') . '</th>';
							$content .= '<th>' . WT_Gedcom_Tag::getLabel('TEXT').'</th>';
							if ($show_unassigned || $show_other) {
								$content .= '<th>' . WT_I18N::translate('Username') . '</th>';
							}
						$content .= '</tr>
					</thead>
					<tbody>';
						$found	= false;
						$end_jd	= $show_future ? 99999999 : WT_CLIENT_JD;
						foreach (get_calendar_events(0, $end_jd, '_TODO', WT_GED_ID) as $todo) {
							$record = WT_GedcomRecord::getInstance($todo['id']);
							if ($record && $record->canDisplayDetails()) {
								$user_name = preg_match('/\n2 _WT_USER (.+)/', $todo['factrec'], $match) ? $match[1] : '';
								$type = $record->getType();
								if ($user_name == WT_USER_NAME || !$user_name && $show_unassigned || $user_name && $show_other) {
									$content .= '<tr>
										<td>' . $todo['date']->JD() . '</td>
										<td class="wrap">' . $todo['date']->Display(empty($SEARCH_SPIDER)) . '</td>
										<td class="wrap">
											<a href="' . $record->getHtmlUrl() . '">' .
												($type == 'INDI' ? $record->getLifespanName() : $record->getFullName()) .'
											</a>
										</td>';
										$text = preg_match('/^1 _TODO (.+)/', $todo['factrec'], $match) ? $match[1] : '';
										$content .= '<td class="wrap">'.$text.'</td>';
										if ($show_unassigned || $show_other) {
											$content .= '<td class="wrap">'.$user_name.'</td>';
										}
									$content .= '</tr>';
									$found = true;
								}
							}
						}
					$content .= '</tbody>
				</table>';
				if (!$found) {
					$content .= '<p>'.WT_I18N::translate('There are no research tasks in this family tree.').'</p>';
				}
			}
			$content .= '</div>
		';

		echo $content;

	}

}
