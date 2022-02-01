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

class KT_Note extends KT_GedcomRecord {

	/**
	 * Get the text contents of the note
	 *
	 * @return string|null
	 */
	public function getNote() {
		if (preg_match('/^0 @' . KT_REGEX_XREF . '@ NOTE ?(.*(?:\n1 CONT ?.*)*)/', $this->getGedcomRecord(), $match)) {
			return preg_replace("/\n1 CONT ?/", "\n", $match[1]);
		} else {
			return null;
		}
	}

	// Implement note-specific privacy logic
	protected function _canDisplayDetailsByType($access_level) {
		// Hide notes if they are attached to private records
		$linked_ids=KT_DB::prepare(
			"SELECT l_from FROM `##link` WHERE l_to=? AND l_file=?"
		)->execute(array($this->xref, $this->ged_id))->fetchOneColumn();
		foreach ($linked_ids as $linked_id) {
			$linked_record=KT_GedcomRecord::getInstance($linked_id);
			if ($linked_record && !$linked_record->canDisplayDetails($access_level)) {
				return false;
			}
		}

		// Apply default behaviour
		return parent::_canDisplayDetailsByType($access_level);
	}

	// Generate a private version of this record
	protected function createPrivateGedcomRecord($access_level) {
		return '0 @'.$this->xref.'@ NOTE '.KT_I18N::translate('Private');
	}

	// Fetch the record from the database
	protected static function fetchGedcomRecord($xref, $ged_id) {
		static $statement=null;

		if ($statement===null) {
			$statement=KT_DB::prepare(
				"SELECT o_type AS type, o_id AS xref, o_file AS ged_id, o_gedcom AS gedrec ".
				"FROM `##other` WHERE o_id=? AND o_file=? AND o_type='NOTE'"
			);
		}
		return $statement->execute(array($xref, $ged_id))->fetchOneRow(PDO::FETCH_ASSOC);
	}

	// Generate a URL to this record, suitable for use in HTML
	public function getHtmlUrl() {
		return parent::_getLinkUrl('note.php?nid=', '&amp;');
	}
	// Generate a URL to this record, suitable for use in javascript, HTTP headers, etc.
	public function getRawUrl() {
		return parent::_getLinkUrl('note.php?nid=', '&');
	}

	// The 'name' of a note record is the first line.  This can be
	// somewhat unwieldy if lots of CONC records are used.  Limit to 100 chars
	protected function _addName($type, $value, $gedrec) {
		if (utf8_strlen($value)<100) {
			parent::_addName($type, $value, $gedrec);
		} else {
			parent::_addName($type, utf8_substr($value, 0, 100).KT_I18N::translate('…'), $gedrec);
		}
	}

	// Get an array of structures containing all the names in the record
	public function getAllNames() {
		// Uniquely, the NOTE objects have data in their level 0 record.
		// Hence the REGEX passed in the second parameter
		return parent::_getAllNames('NOTE', '0 @'.KT_REGEX_XREF.'@');
	}
}
