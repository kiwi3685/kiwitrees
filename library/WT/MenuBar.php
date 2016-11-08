<?php
// System for generating menus.
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2010 PGV Development Team
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
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class WT_MenuBar {
	public static function getGedcomMenu() {
		if (count(WT_Tree::getAll()) === 1 || WT_Site::preference('ALLOW_CHANGE_GEDCOM') === '0') {
			$menu = new WT_Menu(WT_I18N::translate('Home'), 'index.php?ctype=gedcom&amp;ged='.WT_GEDURL, 'menu-tree');
		} else {
			$menu = new WT_Menu(WT_I18N::translate('Home'), '#', 'menu-tree');
			foreach (WT_Tree::getAll() as $tree) {
				$submenu = new WT_Menu(
					$tree->tree_title_html,
					'index.php?ctype=gedcom&amp;ged='.$tree->tree_name_url,
					'menu-tree-'.$tree->tree_id // Cannot use name - it must be a CSS identifier
				);
				$menu->addSubmenu($submenu);
			}
		}
		return $menu;
	}

	public static function getMyAccountMenu() {
		global $PEDIGREE_FULL_DETAILS, $PEDIGREE_LAYOUT;

		$showFull = ($PEDIGREE_FULL_DETAILS) ? 1 : 0;
		$showLayout = ($PEDIGREE_LAYOUT) ? 1 : 0;

		if (!WT_USER_ID) {
			return null;
		}

		//-- main menu
		$menu = new WT_Menu(getUserFullName(WT_USER_ID), '#', 'menu-mylogout');

		//-- editaccount submenu
			$submenu = new WT_Menu(WT_I18N::translate('My account'), 'edituser.php', 'menu-myaccount');
			$menu->addSubmenu($submenu);
		if (WT_USER_GEDCOM_ID) {
			//-- my_pedigree submenu
			$submenu = new WT_Menu(
				WT_I18N::translate('My pedigree'),
				'pedigree.php?ged='.WT_GEDURL.'&amp;rootid='.WT_USER_GEDCOM_ID."&amp;show_full={$showFull}&amp;talloffset={$showLayout}",
				'menu-mypedigree'
			);
			$menu->addSubmenu($submenu);
			//-- my_indi submenu
			$submenu = new WT_Menu(WT_I18N::translate('My individual record'), 'individual.php?pid='.WT_USER_GEDCOM_ID.'&amp;ged='.WT_GEDURL, 'menu-myrecord');
			$menu->addSubmenu($submenu);
		}
		if (WT_USER_GEDCOM_ADMIN) {
			//-- admin submenu
			$submenu = new WT_Menu(WT_I18N::translate('Administration'), 'admin.php', 'menu-admin');
			$menu->addSubmenu($submenu);
		}
		//-- logout
		$submenu = new WT_Menu(logout_link(), '', 'menu-logout');
		$menu->addSubmenu($submenu);

		return $menu;
	}

	public static function getChartsMenu() {
		global $SEARCH_SPIDER, $controller;
		if ($SEARCH_SPIDER || !WT_GED_ID) {
			return null;
		}
		$active_charts = WT_Module::getActiveCharts();
		if ($active_charts) {
			$indi_xref = $controller->getSignificantIndividual()->getXref();
			$PEDIGREE_ROOT_ID = get_gedcom_setting(WT_GED_ID, 'PEDIGREE_ROOT_ID');
			$menu = new WT_Menu(WT_I18N::translate('Charts'), '#', 'menu-chart');
			uasort($active_charts, create_function('$x,$y', 'return utf8_strcasecmp((string)$x, (string)$y);'));
			foreach ($active_charts as $chart) {
				foreach ($chart->getChartMenus() as $submenu) {
					$menu->addSubmenu($submenu);
				}
			}
			return $menu;
		}
	}

	public static function getListsMenu() {
		global $SEARCH_SPIDER, $controller;

		$active_lists = WT_Module::getActiveLists();

		if ($SEARCH_SPIDER || !$active_lists) {
			return null;
		}

		$menu = new WT_Menu(WT_I18N::translate('Lists'), '#', 'menu-list');
		uasort($active_lists, create_function('$x,$y', 'return utf8_strcasecmp((string)$x, (string)$y);'));
		foreach ($active_lists as $list) {
			foreach ($list->getListMenus() as $submenu) {

				$menu->addSubmenu($submenu);
			}
		}
		return $menu;
	}

	/**
	* get the reports menu
	* @return WT_Menu the menu item
	*/
	public static function getReportsMenu($pid='', $famid='') {
		global $SEARCH_SPIDER;

		$active_reports = WT_Module::getActiveReports();
		if ($SEARCH_SPIDER || !$active_reports) {
			return null;
		}

		$menu = new WT_Menu(WT_I18N::translate('Reports'), '#', 'menu-report');

		foreach ($active_reports as $report) {
			foreach ($report->getReportMenus() as $submenu) {
				$menu->addSubmenu($submenu);
			}
		}
		return $menu;
	}

	/**
	* get the resources menu
	* @return WT_Menu the menu item
	*/
	public static function getResourcesMenu($pid='', $famid='') {
		global $SEARCH_SPIDER;

		$active_resources = WT_Module::getActiveResources();

		if ($SEARCH_SPIDER || !$active_resources) {
			return null;
		}

		$menu = new WT_Menu(WT_I18N::translate('Resources'), '#', 'menu-resources');

		foreach ($active_resources as $resources) {
			foreach ($resources->getResourceMenus() as $submenu) {
				$menu->addSubmenu($submenu);
			}
		}
		return $menu;
	}


	public static function getSearchMenu() {
		global $SEARCH_SPIDER;

		if ($SEARCH_SPIDER) {
			return null;
		}

		$menu = new WT_Menu(WT_I18N::translate('Search'), 'search.php?ged=' . WT_GEDURL, 'menu-search');
		return $menu;
	}

	public static function getLanguageMenu() {
		global $SEARCH_SPIDER;
		$languages = WT_I18N::used_languages();

		if ($SEARCH_SPIDER) {
			return null;
		} else {
			$menu = new WT_Menu(WT_I18N::translate('Language'), '#', 'menu-language');
			foreach ($languages as $lang=>$name) {
				$submenu=new WT_Menu(WT_I18N::translate($name), get_query_url(array('lang'=>$lang), '&amp;'), 'menu-language-'.$lang);
				if (WT_LOCALE == $lang) {$submenu->addClass('','','lang-active');}
				$menu->addSubMenu($submenu);
			}
			if (count($menu->submenus)>1) {
				return $menu;
			} else {
				return null;
			}
		}
	}

	public static function getFavoritesMenu() {
		global $controller, $SEARCH_SPIDER;

		$show_user_favs = WT_USER_ID && array_key_exists('widget_favorites', WT_Module::getActiveModules());
		$show_gedc_favs = array_key_exists('gedcom_favorites', WT_Module::getActiveModules());

		if ($show_user_favs && !$SEARCH_SPIDER) {
			if ($show_gedc_favs && !$SEARCH_SPIDER) {
				$favorites = array_merge(
					gedcom_favorites_WT_Module::getFavorites(WT_GED_ID),
					widget_favorites_WT_Module::getFavorites(WT_USER_ID)
				);
			} else {
				$favorites = widget_favorites_WT_Module::getFavorites(WT_USER_ID);
			}
		} else {
			if ($show_gedc_favs && !$SEARCH_SPIDER) {
				$favorites = gedcom_favorites_WT_Module::getFavorites(WT_GED_ID);
			} else {
				return null;
			}
		}
		// Sort $favorites alphabetically?

		$menu = new WT_Menu(WT_I18N::translate('Favorites'), '#', 'menu-favorites');

		foreach ($favorites as $favorite) {
			switch($favorite['type']) {
			case 'URL':
				$submenu = new WT_Menu($favorite['title'], $favorite['url']);
				$menu->addSubMenu($submenu);
				break;
			case 'INDI':
			case 'FAM':
			case 'SOUR':
			case 'OBJE':
			case 'NOTE':
				$obj = WT_GedcomRecord::getInstance($favorite['gid']);
				if ($obj && $obj->canDisplayName()) {
					$submenu = new WT_Menu($obj->getFullName(), $obj->getHtmlUrl());
					$menu->addSubMenu($submenu);
				}
				break;
			}
		}

		if ($show_user_favs) {
			if (isset($controller->record) && $controller->record instanceof WT_GedcomRecord) {
				$submenu = new WT_Menu(WT_I18N::translate('Add to favorites'), '#');
				$submenu->addOnclick("jQuery.post('module.php?mod=widget_favorites&amp;mod_action=menu-add-favorite',{xref:'".$controller->record->getXref()."'},function(){location.reload();})");
				$menu->addSubMenu($submenu);
			}
		}
		return $menu;
	}

	public static function getMainMenus() {
		$menus = array();
		foreach (WT_Module::getActiveMenus() as $module) {
			if ($module->MenuType() == 'main' || !$module->MenuType()) {
				$menu = $module->getMenu();
				if ($menu) {
					$menus[] = $menu;
				}
			}
		}
		return $menus;
	}

	public static function getOtherMenus() {
		$menus = array();
		foreach (WT_Module::getActiveMenus() as $module) {
			if ($module->MenuType() == 'other') {
				$menu = $module->getMenu();
				if ($menu) {
					$menus[] = $menu;
				}
			}
		}
		return $menus;
	}

}
