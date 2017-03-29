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

class update_links_bu_plugin extends base_plugin {
	static function getName() {
		return WT_I18N::translate('Update missing links');
	}

	static function getDescription() {
		return WT_I18N::translate('Occasionally the table of links between needs to be synchronised with the GEDCOM data. This tools checks for missing links and inserts them into the table.');
	}

	// Default is to operate on INDI records
	function getRecordTypesToUpdate() {
		return array('INDI', 'FAM', 'SOUR', 'REPO', 'NOTE', 'OBJE');
	}

	static function doesRecordNeedUpdate($xref, $gedrec) {
		preg_match_all('/^\d+ ('.WT_REGEX_TAG.') @('.WT_REGEX_XREF.')@/m', $gedrec, $matches, PREG_SET_ORDER);
		// Try fast check first - no links in table at all
		$record = WT_DB::prepare("SELECT l_to FROM `##link` WHERE l_from = ? AND l_file = ?")->execute(array($xref, WT_GED_ID))->fetchAll();
		if ($matches && !$record) {
			return $matches;
		}
	}

	static function updateRecord($xref, $gedrec) {
		// extract all the links from the given record and insert them into the database
		// copy of function in functions_import
		static $sql_insert_link = null;
		if (!$sql_insert_link) {
			$sql_insert_link = WT_DB::prepare("INSERT IGNORE INTO `##link` (l_from,l_to,l_type,l_file) VALUES (?,?,?,?)");
		}

		if (preg_match_all('/^\d+ ('.WT_REGEX_TAG.') @('.WT_REGEX_XREF.')@/m', $gedrec, $matches, PREG_SET_ORDER)) {
			$data = array();
			foreach ($matches as $match) {
				// Include each link once only.
				if (!in_array($match[1].$match[2], $data)) {
					$data[] = $match[1].$match[2];
					// Ignore any errors, which may be caused by "duplicates" that differ on case/collation, e.g. "S1" and "s1"
					try {
						$sql_insert_link->execute(array($xref, $match[2], $match[1], WT_GED_ID));
					} catch (PDOException $e) {
						// We could display a warning here....
					}
				}
			}
		}
		return $gedrec;
	}

}
