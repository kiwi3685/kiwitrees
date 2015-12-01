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

class resource_todo_WT_Module extends WT_Module implements WT_Module_menu {
	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module. Tasks that need further research. */ WT_I18N::translate('Research tasks page');
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

		//Configuration settings ===== //
		$show_unassigned	= true; // Show research tasks that are not assigned to any user
		$show_other			= true; // Show research tasks that are assigned to other users
		$show_future		= true; // Show research tasks that have a date in the future
		// ============================ //

		$template			= true;
		$id					= $this->getName();
		$class				= $this->getName();
		$title				= '';
		$table_id = 'ID'.(int)(microtime()*1000000); // create a unique ID
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
			<style>#research_tasks-page table th, #research_tasks-page table td {padding:8px;}</style>
			<div id="research_tasks-page" style="margin: auto; width: 90%;">
				<h2>' . $this->getTitle() . '</h2>
				<div class="loading-image">&nbsp;</div>
				<table id="' .$table_id. '" style="visibility:hidden; width:100%;">
					<thead>
						<tr>
							<th>DATE</th>
							<th>' . WT_Gedcom_Tag::getLabel('DATE') . '</th>
							<th>' . WT_I18N::translate('Record') . '</th>';
							if ($show_unassigned || $show_other) {
								$content .= '<th>'.WT_I18N::translate('Username').'</th>';
							}
							$content .= '<th>'.WT_Gedcom_Tag::getLabel('TEXT').'</th>
						</tr>
					</thead>
					<tbody>';
						$found	= false;
						$end_jd	= $show_future ? 99999999 : WT_CLIENT_JD;

						foreach (get_calendar_events(0, $end_jd, '_TODO', WT_GED_ID) as $todo) {
							$record = WT_GedcomRecord::getInstance($todo['id']);
							if ($record && $record->canDisplayDetails()) {
								$user_name = preg_match('/\n2 _WT_USER (.+)/', $todo['factrec'], $match) ? $match[1] : '';
								if ($user_name == WT_USER_NAME || !$user_name && $show_unassigned || $user_name && $show_other) {
									$content .= '<tr>
										<td>' . $todo['date']->JD() . '</td>
										<td class="wrap">' . $todo['date']->Display(empty($SEARCH_SPIDER)) . '</td>
										<td class="wrap"><a href="' . $record->getHtmlUrl() . '">' . $record->getFullName() . '</a></td>';
										if ($show_unassigned || $show_other) {
											$content .= '<td class="wrap">'.$user_name.'</td>';
										}
										$text = preg_match('/^1 _TODO (.+)/', $todo['factrec'], $match) ? $match[1] : '';
										$content .= '<td class="wrap">'.$text.'</td>
									</tr>';
									$found = true;
								}
							}
						}
					$content .= '</tbody>
				</table>';
				if (!$found) {
					$content .= '<p>'.WT_I18N::translate('There are no research tasks in this family tree.').'</p>';
				}
			$content .= '</div>
		';

		echo $content;

	}

}
