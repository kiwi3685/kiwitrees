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

class page_menu_WT_Module extends WT_Module implements WT_Module_Menu {
	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of a module/menu */ WT_I18N::translate('Edit');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the “Edit” module */ WT_I18N::translate('An edit menu for individuals, families, sources, etc.');
	}

	// Implement WT_Module_Menu
	public function defaultMenuOrder() {
		return 20;
	}

	// Extend class WT_Module
	public function defaultAccessLevel() {
		return WT_PRIV_USER;
	}

	// Implement WT_Module_Menu
	public function MenuType() {
		return 'main';
	}

	// Implement WT_Module_Menu
	public function getMenu() {
		global $controller;

		$menu = null;
		if (empty($controller)) {
			return null;
		}

		if (WT_USER_CAN_EDIT && method_exists($controller, 'getEditMenu')) {
			$menu = $controller->getEditMenu();
		}
		return $menu;
	}
}
