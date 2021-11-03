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

// Modules can optionally implement the following interfaces.
interface KT_Module_Block {
	public function getBlock($block_id);
	public function loadAjax();
	public function isGedcomBlock();
	public function configureBlock($block_id);
}

interface KT_Module_Chart {
	public function getChartMenus();
}

interface KT_Module_Config {
	public function getConfigLink();
}

interface KT_Module_List {
	public function getListMenus();
}

interface KT_Module_Menu {
	public function defaultMenuOrder();
	public function defaultAccessLevel();
	public function MenuType();
}

interface KT_Module_Report {
	public function getReportMenus();
}

interface KT_Module_Sidebar {
	public function defaultSidebarOrder();
	public function defaultAccessLevel();
	public function getSidebarContent();
	public function getSidebarAjaxContent();
	public function hasSidebarContent();
}

interface KT_Module_Tab {
	public function defaultTabOrder();
	public function getTabContent();
	public function hasTabContent();
	public function canLoadAjax();
	public function getPreLoadContent();
	public function isGrayedOut();
	public function defaultAccessLevel();
}

interface KT_Module_Widget {
	public function getWidget($widget_id);
	public function defaultWidgetOrder();
	public function loadAjax();
	public function configureBlock($widget_id);
}

abstract class KT_Module {

	private $_title = null;

	public function __toString() {
		// We need to call getTitle() frequently (e.g. uasort callback function), but
		// this results in repeated calls to the same KT_I18N::Translate('...')
		// Caching the result gives a measurable performance boost.
		if (!isset($this->_title)) {
			$this->_title=$this->getTitle();
		}
		return $this->_title;
	}

	// Each module must provide the following functions
	abstract public function getTitle();       // To label tabs, etc.
	abstract public function getDescription(); // A sentence describing what this module does

	// This is the default for the module and all its components.
	public function defaultAccessLevel() {
		// Returns one of: KT_PRIV_HIDE, KT_PRIV_PUBLIC, KT_PRIV_USER, KT_PRIV_ADMIN
		return KT_PRIV_HIDE;
	}

	// This is an internal name, used to generate identifiers
	public function getName() {
		return str_replace('_KT_Module', '', get_class($this));
	}

	// Run an action specified on the URL through module.php?mod=FOO&mod_action=BAR
	public function modAction($mod_action) {
	}

	static public function getActiveModules($sort = false) {
		// We call this function several times, so cache the results.
		// Sorting is slow, so only do it when requested.
		static $modules	= null;
		static $sorted	= false;

		if ($modules === null) {
			$module_names = KT_DB::prepare(
				"SELECT module_name FROM `##module` WHERE status='enabled'"
			)->fetchOneColumn();
			$modules=array();
			foreach ($module_names as $module_name) {
				if (file_exists(KT_ROOT . KT_MODULES_DIR . $module_name . '/module.php')) {
					require_once KT_ROOT . KT_MODULES_DIR . $module_name . '/module.php';
					$class = $module_name . '_KT_Module';
					$modules[$module_name] = new $class();
				} else {
					// Module has been deleted from disk?  Disable it.
					AddToLog("Module {$module_name} has been deleted from disk - disabling it", 'config');
					KT_DB::prepare(
						"UPDATE `##module` SET status = 'disabled' WHERE module_name = ?"
					)->execute(array($module_name));
				}
			}
		}
		if ($sort && !$sorted) {
			uasort($modules, function ($x, $y) {
				return KT_I18N::strcasecmp((string)$x, (string)$y);
			});
			$sorted = true;
		}
		return $modules;
	}

	static private function getActiveModulesByComponent($component, $ged_id, $access_level) {
		$module_names = KT_DB::prepare(
			"SELECT module_name".
			" FROM `##module`".
			" JOIN `##module_privacy` USING (module_name)".
			" WHERE gedcom_id=? AND component=? AND status='enabled' AND access_level>=?".
			" ORDER BY CASE component WHEN 'menu' THEN menu_order WHEN 'sidebar' THEN sidebar_order WHEN 'tab' THEN tab_order WHEN 'widget' THEN widget_order ELSE 0 END, module_name"
		)->execute(array($ged_id, $component, $access_level))->fetchOneColumn();
		$array = array();
		foreach ($module_names as $module_name) {
			if (file_exists(KT_ROOT . KT_MODULES_DIR . $module_name . '/module.php')) {
				require_once KT_ROOT . KT_MODULES_DIR . $module_name . '/module.php';
				$class = $module_name . '_KT_Module';
				$array[$module_name] = new $class();
			} else {
				// Module has been deleted from disk? Disable it.
				AddToLog("Module {$module_name} has been deleted from disk - disabling it", 'config');
				KT_DB::prepare(
					"UPDATE `##module` SET status='disabled' WHERE module_name=?"
				)->execute(array($module_name));
			}
		}

		if ($component != 'menu' && $component != 'sidebar' && $component != 'tab' && $component != 'widget') {
			uasort($array, function ($x, $y) {
				return KT_I18N::strcasecmp((string)$x, (string)$y);
			});
		}
		return $array;
	}

	// LIST ACTIVE MODULES
	// Get a list of all the active, authorised blocks
	static public function getActiveBlocks($ged_id = KT_GED_ID, $access_level = KT_USER_ACCESS_LEVEL) {
		static $blocks = null;
		if ($blocks === null) {
			$blocks = self::getActiveModulesByComponent('block', $ged_id, $access_level);
		}
		return $blocks;
	}

	// Get a list of all the active, authorised charts
	static public function getActiveCharts($ged_id = KT_GED_ID, $access_level = KT_USER_ACCESS_LEVEL) {
		static $charts = null;
		if ($charts === null) {
			$charts = self::getActiveModulesByComponent('chart', $ged_id, $access_level);
		}
		return $charts;
	}

	// Get a list of all the active, authorised lists
	static public function getActiveLists($ged_id = KT_GED_ID, $access_level = KT_USER_ACCESS_LEVEL) {
		static $lists = null;
		if ($lists === null) {
			$lists = self::getActiveModulesByComponent('list', $ged_id, $access_level);
		}
		return $lists;
	}

	// Get a list of all the active, authorised menus
	static public function getActiveMenus($ged_id = KT_GED_ID, $access_level = KT_USER_ACCESS_LEVEL) {
		static $menus = null;
		if ($menus === null) {
			$menus = self::getActiveModulesByComponent('menu', $ged_id, $access_level);
		}
		return $menus;
	}

	// Get a list of all the active, authorised reports
	static public function getActiveReports($ged_id = KT_GED_ID, $access_level = KT_USER_ACCESS_LEVEL) {
		static $reports = null;
		if ($reports === null) {
			$reports = self::getActiveModulesByComponent('report', $ged_id, $access_level);
		}
		return $reports;
	}

	// Get a list of all the active, authorised sidebars
	static public function getActiveSidebars($ged_id = KT_GED_ID, $access_level = KT_USER_ACCESS_LEVEL) {
		static $sidebars = null;
		if ($sidebars === null) {
			$sidebars = self::getActiveModulesByComponent('sidebar', $ged_id, $access_level);
		}
		return $sidebars;
	}

	// Get a list of all the active, authorised tabs
	static public function getActiveTabs($ged_id = KT_GED_ID, $access_level = KT_USER_ACCESS_LEVEL) {
		static $tabs = null;
		if ($tabs === null) {
			$tabs = self::getActiveModulesByComponent('tab', $ged_id, $access_level);
		}
		return $tabs;
	}

	// Get a list of all the active, authorised widgets
	static public function getActiveWidgets($ged_id = KT_GED_ID, $access_level = KT_USER_ACCESS_LEVEL) {
		static $widgets = null;
		if ($widgets === null) {
			$widgets = self::getActiveModulesByComponent('widget', $ged_id, $access_level);
		}
		return $widgets;
	}

	// CHECK MODULE ACCESS
	// Check module (a) provides a list and (b) we have permission to see.
	public static function isActiveList($ged_id = KT_GED_ID, $module, $access_level) {
		return array_key_exists($module, self::getActiveModulesByComponent('list', $ged_id, $access_level));
	}

	// Check module (a) provides a chart and (b) we have permission to see.
	public static function isActiveChart($ged_id = KT_GED_ID, $module, $access_level) {
		return array_key_exists($module, self::getActiveModulesByComponent('chart', $ged_id, $access_level));
	}

	// heck module (a) provides a sidebar and (b) we have permission to see.
	public static function isActiveSidebar($ged_id = KT_GED_ID, $module, $access_level) {
		return array_key_exists($module, self::getActiveModulesByComponent('sidebar', $ged_id, $access_level));
	}

	/**
	 * Find a specified module, if it is currently active.
	 *
	 * @param string $module_name
	 *
	 * @return Module|null
	 */
	public static function getModuleByName($module_name) {
		$modules = self::getActiveModules();
		if (array_key_exists($module_name, $modules)) {
			return $modules[$module_name];
		} else {
			return null;
		}
	}

	// Get a list of all installed modules.
	// During setup, new modules need status of 'enabled' (setup.php)
	// In admin->modules, new modules need status of 'disabled' (admin_modules.php)
	static public function getInstalledModules($status) {
		$modules	= array();
		$dir		= opendir(KT_ROOT . KT_MODULES_DIR);
		while (($file = readdir($dir)) !== false) {
			if (preg_match('/^[a-zA-Z0-9_]+$/', $file) && file_exists(KT_ROOT . KT_MODULES_DIR . $file . '/module.php')) {
				require_once KT_ROOT . KT_MODULES_DIR . $file . '/module.php';
				$class	= $file . '_KT_Module';
				$module	= new $class();
				$modules[$module->getName()] = $module;
				KT_DB::prepare("INSERT IGNORE INTO `##module` (module_name, status, menu_order, sidebar_order, tab_order, widget_order) VALUES (?, ?, ?, ?, ?, ?)")
					->execute(array(
						$module->getName(),
						$status,
						$module instanceof KT_Module_Menu    ? $module->defaultMenuOrder   () : null,
						$module instanceof KT_Module_Sidebar ? $module->defaultSidebarOrder() : null,
						$module instanceof KT_Module_Tab     ? $module->defaultTabOrder    () : null,
						$module instanceof KT_Module_Widget  ? $module->defaultWidgetOrder () : null
					));
				// Set the default privacy for this module.  Note that this also sets it for the
				// default family tree, with a gedcom_id of -1
				if ($module instanceof KT_Module_Block) {
					KT_DB::prepare(
						"INSERT IGNORE INTO `##module_privacy` (module_name, gedcom_id, component, access_level)".
						" SELECT ?, gedcom_id, 'block', ?".
						" FROM `##gedcom`"
					)->execute(array($module->getName(), $module->defaultAccessLevel()));
				}
				if ($module instanceof KT_Module_Chart) {
					KT_DB::prepare(
						"INSERT IGNORE INTO `##module_privacy` (module_name, gedcom_id, component, access_level)".
						" SELECT ?, gedcom_id, 'chart', ?".
						" FROM `##gedcom`"
					)->execute(array($module->getName(), $module->defaultAccessLevel()));
				}
				if ($module instanceof KT_Module_List) {
					KT_DB::prepare(
						"INSERT IGNORE INTO `##module_privacy` (module_name, gedcom_id, component, access_level)".
						" SELECT ?, gedcom_id, 'list', ?".
						" FROM `##gedcom`"
					)->execute(array($module->getName(), $module->defaultAccessLevel()));
				}
				if ($module instanceof KT_Module_Menu) {
					KT_DB::prepare(
						"INSERT IGNORE INTO `##module_privacy` (module_name, gedcom_id, component, access_level)".
						" SELECT ?, gedcom_id, 'menu', ?".
						" FROM `##gedcom`"
					)->execute(array($module->getName(), $module->defaultAccessLevel()));
				}
				if ($module instanceof KT_Module_Report) {
					KT_DB::prepare(
						"INSERT IGNORE INTO `##module_privacy` (module_name, gedcom_id, component, access_level)".
						" SELECT ?, gedcom_id, 'report', ?".
						" FROM `##gedcom`"
					)->execute(array($module->getName(), $module->defaultAccessLevel()));
				}
				if ($module instanceof KT_Module_Sidebar) {
					KT_DB::prepare(
						"INSERT IGNORE INTO `##module_privacy` (module_name, gedcom_id, component, access_level)".
						" SELECT ?, gedcom_id, 'sidebar', ?".
						" FROM `##gedcom`"
					)->execute(array($module->getName(), $module->defaultAccessLevel()));
				}
				if ($module instanceof KT_Module_Tab) {
					KT_DB::prepare(
						"INSERT IGNORE INTO `##module_privacy` (module_name, gedcom_id, component, access_level)".
						" SELECT ?, gedcom_id, 'tab', ?".
						" FROM `##gedcom`"
					)->execute(array($module->getName(), $module->defaultAccessLevel()));
				}
				if ($module instanceof KT_Module_Widget) {
					KT_DB::prepare(
						"INSERT IGNORE INTO `##module_privacy` (module_name, gedcom_id, component, access_level)".
						" SELECT ?, gedcom_id, 'widget', ?".
						" FROM `##gedcom`"
					)->execute(array($module->getName(), $module->defaultAccessLevel()));
				}
			}
		}
		uasort($modules, function ($x, $y) {
			return KT_I18N::strcasecmp((string)$x, (string)$y);
		});

		return $modules;
	}

	// We have a new family tree - assign default access rights to it.
	static public function setDefaultAccess($ged_id) {
		foreach (self::getInstalledModules('disabled') as $module) {
			if ($module instanceof KT_Module_Block) {
				KT_DB::prepare(
					"INSERT IGNORE `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, 'block', ?)"
				)->execute(array($module->getName(), $ged_id, $module->defaultAccessLevel()));
			}
			if ($module instanceof KT_Module_Chart) {
				KT_DB::prepare(
					"INSERT IGNORE `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, 'chart', ?)"
				)->execute(array($module->getName(), $ged_id, $module->defaultAccessLevel()));
			}
			if ($module instanceof KT_Module_List) {
				KT_DB::prepare(
					"INSERT IGNORE `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, 'list', ?)"
				)->execute(array($module->getName(), $ged_id, $module->defaultAccessLevel()));
			}
			if ($module instanceof KT_Module_Menu) {
				KT_DB::prepare(
					"INSERT IGNORE `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, 'menu', ?)"
				)->execute(array($module->getName(), $ged_id, $module->defaultAccessLevel()));
			}
			if ($module instanceof KT_Module_Report) {
				KT_DB::prepare(
					"INSERT IGNORE `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, 'report', ?)"
				)->execute(array($module->getName(), $ged_id, $module->defaultAccessLevel()));
			}
			if ($module instanceof KT_Module_Sidebar) {
				KT_DB::prepare(
					"INSERT IGNORE `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, 'sidebar', ?)"
				)->execute(array($module->getName(), $ged_id, $module->defaultAccessLevel()));
			}
			if ($module instanceof KT_Module_Tab) {
				KT_DB::prepare(
					"INSERT IGNORE `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, 'tab', ?)"
				)->execute(array($module->getName(), $ged_id, $module->defaultAccessLevel()));
			}
			if ($module instanceof KT_Module_Widget) {
				KT_DB::prepare(
					"INSERT IGNORE `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, 'widget', ?)"
				)->execute(array($module->getName(), $ged_id, $module->defaultAccessLevel()));
			}
		}
	}

	// Create the default module settings for new family trees
	static public function setDefaultModules() {

		/**
		 *  An array listing modules to be enabled during setup processing
		 * @param module name
		 * @param status
		 * @param display order for tabs, menus, sidebar, widgets, resources
		 */
		$default_modules = array(
			// tabs
			'personal_facts'		=> array('enabled', 1, NULL, NULL, NULL),
			'relatives'				=> array('enabled', 2, NULL, NULL, NULL),
			'sources_tab'			=> array('enabled', 3, NULL, NULL, NULL),
			'notes'					=> array('enabled', 4, NULL, NULL, NULL),
			'tree'					=> array('enabled', 5, NULL, NULL, NULL),
			'album'					=> array('enabled', 6, NULL, NULL, NULL),
			// menus - main menu
			'menu_homepage'			=> array('enabled', NULL, 1, NULL, NULL),
			'page_menu'				=> array('enabled', NULL, 2, NULL, NULL),
			'menu_charts'			=> array('enabled', NULL, 3, NULL, NULL),
			'menu_lists'			=> array('enabled', NULL, 4, NULL, NULL),
			'menu_reports'			=> array('enabled', NULL, 5, NULL, NULL),
			'menu_search'			=> array('enabled', NULL, 6, NULL, NULL),
			// menus - extra menu
			'menu_login'			=> array('enabled', NULL, 13, NULL, NULL),
			'menu_favorites'		=> array('enabled', NULL, 14, NULL, NULL),
			'menu_languages'		=> array('enabled', NULL, 15, NULL, NULL),
			// sidebar
			'extra_info'			=> array('enabled', NULL, NULL, 1, NULL),
			'family_nav'			=> array('enabled', NULL, NULL, 2, NULL),
			'descendancy'			=> array('enabled', NULL, NULL, 3, NULL),
			'individuals'			=> array('enabled', NULL, NULL, 4, NULL),
			'families'				=> array('enabled', NULL, NULL, 5, NULL),
			// widgets
			'widget_quicklinks'		=> array('enabled', NULL, NULL, NULL, 10),
			'widget_todays_events'	=> array('enabled', NULL, NULL, NULL, 20),
			'widget_upcoming'		=> array('enabled', NULL, NULL, NULL, 30),
			'widget_recent_changes'	=> array('enabled', NULL, NULL, NULL, 40),
			// not ordered
			//  charts (sorted alphabetically)
			'chart_ancestry'		=> array('enabled', NULL, NULL, NULL, NULL),
			'chart_compact'			=> array('enabled', NULL, NULL, NULL, NULL),
			'chart_descendancy'		=> array('enabled', NULL, NULL, NULL, NULL),
			'chart_familybook'		=> array('enabled', NULL, NULL, NULL, NULL),
			'chart_fanchart'		=> array('enabled', NULL, NULL, NULL, NULL),
			'chart_hourglass'		=> array('enabled', NULL, NULL, NULL, NULL),
			'chart_lifespan'		=> array('enabled', NULL, NULL, NULL, NULL),
			'chart_pedigree'		=> array('enabled', NULL, NULL, NULL, NULL),
			'chart_relationship'	=> array('enabled', NULL, NULL, NULL, NULL),
			'chart_statistics'		=> array('enabled', NULL, NULL, NULL, NULL),
			'chart_timeline'		=> array('enabled', NULL, NULL, NULL, NULL),
			// lists (sorted alphabetically)
			'list_branches'			=> array('enabled', NULL, NULL, NULL, NULL),
			'list_calendar'			=> array('enabled', NULL, NULL, NULL, NULL),
			'calendar_utilities'	=> array('enabled', NULL, NULL, NULL, NULL),
			'list_families'			=> array('enabled', NULL, NULL, NULL, NULL),
			'list_individuals'		=> array('enabled', NULL, NULL, NULL, NULL),
			'list_media'			=> array('enabled', NULL, NULL, NULL, NULL),
			'list_places'			=> array('enabled', NULL, NULL, NULL, NULL),
			'list_repositories'		=> array('enabled', NULL, NULL, NULL, NULL),
			'list_shared_notes'		=> array('enabled', NULL, NULL, NULL, NULL),
			'list_sources'			=> array('enabled', NULL, NULL, NULL, NULL),
			// reports (sorted alphabetically)
			'report_changes'		=> array('enabled', NULL, NULL, NULL, NULL),
			'report_fact'			=> array('enabled', NULL, NULL, NULL, NULL),
			'report_family'			=> array('enabled', NULL, NULL, NULL, NULL),
			'report_individual'		=> array('enabled', NULL, NULL, NULL, NULL),
			'report_marriages'		=> array('enabled', NULL, NULL, NULL, NULL),
			'report_related_fam'	=> array('enabled', NULL, NULL, NULL, NULL),
			'report_related_indi'	=> array('enabled', NULL, NULL, NULL, NULL),
			'report_todo'			=> array('enabled', NULL, NULL, NULL, NULL),
			'report_vital_records'	=> array('enabled', NULL, NULL, NULL, NULL),
			// blocks (manually positioned and sorted)
			'charts'				=> array('enabled', NULL, NULL, NULL, NULL),
			'gedcom_block'			=> array('enabled', NULL, NULL, NULL, NULL),
			'gedcom_favorites'		=> array('enabled', NULL, NULL, NULL, NULL),
			'gedcom_news'			=> array('enabled', NULL, NULL, NULL, NULL),
			'gedcom_stats'			=> array('enabled', NULL, NULL, NULL, NULL),
			'html'					=> array('enabled', NULL, NULL, NULL, NULL),
			'login_block'			=> array('enabled', NULL, NULL, NULL, NULL),
			'logged_in'				=> array('enabled', NULL, NULL, NULL, NULL),
			'random_media'			=> array('enabled', NULL, NULL, NULL, NULL),
			'recent_changes'		=> array('enabled', NULL, NULL, NULL, NULL),
			'review_changes'		=> array('enabled', NULL, NULL, NULL, NULL),
			'todays_events'			=> array('enabled', NULL, NULL, NULL, NULL),
			'todo'					=> array('enabled', NULL, NULL, NULL, NULL),
			'top10_givnnames'		=> array('enabled', NULL, NULL, NULL, NULL),
			'top10_pageviews'		=> array('enabled', NULL, NULL, NULL, NULL),
			'top10_surnames'		=> array('enabled', NULL, NULL, NULL, NULL),
			'upcoming_events'		=> array('enabled', NULL, NULL, NULL, NULL),
			// other
			'batch_update'			=> array('enabled', NULL, NULL, NULL, NULL),
			'ckeditor'				=> array('enabled', NULL, NULL, NULL, NULL),
			'sitemap'				=> array('enabled', NULL, NULL, NULL, NULL),
		);

		foreach($default_modules as $module => $order) {
			KT_DB::prepare("
				INSERT IGNORE INTO `##module` (module_name, status, tab_order, menu_order, sidebar_order, widget_order) VALUES (?, ?, ?, ?, ?, ?)
			")->execute(array($module, $order[0], $order[1], $order[2], $order[3], $order[4]));
		}
	}

}
