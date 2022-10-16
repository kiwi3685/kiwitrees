<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net
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

#[AllowDynamicProperties]
class KT_MenuBar {
	public static function getGedcomMenu() {
        $menu = new KT_Menu(KT_I18N::translate('Home'), 'index.php?ged=' . KT_GEDURL, 'menu-tree');
		if (count(KT_Tree::getAll())>1 && KT_Site::preference('ALLOW_CHANGE_GEDCOM')) {
			foreach (KT_Tree::getAll() as $tree) {
				$submenu = new KT_Menu(
					$tree->tree_title_html,
					'index.php?ged=' . $tree->tree_name_url,
					'menu-tree-' . $tree->tree_id // Cannot use name - it must be a CSS identifier
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

		if (!KT_USER_ID) {
			return null;
		}

		//-- main menu
		$menu = new KT_Menu(getUserFullName(KT_USER_ID), '#', 'menu-mylogout');

		//-- editaccount submenu
			$submenu = new KT_Menu(KT_I18N::translate('My account'), 'edituser.php', 'menu-myaccount');
			$menu->addSubmenu($submenu);
		if (KT_USER_GEDCOM_ID) {
			//-- my_pedigree submenu
			$submenu = new KT_Menu(
				KT_I18N::translate('My pedigree'),
				'pedigree.php?ged='.KT_GEDURL.'&amp;rootid='.KT_USER_GEDCOM_ID."&amp;show_full={$showFull}&amp;talloffset={$showLayout}",
				'menu-mypedigree'
			);
			$menu->addSubmenu($submenu);
			//-- my_indi submenu
			$submenu = new KT_Menu(KT_I18N::translate('My individual record'), 'individual.php?pid='.KT_USER_GEDCOM_ID.'&amp;ged='.KT_GEDURL, 'menu-myrecord');
			$menu->addSubmenu($submenu);
		}
		//-- admin submenu (only if not already included in main or other menu)
		if (
			KT_USER_GEDCOM_ADMIN
			 && !(
				 array_key_exists('menu_admin_main', KT_Module::getActiveMenus()) ||
				 array_key_exists('menu_admin_other', KT_Module::getActiveMenus())
			)
		) {
			$submenu = new KT_Menu(KT_I18N::translate('Administration'), 'admin.php', 'menu-admin');
			$menu->addSubmenu($submenu);
		}
		//-- logout
		$submenu = new KT_Menu(logout_link(), '', 'menu-logout');
		$menu->addSubmenu($submenu);

		return $menu;
	}

	public static function getAdminMenu() {
		$menu = new KT_Menu(KT_I18N::translate('Administration'), 'admin.php', 'menu-admin');
		return $menu;
	}


	public static function getChartsMenu() {
		global $SEARCH_SPIDER, $controller;
		if ($SEARCH_SPIDER || !KT_GED_ID) {
			return null;
		}
		$active_charts = KT_Module::getActiveCharts();
		if ($active_charts) {
			$indi_xref = $controller->getSignificantIndividual()->getXref();
			$PEDIGREE_ROOT_ID = get_gedcom_setting(KT_GED_ID, 'PEDIGREE_ROOT_ID');
			$menu = new KT_Menu(KT_I18N::translate('Charts'), '#', 'menu-chart');
			uasort($active_charts, function ($x, $y) {
				return KT_I18N::strcasecmp((string)$x, (string)$y);
			});

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

		$active_lists = KT_Module::getActiveLists();

		if ($SEARCH_SPIDER || !$active_lists) {
			return null;
		}

		$menu = new KT_Menu(KT_I18N::translate('Lists'), '#', 'menu-list');
		uasort($active_lists, function ($x, $y) {
			return KT_I18N::strcasecmp($x->getTitle(), $y->getTitle());
		});

		foreach ($active_lists as $list) {
			foreach ($list->getListMenus() as $submenu) {
				$menu->addSubmenu($submenu);
			}
		}
		return $menu;
	}

	/**
	* get the reports menu
	* @return KT_Menu the menu item
	*/
	public static function getReportsMenu($pid='', $famid='') {
		global $SEARCH_SPIDER;

		$active_reports = KT_Module::getActiveReports();

		if ($SEARCH_SPIDER || !$active_reports) {
			return null;
		}

		$menu = new KT_Menu(KT_I18N::translate('Reports'), '#', 'menu-report');

		foreach ($active_reports as $report) {
			foreach ($report->getReportMenus() as $submenu) {
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

		$menu = new KT_Menu(KT_I18N::translate('Search'), 'search.php?ged=' . KT_GEDURL, 'menu-search');
		return $menu;
	}

	public static function getLanguageMenu() {
		global $SEARCH_SPIDER;

		// filter and sort by localised name
		$code_list = KT_Site::preference('LANGUAGES');
		if ($code_list) {
			$languages = explode(',', $code_list);
		} else {
			$languages = array(
				'ar', 'bg', 'ca', 'cs', 'da', 'de', 'el', 'en_GB', 'en_US', 'es',
				'et', 'fi', 'fr', 'he', 'hr', 'hu', 'is', 'it', 'ka', 'lt', 'nb',
				'nl', 'nn', 'pl', 'pt', 'ru', 'sk', 'sv', 'tr', 'uk', 'vi', 'zh',
			);
		}
		$installed = KT_I18N::installed_languages();
		foreach ($installed as $code=>$name) {
			if (in_array($code, $languages)) {
				$installed[$code] = KT_I18N::translate($name);
			} else {
				unset($installed[$code]);
			}
		}
		asort($installed);

		if ($SEARCH_SPIDER) {
			return null;
		} else {
			$menu = new KT_Menu(KT_I18N::translate('Language'), '#', 'menu-language');
			foreach ($installed as $code=>$name) {
				$submenu=new KT_Menu(KT_I18N::translate($name), get_query_url(array('lang'=>$code), '&amp;'), 'menu-language-'.$code);
				if (KT_LOCALE == $code) {$submenu->addClass('','','lang-active');}
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

		$show_user_favs = KT_USER_ID && array_key_exists('widget_favorites', KT_Module::getActiveModules());
		$show_gedc_favs = array_key_exists('gedcom_favorites', KT_Module::getActiveModules());

		if ($show_user_favs && !$SEARCH_SPIDER) {
			if ($show_gedc_favs && !$SEARCH_SPIDER) {
				$favorites = array_merge(
					gedcom_favorites_KT_Module::getFavorites(KT_GED_ID),
					widget_favorites_KT_Module::getFavorites(KT_USER_ID)
				);
			} else {
				$favorites = widget_favorites_KT_Module::getFavorites(KT_USER_ID);
			}
		} else {
			if ($show_gedc_favs && !$SEARCH_SPIDER) {
				$favorites = gedcom_favorites_KT_Module::getFavorites(KT_GED_ID);
			} else {
				return null;
			}
		}
		// Sort $favorites alphabetically?

		$menu = new KT_Menu(KT_I18N::translate('Favorites'), '#', 'menu-favorites');

		foreach ($favorites as $favorite) {
			switch($favorite['type']) {
			case 'URL':
				$submenu = new KT_Menu($favorite['title'], $favorite['url']);
				$menu->addSubMenu($submenu);
				break;
			case 'INDI':
			case 'FAM':
			case 'SOUR':
			case 'OBJE':
			case 'NOTE':
				$obj = KT_GedcomRecord::getInstance($favorite['gid']);
				if ($obj && $obj->canDisplayName()) {
					$submenu = new KT_Menu($obj->getFullName(), $obj->getHtmlUrl());
					$menu->addSubMenu($submenu);
				}
				break;
			}
		}

		if ($show_user_favs) {
			if (isset($controller->record) && $controller->record instanceof KT_GedcomRecord) {
				$submenu = new KT_Menu(KT_I18N::translate('Add to favorites'), '#');
				$submenu->addOnclick("jQuery.post('module.php?mod=widget_favorites&amp;mod_action=menu-add-favorite',{xref:'".$controller->record->getXref()."'},function(){location.reload();})");
				$menu->addSubMenu($submenu);
			}
		}
		return $menu;
	}

	public static function getMainMenus() {
		$menus = array();
		foreach (KT_Module::getActiveMenus() as $module) {
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
		foreach (KT_Module::getActiveMenus() as $module) {
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
