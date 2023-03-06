<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net
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

class menu_homepage_KT_Module extends KT_Module implements KT_Module_Menu {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module/menu */ KT_I18N::translate('Home menu');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Edit” module */ KT_I18N::translate('The Home menu item');
	}

	// Implement KT_Module_Menu
	public function defaultMenuOrder() {
		return 10;
	}

	// Extend class KT_Module_Menu
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement KT_Module_Menu
	public function MenuType() {
		return 'main';
	}

	// Implement KT_Module_Menu
	public function getMenu() {
		$menu = KT_MenuBar::getGedcomMenu();
		return $menu;
	}
}
