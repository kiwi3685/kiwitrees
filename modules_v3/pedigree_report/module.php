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

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class pedigree_report_WT_Module extends WT_Module implements WT_Module_Report {
	// Extend class WT_Module
	public function getTitle() {
		// This text also appears in the .XML file - update both together
		return /* I18N: Name of a report */ WT_I18N::translate('Pedigree');
	}

	// Extend class WT_Module
	public function getDescription() {
		// This text also appears in the .XML file - update both together
		return /* I18N: Description of the “Pedigree” module */ WT_I18N::translate('A report of an individual’s ancestors, formatted as a tree.');
	}

	// Extend class WT_Module
	public function defaultAccessLevel() {
		return WT_PRIV_PUBLIC;
	}

	// Implement WT_Module_Report - a module can provide many reports
	public function getReportMenus() {
		global $controller;

		$menus=array();
		$menu=new WT_Menu(
			$this->getTitle(),
			'reportengine.php?ged='.WT_GEDURL.'&amp;action=setup&amp;report='.WT_MODULES_DIR.$this->getName().'/report.xml&amp;pid='.$controller->getSignificantIndividual()->getXref(),
			'menu-report-'.$this->getName()
		);
		$menus[]=$menu;

		return $menus;
		
	}
}
