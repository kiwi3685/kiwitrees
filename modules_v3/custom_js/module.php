<?php
// Classes and libraries for module system
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2015 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2010 John Finlay
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
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class custom_js_WT_Module extends WT_Module implements WT_Module_Config, WT_Module_Menu {
	// Extend WT_Module
	public function getTitle() {
		return WT_I18N::translate('Custom JavaScript');
	}

	// Extend WT_Module
	public function getDescription() {
		return WT_I18N::translate('Allows you to easily add Custom JavaScript to your webtrees site.');
	}

	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'admin_config':
			$controller=new WT_Controller_Page;
			$controller
				->requireAdminLogin()
				->setPageTitle($this->getTitle())
				->pageHeader()
				->addInlineJavascript('
				    function clearFields() {
					    document.getElementById("new_js").value=""
					}
				');

			$action = safe_POST("action");

			if ($action == 'update') {
				set_module_setting('custom_js', 'CJS_FOOTER',  $_POST['NEW_CJS_FOOTER']);
				AddToLog($this->getTitle().' config updated', 'config');
			}

			$CJS_FOOTER=get_module_setting('custom_js', 'CJS_FOOTER');
                echo '
					<div id="js_form" style="width:80%; min-width:600px;" >
						<h3>', WT_I18N::translate('Custom Javascript for Footer'), '</h3>
						<form style="width:98%;" method="post" name="configform" action="', $this->getConfigLink(), '">
							<input type="hidden" name="action" value="update">
							<textarea id="new_js" style="width:100%;" name="NEW_CJS_FOOTER">', $CJS_FOOTER, '</textarea>
							<button class="btn btn-primary save" type="submit">
							    <i class="fa fa-floppy-o"></i>'.
							    WT_I18N::translate('save').'
							</button>
							<button class="btn btn-primary reset" type="reset">
							    <i class="fa fa-refresh"></i>'.
							    WT_I18N::translate('reset').'
							</button>
							<button class="btn btn-primary clear" type="button" onclick="clearFields()">
							    <i class="fa fa-trash-o"></i>'.
							    WT_I18N::translate('clear').'
							</button>
						</form>
					</div>
				';
			break;
            default:
                header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement WT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod='.$this->getName().'&amp;mod_action=admin_config';
	}

	// Implement WT_Module_Menu
	public function defaultMenuOrder() {
		return 999;
	}

	// Implement WT_Module_Menu
	public function MenuType() {
		return 'other';
	}

	// Implement WT_Module_Menu
	public function getMenu() {
		// We don't actually have a menu - this is just a convenient "hook" to execute
		// code at the right time during page execution
		global $controller;

		$cjs_footer=get_module_setting('custom_js', 'CJS_FOOTER', '');
		if (strpos($cjs_footer, '#')!==false) {
			# parse for embedded keywords
			$stats = new WT_Stats(WT_GEDCOM);
			$cjs_footer = $stats->embedTags($cjs_footer);
		}
		$controller->addInlineJavaScript($cjs_footer, WT_Controller_Base::JS_PRIORITY_LOW);

		return null;
	}

}
