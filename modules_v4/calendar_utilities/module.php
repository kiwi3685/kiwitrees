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

class calendar_utilities_KT_Module extends KT_Module implements KT_Module_Config, KT_Module_List {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Calendar utilities');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the calendar utilities module */ KT_I18N::translate('A selection of calendar utility tools');
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return KT_PRIV_USER;
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'show':
			$this->show();
			break;
		case 'admin_config':
			$this->config();
			break;
		}
	}

	// Implement KT_Module_List
	public function getListMenus() {
		global $controller;
		$menus = array();
		$i = 0;
		foreach ($this->list_plugins() as $plugin_file) {
			if (get_module_setting($this->getName(), $plugin_file) == '1') {
				$i++;
			}
		}
		if ($i > 0) {
			$menu  = new KT_Menu(
				$this->getTitle(),
				'module.php?mod=' . $this->getName() . '&amp;mod_action=show',
				'menu-calendar_utilities'
			);
			$menus[] = $menu;
		}
		return $menus;
	}

	// Implement KT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod='.$this->getName().'&amp;mod_action=admin_config';
	}

	private function show() {
		global $controller;
		$controller = new KT_Controller_Page();
		$controller
			->restrictAccess(KT_Module::isActiveList(KT_GED_ID, $this->getName(), KT_USER_ACCESS_LEVEL))
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addInlineJavascript('jQuery("#calendar_tabs").tabs();');

		$html = '
			<div id="utilities-container">
				<h2>' . $controller->getPageTitle() . '</h2>
				<div id="calendar_tabs">
					<ul>';
						foreach ($this->list_plugins() as $plugin_file) {
							if ( get_module_setting($this->getName(), $plugin_file) == '1'){
								$pluginfile = implode('', file(KT_MODULES_DIR.$this->getName() . '/plugins/' . $plugin_file . '.php'));
								if (preg_match('/plugin_name\s*=\s*"(.*)";/', $pluginfile, $match)) {
									$plugin_title = KT_I18N::translate($match[1]);
								}
								$html .=
									'<li>'.
										'<a href="#' . $plugin_file . '">'.
											'<span title="' . $plugin_title . '">' . $plugin_title.'</span>
										</a>
									</li>';
							}
						}
				$html .= '
					</ul>
					<div id="plugin_container">';
						foreach ($this->list_plugins() as $plugin_file) {
							if (get_module_setting($this->getName(), $plugin_file) == '1') {
								$html .= '<div id="' . $plugin_file . '">';
									include_once KT_MODULES_DIR . $this->getName() . '/plugins/' . $plugin_file . '.php';
								$html .= '</div>';
							}
						}
				$html .= '
					</div>
				</div>
			</div>';

		echo $html;
	}

	private function config() {
		require_once KT_ROOT.'includes/functions/functions_edit.php';
		$controller = new KT_Controller_Page();
		$controller
			->restrictAccess(KT_USER_IS_ADMIN)
			->setPageTitle($this->getTitle())
			->pageHeader();

		$action = KT_Filter::post('action');

		if ($action == 'update') {
			foreach ($this->list_plugins() as $plugin_file) {
				set_module_setting($this->getName(), $plugin_file, KT_Filter::post('NEW_'.$plugin_file));
			}
			AddToLog('calendar_utilities config updated', 'config');
		}

		echo '
			<div id="calendar_utilities">
				<h2>', $controller->getPageTitle(), '</h2>
				<h3>', KT_I18N::translate('Select the utilities you want to display'), '</h3>
				<form method="post" name="utilities" action="module.php?mod=' . $this->getName() . '&mod_action=admin_config">
					<input type="hidden" name="action" value="update">';
					foreach ($this->list_plugins() as $plugin_file) {
						$pluginfile = implode('', file(KT_MODULES_DIR . $this->getName() . '/plugins/' . $plugin_file.'.php'));
						if (preg_match('/plugin_name\s*=\s*"(.*)";/', $pluginfile, $match)) {
							$plugin_title = KT_I18N::translate($match[1]);
						}
						echo '
						<div class="container">
							<div class="label">', $plugin_title, '</div>
							<div>', edit_field_yes_no('NEW_' .$plugin_file. '"', get_module_setting($this->getName(), $plugin_file, '1')), '</div>
						</div>';
					}
					echo '
						<button class="btn btn-primary save" type="submit">
							<i class="fa fa-floppy-o"></i>'.
							KT_I18N::translate('Save').'
						</button>
				</form>
			</div >';
	}

	// Scan the plugin folder for a list of plugins
	static function list_plugins() {
		$results	= array();
		$dir		= dirname(__FILE__).'/plugins/';
		$dir_handle	= opendir($dir);
		while ($file = readdir($dir_handle)) {
			if (substr($file, -4) == '.php') {
				$file		= basename($file, '.php');
				$results[]	= $file;
			}
		}
		closedir($dir_handle);
		return $results;
	}
}
