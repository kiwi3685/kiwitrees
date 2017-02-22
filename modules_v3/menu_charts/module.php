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

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class menu_charts_WT_Module extends WT_Module implements WT_Module_Menu {
	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of a module/menu */ WT_I18N::translate('Charts menu');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the “Edit” module */ WT_I18N::translate('The Charts menu item');
	}

	// Implement WT_Module_Menu
	public function defaultMenuOrder() {
		return 30;
	}

	// Implement WT_Module_Menu
	public function defaultAccessLevel() {
		return WT_PRIV_PUBLIC;
	}

	// Implement WT_Module_Menu
	public function MenuType() {
		return 'main';
	}

	// Implement WT_Module_Menu
	public function getMenu() {
		$menu = WT_MenuBar::getChartsMenu();
		return $menu;
	}
}
