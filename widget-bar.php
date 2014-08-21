<?php
// My page page allows a logged in user the abilty
// to keep bookmarks, see a list of upcoming events, etc.
//
// webtrees: Web based Family History software
// Copyright (C) 2012 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2010  PGV Development Team
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

//define('WT_SCRIPT_NAME', 'widget-bar.php');
//require './includes/session.php';

global $controller, $ctype;

//-- get the widgets list
//$widgets=get_user_widgets(WT_USER_ID);

$widgets = WT_Module::getActiveWidgets(WT_GED_ID, WT_PRIV_HIDE);
//print_r($widgets);
echo '
	<div id="widget-bar">';
		foreach ($widgets as $module_name=>$module) {
			$class_name = $module_name.'_WT_Module';
			$module = new $class_name;
			echo '<div class="widget">';
			if ($SEARCH_SPIDER || !$module->loadAjax()) {
				// Load the widget directly
				$module->getWidget($module_name);
			} else {
				// Load the widget asynchronously
				echo '<div id="widget_', $module_name, '"><div class="loading-image">&nbsp;</div></div>';
				$controller->addInlineJavascript(
					'jQuery("#widget_'.$module_name.'").load("index.php?ctype='.$ctype.'&action=ajax&module_name='.$module_name.'");'
				);
			}
			echo '</div>';
		}

		// link for changing widgets
		echo '<div id="link_change_widgets">';
			if (WT_USER_ID) echo '<a href="index_edit.php?user_id='.WT_USER_ID.'" onclick="return modalDialog(\'index_edit.php?user_id='.WT_USER_ID.'\', \'', WT_I18N::translate('Change the widgets on this page'), '\');">', WT_I18N::translate('Change the widgets on this page'), '</a>';
		echo '</div>';
echo '</div>';
	