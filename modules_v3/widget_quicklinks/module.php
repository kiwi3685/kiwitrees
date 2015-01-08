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

class widget_quicklinks_WT_Module extends WT_Module implements WT_Module_Widget {
	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Quick links');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of the “Quick links” module */ WT_I18N::translate('A selection of links for a user.');
	}

	// Implement WT_Module_Sidebar
	public function defaultWidgetOrder() {
		return 10;
	}

	// Implement class WT_Module_Widget
	public function getWidget($widget_id, $template=true, $cfg=null) {
		$id		= $this->getName();
		$class	= $this->getName();
		$title	= $this->getTitle();
		$content= '<table><tr>';
		if (get_user_setting(WT_USER_ID, 'editaccount')) {
			$content .= '<td><a href="edituser.php"><i class="icon-mypage"></i><br>'.WT_I18N::translate('My account').'</a></td>';
		}
		if (WT_USER_GEDCOM_ID) {
			$content .= '<td><a href="pedigree.php?rootid='.WT_USER_GEDCOM_ID.'&amp;ged='.WT_GEDURL.'"><i class="icon-pedigree"></i><br>'.WT_I18N::translate('My pedigree').'</a></td>';
			$content .= '<td><a href="individual.php?pid='.WT_USER_GEDCOM_ID.'&amp;ged='.WT_GEDURL.'"><i class="icon-indis"></i><br>'.WT_I18N::translate('My individual record').'</a></td>';
		}
		if (WT_USER_IS_ADMIN) {
			$content .= '<td><a href="admin.php"><i class="icon-admin"></i><br>'.WT_I18N::translate('Administration').'</a></td>';
		}
		$content .= '</tr>';
		$content .= '</table>';

		if ($template) {
			require WT_THEME_DIR.'templates/widget_template.php';
		} else {
			return $content;
		}
	}

	// Implement class WT_Module_Widget
	public function loadAjax() {
		return false;
	}

	// Implement class WT_Module_Widget
	public function configureBlock($widget_id) {
	}
}
