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

class chart_fanchart_WT_Module extends WT_Module implements WT_Module_Chart {

	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Fanchart');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of “Fanchart” module */ WT_I18N::translate('An individual\'s fanchart');
	}

	// Extend WT_Module
	public function modAction($mod_action) {
		switch ($mod_action) {
		case 'update':
			Zend_Session::writeClose();
			$rootId	= WT_Filter::get('rootid', WT_REGEX_XREF);
			$person	= WT_Person::getInstance($rootId, WT_GED_ID);
			$controller	= new WT_Controller_Fanchart();

			header('Content-Type: application/json;charset=UTF-8');

			echo json_encode($controller->buildJsonTree($person));
			break;
		case 'show':
			global $controller;
			$controller = new WT_Controller_Fanchart();
	        $controller
				->restrictAccess(WT_Module::isActiveChart(WT_GED_ID, $this->getName(), WT_USER_ACCESS_LEVEL))
	            ->pageHeader()
				->addExternalJavascript(WT_AUTOCOMPLETE_JS_URL)
				->addExternalJavascript(WT_D3_JS)
				->addExternalJavascript(WT_STATIC_URL . WT_MODULES_DIR . $this->getName() . '/js/ancestral-fan-chart.js');

	        echo $controller->render();
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
		$person	= $controller->getSignificantIndividual();
		$menus	= array();
		$menu	= new WT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;rootid=' . $person->getXref() . '&amp;ged=' . WT_GEDURL,
			'menu-chart-fanchart'
		);
		$menus[] = $menu;
		return $menus;
	}

}
