<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net
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

class gedcom_block_KT_Module extends KT_Module implements KT_Module_Block {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Home');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Home” module */ KT_I18N::translate('A greeting message for site visitors.');
	}

	// Extend class KT_Module_Block
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement class KT_Module_Block
	public function getBlock($block_id, $template=true, $cfg=null) {
		global $controller;

		$indi_xref=$controller->getSignificantIndividual()->getXref();
		$id=$this->getName().$block_id;
		$class=$this->getName().'_block';
		$title='<span dir="auto">'.KT_TREE_TITLE.'</span>';
		$content = '<table><tr>';
		$content .= '<td><a href="pedigree.php?rootid='.$indi_xref.'&amp;ged='.KT_GEDURL.'"><i class="icon-pedigree"></i><br>'.KT_I18N::translate('Default chart').'</a></td>';
		$content .= '<td><a href="individual.php?pid='.$indi_xref.'&amp;ged='.KT_GEDURL.'"><i class="icon-indis"></i><br>'.KT_I18N::translate('Default individual').'</a></td>';
		if (KT_Site::preference('USE_REGISTRATION_MODULE') && KT_USER_ID==false) {
			$content .= '<td><a href="'.KT_LOGIN_URL.'?action=register"><i class="fa-user"></i><br>'.KT_I18N::translate('Request new user account').'</a></td>';
		}
		$content .= "</tr>";
		$content .= "</table>";

		if ($template) {
			require KT_THEME_DIR . 'templates/block_main_temp.php';
		} else {
			return $content;
		}
	}

	// Implement class KT_Module_Block
	public function loadAjax() {
		return false;
	}

	// Implement class KT_Module_Block
	public function isGedcomBlock() {
		return true;
	}

	// Implement class KT_Module_Block
	public function configureBlock($block_id) {
	}
}
