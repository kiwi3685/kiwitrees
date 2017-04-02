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

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class widget_logged_in_WT_Module extends WT_Module implements WT_Module_Widget {
	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module. (A list of users who are online now) */ WT_I18N::translate('Who is online');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of the “Who is online” module */ WT_I18N::translate('A list of users and visitors who are currently online.');
	}

	// Implement class WT_Module_Block
	public function getWidget($widget_id, $template=true, $cfg=null) {
		$id=$this->getName();
		$class=$this->getName();
		$title=$this->getTitle();
		$content = '<div>' . whoisonline() . '</div>';

		if ($template) {
			require WT_THEME_DIR.'templates/widget_template.php';
		} else {
			return $content;
		}
	}

	// Implement class WT_Module_Block
	public function loadAjax() {
		return false;
	}

	// Implement WT_Module_Widget
	public function defaultWidgetOrder() {
		return 150;
	}

	// Implement WT_Module_Menu
	public function defaultAccessLevel() {
		return WT_PRIV_USER;
	}

	// Implement class WT_Module_Block
	public function configureBlock($widget_id) {
	}
}
