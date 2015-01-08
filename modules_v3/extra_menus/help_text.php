<?php
// Module help text.
//
// This file is included from the application help_text.php script.
// It simply needs to set $title and $text for the help topic $help_topic
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2015 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
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
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
// $Id: help_text.php 13128 2012-01-12 04:03:13Z lukasz $

if (!defined('WT_WEBTREES') || !defined('WT_SCRIPT_NAME') || WT_SCRIPT_NAME!='help_text.php') {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

switch ($help) {
case 'add_menu':
	$title=WT_I18N::translate('Add menu item');
	$text=WT_I18N::translate('The menu should contains menu items. Using this option you can add menu details..').
	'<p>IMPORTANT: if you add more than one menu item you might also want to add different sub-menu images for each. They need to be added to the images folder and referenced in the style sheet of each theme used on your site. An example image called Test.png is given in each standard theme, for those themes that use menu images.';
	break;


case 'menu_position':
	$title=WT_I18N::translate('Menu position');
	$text=WT_I18N::translate('This field controls the order in which the menu items are displayed.').'<br><br>'.WT_I18N::translate('You do not have to enter the numbers sequentially. If you leave holes in the numbering scheme, you can insert other menu items later. For example, if you use the numbers 1, 6, 11, 16, you can later insert menu items with the missing sequence numbers. Negative numbers and zero are allowed, and can be used to insert menu items in front of the first one.').'<br><br>'.WT_I18N::translate('When more than one menu item has the same position number, only one of these menu items will be visible.');
	break;

case 'menu_visibility':
	$title=WT_I18N::translate('Menu visibility');
	$text=WT_I18N::translate('You can determine whether this menu item will be visible regardless of family tree, or whether it will be visible only to the current family tree.').
	'<br><ul><li><b>'.WT_I18N::translate('All').'</b>&nbsp;&nbsp;&nbsp;'.WT_I18N::translate('The menu item will appear, regardless of family tree.').'</li><li><b>'.get_gedcom_setting(WT_GED_ID, 'title').'</b>&nbsp;&nbsp;&nbsp;'.WT_I18N::translate('The menu item will appear only in the currently active family trees.').'</li></ul>';
	break;

case 'delete_menu':
	$title=WT_I18N::translate('Delete menu');
	$text=WT_I18N::translate('This option will let you delete a menu item.');
	break;

case 'edit_menu':
	$title=WT_I18N::translate('Edit menu');
	$text=WT_I18N::translate('This option will let you edit a menu item.');
	break;

case 'movedown_menu':
	$title=WT_I18N::translate('Move menu down');
	$text=WT_I18N::translate('This option will let you move a menu item downwards.').'<br><br>'.WT_I18N::translate('Each time you use this option, the position number of this menu item is increased by one. You can achieve the same effect by editing the menu item and changing the menu position field.').'<br><br>'.WT_I18N::translate('When more than one menu item has the same position number, only one of these menu items will be visible.');
	break;

case 'moveup_menu':
	$title=WT_I18N::translate('Move menu up');
	$text=WT_I18N::translate('This option will let you move a menu item upwards.').'<br><br>'.WT_I18N::translate('Each time you use this option, the position number of this menu item is reduced by one. You can achieve the same effect by editing the menu item and changing the menu position field.').'<br><br>'.WT_I18N::translate('When more than one menu item has the same position number, only one of these menu items will be visible.');
	break;

	case 'new_tab':
	$title=WT_I18N::translate('Open menu in new tab or window');
	$text=WT_I18N::translate('Normally you would expect clicking on a menu item to open that page in the existing browser window or tab. But if you need it to open in a new window or tab you can tick this option.');
	break;
}
