<?php
// Classes and libraries for module system
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

// Modules can optionally implement the following interfaces.
interface WT_Module_Block {
	public function getBlock($block_id);
	public function loadAjax();
	public function isGedcomBlock();
	public function configureBlock($block_id);
}

interface WT_Module_Chart {
	public function getChartMenus();
}

interface WT_Module_Config {
	public function getConfigLink();
}

interface WT_Module_List {
	public function getListMenus();
}

interface WT_Module_Menu {
	public function defaultMenuOrder();
	public function defaultAccessLevel();
	public function MenuType();
}

interface WT_Module_Report {
	public function getReportMenus();
}

interface WT_Module_Resources {
	public function getResourceMenus();
}

interface WT_Module_Sidebar {
	public function defaultSidebarOrder();
	public function getSidebarContent();
	public function getSidebarAjaxContent();
	public function hasSidebarContent();
}

interface WT_Module_Tab {
	public function defaultTabOrder();
	public function getTabContent();
	public function hasTabContent();
	public function canLoadAjax();
	public function getPreLoadContent();
	public function isGrayedOut();
	public function defaultAccessLevel();
}

interface WT_Module_Widget {
	public function getWidget($widget_id);
	public function defaultWidgetOrder();
	public function loadAjax();
	public function configureBlock($widget_id);
}

abstract class WT_Module {

	private $_title = null;

	public function __toString() {
		// We need to call getTitle() frequently (e.g. uasort callback function), but
		// this results in repeated calls to the same WT_I18N::Translate('...')
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
		// Returns one of: WT_PRIV_HIDE, WT_PRIV_PUBLIC, WT_PRIV_USER, WT_PRIV_ADMIN
		return WT_PRIV_HIDE;
	}

	// This is an internal name, used to generate identifiers
	public function getName() {
		return str_replace('_WT_Module', '', get_class($this));
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
			$module_names = WT_DB::prepare(
				"SELECT SQL_CACHE module_name FROM `##module` WHERE status='enabled'"
			)->fetchOneColumn();
			$modules=array();
			foreach ($module_names as $module_name) {
				if (file_exists(WT_ROOT . WT_MODULES_DIR . $module_name . '/module.php')) {
					require_once WT_ROOT . WT_MODULES_DIR . $module_name . '/module.php';
					$class = $module_name . '_WT_Module';
					$modules[$module_name] = new $class();
				} else {
					// Module has been deleted from disk?  Disable it.
					AddToLog("Module {$module_name} has been deleted from disk - disabling it", 'config');
					WT_DB::prepare(
						"UPDATE `##module` SET status = 'disabled' WHERE module_name = ?"
					)->execute(array($module_name));
				}
			}
		}
		if ($sort && !$sorted) {
			uasort($modules, create_function('$x,$y', 'return utf8_strcasecmp((string)$x, (string)$y);'));
			$sorted = true;
		}
		return $modules;
	}

	static private function getActiveModulesByComponent($component, $ged_id, $access_level) {
		$module_names = WT_DB::prepare(
			"SELECT SQL_CACHE module_name".
			" FROM `##module`".
			" JOIN `##module_privacy` USING (module_name)".
			" WHERE gedcom_id=? AND component=? AND status='enabled' AND access_level>=?".
			" ORDER BY CASE component WHEN 'menu' THEN menu_order WHEN 'sidebar' THEN sidebar_order WHEN 'tab' THEN tab_order WHEN 'widget' THEN widget_order ELSE 0 END, module_name"
		)->execute(array($ged_id, $component, $access_level))->fetchOneColumn();
		$array = array();
		foreach ($module_names as $module_name) {
			if (file_exists(WT_ROOT . WT_MODULES_DIR . $module_name . '/module.php')) {
				require_once WT_ROOT . WT_MODULES_DIR . $module_name . '/module.php';
				$class = $module_name . '_WT_Module';
				$array[$module_name] = new $class();
			} else {
				// Module has been deleted from disk? Disable it.
				AddToLog("Module {$module_name} has been deleted from disk - disabling it", 'config');
				WT_DB::prepare(
					"UPDATE `##module` SET status='disabled' WHERE module_name=?"
				)->execute(array($module_name));
			}
		}
		if ($component != 'menu' && $component != 'sidebar' && $component != 'tab' && $component != 'widget') {
			uasort($array, create_function('$x,$y', 'return utf8_strcasecmp((string)$x, (string)$y);'));
		}
		return $array;
	}

	// Get a list of all the active, authorised blocks
	static public function getActiveBlocks($ged_id = WT_GED_ID, $access_level = WT_USER_ACCESS_LEVEL) {
		static $blocks = null;
		if ($blocks === null) {
			$blocks = self::getActiveModulesByComponent('block', $ged_id, $access_level);
		}
		return $blocks;
	}

	// Get a list of all the active, authorised charts
	static public function getActiveCharts($ged_id = WT_GED_ID, $access_level = WT_USER_ACCESS_LEVEL) {
		static $charts = null;
		if ($charts === null) {
			$charts = self::getActiveModulesByComponent('chart', $ged_id, $access_level);
		}
		return $charts;
	}

	// Get a list of all the active, authorised lists
	static public function getActiveLists($ged_id = WT_GED_ID, $access_level = WT_USER_ACCESS_LEVEL) {
		static $lists = null;
		if ($lists === null) {
			$lists = self::getActiveModulesByComponent('list', $ged_id, $access_level);
		}
		return $lists;
	}
	// Get a list of modules which (a) provide a list and (b) we have permission to see.
	public static function isActiveList($ged_id = WT_GED_ID, $module, $access_level) {
		return array_key_exists($module, self::getActiveModulesByComponent('list', $ged_id, $access_level));
	}

	// Get a list of modules which (a) provide a chart and (b) we have permission to see.
	public static function isActiveChart($ged_id = WT_GED_ID, $module, $access_level) {
		return array_key_exists($module, self::getActiveModulesByComponent('chart', $ged_id, $access_level));
	}

	// Get a list of all the active, authorised menus
	static public function getActiveMenus($ged_id = WT_GED_ID, $access_level = WT_USER_ACCESS_LEVEL) {
		static $menus = null;
		if ($menus === null) {
			$menus = self::getActiveModulesByComponent('menu', $ged_id, $access_level);
		}
		return $menus;
	}

	// Get a list of all the active, authorised reports
	static public function getActiveReports($ged_id = WT_GED_ID, $access_level = WT_USER_ACCESS_LEVEL) {
		static $reports = null;
		if ($reports === null) {
			$reports = self::getActiveModulesByComponent('report', $ged_id, $access_level);
		}
		return $reports;
	}

	// Get a list of all the active, authorised reports
	static public function getActiveResources($ged_id = WT_GED_ID, $access_level = WT_USER_ACCESS_LEVEL) {
		static $resources = null;
		if ($resources === null) {
			$resources = self::getActiveModulesByComponent('resource', $ged_id, $access_level);
		}
		return $resources;
	}

	// Get a list of all the active, authorised sidebars
	static public function getActiveSidebars($ged_id = WT_GED_ID, $access_level = WT_USER_ACCESS_LEVEL) {
		static $sidebars = null;
		if ($sidebars === null) {
			$sidebars = self::getActiveModulesByComponent('sidebar', $ged_id, $access_level);
		}
		return $sidebars;
	}

	// Get a list of all the active, authorised tabs
	static public function getActiveTabs($ged_id = WT_GED_ID, $access_level = WT_USER_ACCESS_LEVEL) {
		static $tabs = null;
		if ($tabs === null) {
			$tabs = self::getActiveModulesByComponent('tab', $ged_id, $access_level);
		}
		return $tabs;
	}

	// Get a list of all the active, authorised widgets
	static public function getActiveWidgets($ged_id = WT_GED_ID, $access_level = WT_USER_ACCESS_LEVEL) {
		static $widgets = null;
		if ($widgets === null) {
			$widgets = self::getActiveModulesByComponent('widget', $ged_id, $access_level);
		}
		return $widgets;
	}

	// Get a list of all installed modules.
	// During setup, new modules need status of 'enabled'
	// In admin->modules, new modules need status of 'disabled'
	static public function getInstalledModules($status) {
		$modules	= array();
		$dir		= opendir(WT_ROOT . WT_MODULES_DIR);
		while (($file = readdir($dir)) !== false) {
			if (preg_match('/^[a-zA-Z0-9_]+$/', $file) && file_exists(WT_ROOT.WT_MODULES_DIR . $file . '/module.php')) {
				require_once WT_ROOT . WT_MODULES_DIR . $file . '/module.php';
				$class	= $file . '_WT_Module';
				$module	= new $class();
				$modules[$module->getName()] = $module;
				WT_DB::prepare("INSERT IGNORE INTO `##module` (module_name, status, menu_order, sidebar_order, tab_order, widget_order) VALUES (?, ?, ?, ?, ?, ?)")
					->execute(array(
						$module->getName(),
						$status,
						$module instanceof WT_Module_Menu    ? $module->defaultMenuOrder   () : null,
						$module instanceof WT_Module_Sidebar ? $module->defaultSidebarOrder() : null,
						$module instanceof WT_Module_Tab     ? $module->defaultTabOrder    () : null,
						$module instanceof WT_Module_Widget  ? $module->defaultWidgetOrder () : null
					));
				// Set the default privacy for this module.  Note that this also sets it for the
				// default family tree, with a gedcom_id of -1
				if ($module instanceof WT_Module_Block) {
					WT_DB::prepare(
						"INSERT IGNORE INTO `##module_privacy` (module_name, gedcom_id, component, access_level)".
						" SELECT ?, gedcom_id, 'block', ?".
						" FROM `##gedcom`"
					)->execute(array($module->getName(), $module->defaultAccessLevel()));
				}
				if ($module instanceof WT_Module_Chart) {
					WT_DB::prepare(
						"INSERT IGNORE INTO `##module_privacy` (module_name, gedcom_id, component, access_level)".
						" SELECT ?, gedcom_id, 'chart', ?".
						" FROM `##gedcom`"
					)->execute(array($module->getName(), $module->defaultAccessLevel()));
				}
				if ($module instanceof WT_Module_List) {
					WT_DB::prepare(
						"INSERT IGNORE INTO `##module_privacy` (module_name, gedcom_id, component, access_level)".
						" SELECT ?, gedcom_id, 'list', ?".
						" FROM `##gedcom`"
					)->execute(array($module->getName(), $module->defaultAccessLevel()));
				}
				if ($module instanceof WT_Module_Menu) {
					WT_DB::prepare(
						"INSERT IGNORE INTO `##module_privacy` (module_name, gedcom_id, component, access_level)".
						" SELECT ?, gedcom_id, 'menu', ?".
						" FROM `##gedcom`"
					)->execute(array($module->getName(), $module->defaultAccessLevel()));
				}
				if ($module instanceof WT_Module_Report) {
					WT_DB::prepare(
						"INSERT IGNORE INTO `##module_privacy` (module_name, gedcom_id, component, access_level)".
						" SELECT ?, gedcom_id, 'report', ?".
						" FROM `##gedcom`"
					)->execute(array($module->getName(), $module->defaultAccessLevel()));
				}
				if ($module instanceof WT_Module_Resources) {
					WT_DB::prepare(
						"INSERT IGNORE INTO `##module_privacy` (module_name, gedcom_id, component, access_level)".
						" SELECT ?, gedcom_id, 'resource', ?".
						" FROM `##gedcom`"
					)->execute(array($module->getName(), $module->defaultAccessLevel()));
				}
				if ($module instanceof WT_Module_Sidebar) {
					WT_DB::prepare(
						"INSERT IGNORE INTO `##module_privacy` (module_name, gedcom_id, component, access_level)".
						" SELECT ?, gedcom_id, 'sidebar', ?".
						" FROM `##gedcom`"
					)->execute(array($module->getName(), $module->defaultAccessLevel()));
				}
				if ($module instanceof WT_Module_Tab) {
					WT_DB::prepare(
						"INSERT IGNORE INTO `##module_privacy` (module_name, gedcom_id, component, access_level)".
						" SELECT ?, gedcom_id, 'tab', ?".
						" FROM `##gedcom`"
					)->execute(array($module->getName(), $module->defaultAccessLevel()));
				}
				if ($module instanceof WT_Module_Widget) {
					WT_DB::prepare(
						"INSERT IGNORE INTO `##module_privacy` (module_name, gedcom_id, component, access_level)".
						" SELECT ?, gedcom_id, 'widget', ?".
						" FROM `##gedcom`"
					)->execute(array($module->getName(), $module->defaultAccessLevel()));
				}
			}
		}
		uasort($modules, create_function('$x,$y', 'return utf8_strcasecmp((string)$x, (string)$y);'));
		return $modules;
	}

	// We have a new family tree - assign default access rights to it.
	static public function setDefaultAccess($ged_id) {
		foreach (self::getInstalledModules('disabled') as $module) {
			if ($module instanceof WT_Module_Block) {
				WT_DB::prepare(
					"INSERT IGNORE `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, 'block', ?)"
				)->execute(array($module->getName(), $ged_id, $module->defaultAccessLevel()));
			}
			if ($module instanceof WT_Module_Chart) {
				WT_DB::prepare(
					"INSERT IGNORE `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, 'chart', ?)"
				)->execute(array($module->getName(), $ged_id, $module->defaultAccessLevel()));
			}
			if ($module instanceof WT_Module_List) {
				WT_DB::prepare(
					"INSERT IGNORE `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, 'list', ?)"
				)->execute(array($module->getName(), $ged_id, $module->defaultAccessLevel()));
			}
			if ($module instanceof WT_Module_Menu) {
				WT_DB::prepare(
					"INSERT IGNORE `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, 'menu', ?)"
				)->execute(array($module->getName(), $ged_id, $module->defaultAccessLevel()));
			}
			if ($module instanceof WT_Module_Report) {
				WT_DB::prepare(
					"INSERT IGNORE `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, 'report', ?)"
				)->execute(array($module->getName(), $ged_id, $module->defaultAccessLevel()));
			}
			if ($module instanceof WT_Module_Resources) {
				WT_DB::prepare(
					"INSERT IGNORE `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, 'resource', ?)"
				)->execute(array($module->getName(), $ged_id, $module->defaultAccessLevel()));
			}
			if ($module instanceof WT_Module_Sidebar) {
				WT_DB::prepare(
					"INSERT IGNORE `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, 'sidebar', ?)"
				)->execute(array($module->getName(), $ged_id, $module->defaultAccessLevel()));
			}
			if ($module instanceof WT_Module_Tab) {
				WT_DB::prepare(
					"INSERT IGNORE `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, 'tab', ?)"
				)->execute(array($module->getName(), $ged_id, $module->defaultAccessLevel()));
			}
			if ($module instanceof WT_Module_Widget) {
				WT_DB::prepare(
					"INSERT IGNORE `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, 'widget', ?)"
				)->execute(array($module->getName(), $ged_id, $module->defaultAccessLevel()));
			}
		}
	}
}
