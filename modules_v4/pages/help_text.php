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

if (!defined('WT_KIWITREES') || !defined('WT_SCRIPT_NAME') || WT_SCRIPT_NAME!='help_text.php') {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

switch ($help) {
case 'pages_position':
	$title=WT_I18N::translate('Page position');
	$text=WT_I18N::translate('This field controls the order in which the pages are displayed.').'<br><br>'.WT_I18N::translate('You do not have to enter the numbers sequentially. If you leave holes in the numbering scheme, you can insert other pages later. For example, if you use the numbers 1, 6, 11, 16, you can later insert pages with the missing sequence numbers. Negative numbers and zero are allowed, and can be used to insert pages in front of the first one.').'<br><br>'.WT_I18N::translate('When more than one page has the same position number, only one of these pages will be visible.');
	break;

case 'pages_visibility':
	$title=WT_I18N::translate('Page visibility');
	$text=WT_I18N::translate('You can determine whether this page will be visible regardless of family tree, or whether it will be visible only to the current family tree.').
	'<br><ul><li><b>'.WT_I18N::translate('All').'</b>&nbsp;&nbsp;&nbsp;'.WT_I18N::translate('The page will always appear, regardless of family tree.').'</li><li><b>'.get_gedcom_setting(WT_GED_ID, 'title').'</b>&nbsp;&nbsp;&nbsp;'.WT_I18N::translate('The pages will appear only in the currently active family trees\'s pages.').'</li></ul>';
	break;

case 'pages_language':
	$title=WT_I18N::translate('Page language');
	$text=WT_I18N::translate('Either leave all languages un-ticked to display the page contents in every language, or tick the specific languages you want to display it for.<br><br>To create translated pages for different languages create multiple copies setting the appropriate language only for each version.');
	break;

case 'pages_title':
	$title=WT_I18N::translate('Summary page and Menu title');
	$text=WT_I18N::translate('This is a brief title. It is displayed in two places.<ol><li> It is used as the main menu item name if your theme uses names, and you have more than one page. If you only have one page, then the title of that page is used. It should be kept short or it might break the menu display.</li><li>It is used as the main title on the display page, above the tabbed list of pages.</li></ol>');
	break;

case 'pages_description':
	$title=WT_I18N::translate('Page description');
	$text=WT_I18N::translate('This is a sub-heading that will display below the Summary page title, above the tabbed list of pages. It can contain HTML elements including an image if you wish. Simply ensure there is no content if you do not want to display it.');
	break;
}
