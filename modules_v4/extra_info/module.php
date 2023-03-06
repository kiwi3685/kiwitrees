<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net
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

class extra_info_KT_Module extends KT_Module implements KT_Module_Sidebar {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module/sidebar */ KT_I18N::translate('Extra information');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Extra information” module */ KT_I18N::translate('A sidebar showing non-genealogical information about an individual.');
	}

	// Implement KT_Module_Sidebar
	public function defaultSidebarOrder() {
		return 10;
	}

	// Implement KT_Module_Sidebar
	public function hasSidebarContent() {
		return true;
	}

	// Implement KT_Module_Sidebar
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement KT_Module_Sidebar
	public function getSidebarContent() {
		global $SHOW_COUNTER, $controller;

		$indifacts = array();
		// The individual’s own facts
		foreach ($controller->record->getIndiFacts() as $fact) {
			if (self::showFact($fact)) {
				$indifacts[] = $fact;
			}
		}

		ob_start();
		echo '
			<div>' .
				KT_I18N::translate('Internal reference ') . '
				<span>' . $controller->record->getXref() . '</span>
				<hr>
			</div>
		';
		if (!$indifacts) {
			echo KT_I18N::translate('There are no facts for this individual.');
		} else {
			foreach ($indifacts as $fact) {
				echo '<hr>' . print_fact($fact, $controller->record);
			}
		}
		if ($SHOW_COUNTER && (empty($SEARCH_SPIDER))) {
			require KT_ROOT . 'includes/hitcount.php';
			echo '<div id="hitcounter">';
				echo KT_I18N::translate('Hit Count:') .  ' ' . $hitCount;
			echo '</div>';// close #hitcounter
		}
		return strip_tags(ob_get_clean(), '<a><div><span><hr>');
	}

	// Implement KT_Module_Sidebar
	public function getSidebarAjaxContent() {
		return '';
	}

	// Does this module display a particular fact
	public static function showFact(KT_EVENT $fact) {
		switch ($fact->getTag()) {
		case 'AFN':
		case 'IDNO':
		case 'CHAN':
		case 'FACT':
		case 'REFN':
		case 'RFN':
		case 'RIN':
		case 'SSN':
		case '_UID':
			return true;
		default:
			return false;
		}
	}

}
