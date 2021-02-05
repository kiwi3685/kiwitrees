<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2021 kiwitrees.net
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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class widget_todo_KT_Module extends KT_Module implements KT_Module_Widget {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module.  Tasks that need further research.  */ KT_I18N::translate('Research tasks');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of “Research tasks” module */ KT_I18N::translate('A list of tasks and activities that are linked to the family tree.');
	}

	// Implement class KT_Module_Widget
	public function getWidget($widget_id, $template=true, $cfg=null) {
		global $ctype, $controller;

		$show_unassigned	= get_block_setting($widget_id, 'show_unassigned', true);
		$show_other			= get_block_setting($widget_id, 'show_other',      true);
		$show_future		= get_block_setting($widget_id, 'show_future',     true);
		if ($cfg) {
			foreach (array('show_unassigned', 'show_other', 'show_future') as $name) {
				if (array_key_exists($name, $cfg)) {
					$$name=$cfg[$name];
				}
			}
		}

		$id=$this->getName();
		$class=$this->getName();

		if (KT_USER_GEDCOM_ADMIN) {
			$title='<i class="icon-admin" title="'.KT_I18N::translate('Configure').'" onclick="modalDialog(\'block_edit.php?block_id='.$widget_id.'\', \''.$this->getTitle().'\');"></i>';
		} else {
			$title='';
		}
		$title.=$this->getTitle().help_link('todo', $this->getName());

		$table_id = 'ID'.(int)(microtime(true)*1000000); // create a unique ID
		$controller
			->addExternalJavascript(KT_JQUERY_DATATABLES_URL)
			->addInlineJavascript('
				jQuery("#'.$table_id.'").dataTable( {
				"sDom": \'t\',
				'.KT_I18N::datatablesI18N().',
				"bAutoWidth":false,
				"bPaginate": false,
				"bLengthChange": false,
				"bFilter": false,
				"bInfo": true,
				"bJQueryUI": true,
				"aoColumns": [
					/* 0-DATE */   		{ "bVisible": false },
					/* 1-Date */		{ "iDataSort": 0 },
					/* 1-Record */ 		{},
					/* 2-Username */	{},
					/* 3-Text */		{}
				]
				});
			jQuery("#'.$table_id.'").css("visibility", "visible");
			jQuery(".loading-image").css("display", "none");
			');
		$content = '';
		$content .= '<div class="loading-image">&nbsp;</div>';
		$content .= '<table id="'.$table_id.'" style="visibility:hidden; width:100%;">';
		$content .= '<thead><tr>';
		$content .= '<th>DATE</th>'; //hidden by datables code
		$content .= '<th>'.KT_Gedcom_Tag::getLabel('DATE').'</th>';
		$content .= '<th>'.KT_I18N::translate('Record').'</th>';
		if ($show_unassigned || $show_other) {
			$content .= '<th>'.KT_I18N::translate('Username').'</th>';
		}
		$content .= '<th>'.KT_Gedcom_Tag::getLabel('TEXT').'</th>';
		$content .= '</tr></thead><tbody>';

		$found = false;
		$end_jd = $show_future ? 99999999 : KT_CLIENT_JD;
		foreach (get_calendar_events(0, $end_jd, '_TODO', KT_GED_ID) as $todo) {
			$record=KT_GedcomRecord::getInstance($todo['id']);
			if ($record && $record->canDisplayDetails()) {
				$user_name = preg_match('/\n2 _KT_USER (.+)/', $todo['factrec'], $match) ? $match[1] : '';
				if ($user_name==KT_USER_NAME || !$user_name && $show_unassigned || $user_name && $show_other) {
					$content.='<tr>';
					//-- Event date (sortable)
					$content .= '<td>'; //hidden by datatables code
					$content .= $todo['date']->JD();
					$content .= '</td>';
					$content.='<td class="wrap">'. $todo['date']->Display(empty($SEARCH_SPIDER)).'</td>';
					$content.='<td class="wrap"><a href="'.$record->getHtmlUrl().'">'.$record->getFullName().'</a></td>';
					if ($show_unassigned || $show_other) {
						$content .='<td class="wrap">'.$user_name.'</td>';
					}
					$text = preg_match('/^1 _TODO (.+)/', $todo['factrec'], $match) ? $match[1] : '';
					$content .= '<td class="wrap">'.$text.'</td>';
					$content .= '</tr>';
					$found = true;
				}
			}
		}

		$content .= '</tbody></table>';
		if (!$found) {
			$content.='<p>'.KT_I18N::translate('There are no research tasks in this family tree.').'</p>';
		}

		if ($template) {
			require KT_THEME_DIR.'templates/widget_template.php';
		} else {
			return $content;
		}

	}

	// Implement class KT_Module_Widget
	public function loadAjax() {
		return false;
	}

	// Implement KT_Module_Widget
	public function defaultWidgetOrder() {
		return 40;
	}

	// Implement KT_Module_Menu
	public function defaultAccessLevel() {
		return KT_PRIV_USER;
	}

	// Implement class KT_Module_Widget
	public function configureBlock($widget_id) {
		if (KT_Filter::postBool('save') && KT_Filter::checkCsrf()) {
			set_block_setting($widget_id, 'show_other',      KT_Filter::postBool('show_other'));
			set_block_setting($widget_id, 'show_unassigned', KT_Filter::postBool('show_unassigned'));
			set_block_setting($widget_id, 'show_future',     KT_Filter::postBool('show_future'));
			exit;
		}

		require_once KT_ROOT.'includes/functions/functions_edit.php';

		$show_other=get_block_setting($widget_id, 'show_other', true);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo KT_I18N::translate('Show research tasks that are assigned to other users');
		echo '</td><td class="optionbox">';
		echo edit_field_yes_no('show_other', $show_other);
		echo '</td></tr>';

		$show_unassigned=get_block_setting($widget_id, 'show_unassigned', true);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo KT_I18N::translate('Show research tasks that are not assigned to any user');
		echo '</td><td class="optionbox">';
		echo edit_field_yes_no('show_unassigned', $show_unassigned);
		echo '</td></tr>';

		$show_future=get_block_setting($widget_id, 'show_future', true);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo KT_I18N::translate('Show research tasks that have a date in the future');
		echo '</td><td class="optionbox">';
		echo edit_field_yes_no('show_future', $show_future);
		echo '</td></tr>';
	}
}
