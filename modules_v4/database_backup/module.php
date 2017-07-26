<?php
/*
 * webtrees - simpl_menu module
 * Version 1.1
 * Copyright (C) 2010-2011 Nigel Osborne and kiwtrees.net. All rights reserved.
 *
 * webtrees: Web based Family History software
 * Copyright (C) 2011 webtrees development team.
 *
 * Derived from PhpGedView
 * Copyright (C) 2002 to 2010  PGV Development Team.  All rights reserved.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class database_backup_WT_Module extends WT_Module implements WT_Module_Config {
	// Extend class WT_Module
	public function getTitle() {
		return WT_I18N::translate('Database backup');
	}

	// Extend class WT_Module
	public function getDescription() {
		return WT_I18N::translate('Provides access to MyOOS [Dumper]. A database backup tool.');
	}

	// Implement WT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod='.$this->getName().'&amp;mod_action=admin_databasebackup';
	}

	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'admin_databasebackup':
			$this->config();
			break;
		}
	}

	private function config() {
		$action		= WT_Filter::post("action");
		$controller	= new WT_Controller_Page();
		$controller
			->requireAdminLogin()
			->setPageTitle(WT_I18N::translate('Database backup'))
			->pageHeader();
		echo '
			<div id="database_backup">
				<iframe src="' . WT_MODULES_DIR . $this->getName() . '/vendor/r23/msd" width="100%" height="700">
					<p>Sorry, your browser does not support iframes</p>
				</iframe>
			</div>
		';
	}

}
