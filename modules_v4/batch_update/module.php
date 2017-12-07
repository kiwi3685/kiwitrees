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
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class batch_update_KT_Module extends KT_Module implements KT_Module_Config{
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Batch update');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Batch update” module */ KT_I18N::translate('Apply automatic corrections to your genealogy data.');
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'admin_batch_update':
			$controller = new KT_Controller_Page();
			$controller
				->setPageTitle(KT_I18N::translate('Batch update'))
				->restrictAccess(KT_USER_IS_ADMIN)
				->pageHeader();

			// TODO: these files should be methods in this class
			require KT_ROOT . KT_MODULES_DIR . $this->getName() . '/' . $mod_action.'.php';
			$mod = new batch_update;
			echo $mod->main();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement KT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_batch_update';
	}
}
