<?php
// View for the pedigree tree.
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2010  PGV Development Team
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

class chart_compact_WT_Module extends WT_Module implements WT_Module_Chart {

	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Compact');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of “Compact chart” module */ WT_I18N::translate('An individual\'s compact chart');
	}

	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'show':
			$this->show();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Extend class WT_Module
	public function defaultAccessLevel() {
		return WT_PRIV_PUBLIC;
	}

	// Implement WT_Module_Chart
	public function getChartMenus() {
		global $controller;
		$indi_xref	= $controller->getSignificantIndividual()->getXref();
		$menus		= array();
		$menu		= new WT_Menu(
			$this->getTitle(),
			'compact.php?rootid=' . $indi_xref . '&amp;ged=' . WT_GEDURL,
			'menu-chart-compact'
		);
		$menus[] = $menu;

		return $menus;
	}

}
