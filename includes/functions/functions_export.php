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

// Tidy up a gedcom record on export, for compatibility/portability
function reformat_record_export($rec) {
	global $WORD_WRAPPED_NOTES;

	$newrec='';
	foreach (preg_split('/[\r\n]+/', $rec, -1, PREG_SPLIT_NO_EMPTY) as $line) {
		// Escape @ characters
		// TODO:
		// Need to replace “@” with “@@”, unless it is either
		// a) an xref, such as @I123@
		// b) an escape, such as @#D FRENCH R@
		if (false) {
			$line=str_replace('@', '@@', $line);
		}
		// Split long lines
		// The total length of a GEDCOM line, including level number, cross-reference number,
		// tag, value, delimiters, and terminator, must not exceed 255 (wide) characters.
		// Use quick strlen() check before using slower utf8_strlen() check
		if (strlen($line)>KT_GEDCOM_LINE_LENGTH && utf8_strlen($line)>KT_GEDCOM_LINE_LENGTH) {
			[$level, $tag] = explode(' ', $line, 3);
			if ($tag!='CONT' && $tag!='CONC') {
				$level++;
			}
			do {
				// Split after $pos chars
				$pos=KT_GEDCOM_LINE_LENGTH;
				if ($WORD_WRAPPED_NOTES) {
					// Split on a space, and remove it (for compatibility with some desktop apps)
					while ($pos && utf8_substr($line, $pos-1, 1)!=' ') {
						--$pos;
					}
					if ($pos==strpos($line, ' ', 3)+1) {
						// No spaces in the data! Can’t split it :-(
						break;
					} else {
						$newrec.=utf8_substr($line, 0, $pos-1).KT_EOL;
						$line=$level.' CONC '.utf8_substr($line, $pos);
					}
				} else {
					// Split on a non-space (standard gedcom behaviour)
					while ($pos && utf8_substr($line, $pos-1, 1)==' ') {
						--$pos;
					}
					if ($pos==strpos($line, ' ', 3)) {
						// No non-spaces in the data! Can’t split it :-(
						break;
					}
					$newrec.=utf8_substr($line, 0, $pos).KT_EOL;
					$line=$level.' CONC '.utf8_substr($line, $pos);
				}
			} while (utf8_strlen($line)>KT_GEDCOM_LINE_LENGTH);
		}
		$newrec.=$line.KT_EOL;
	}
	return $newrec;
}

//Create a header for a (newly-created or already-imported) gedcom file.
function gedcom_header($gedfile) {
	$ged_id = get_id_from_gedcom($gedfile);

	// Default values for a new header
	$HEAD = "0 HEAD";
	$SOUR = "\n1 SOUR ".KT_KIWITREES."\n2 NAME ".KT_KIWITREES."\n2 VERS ".KT_VERSION_TEXT;
	$DEST = "\n1 DEST DISKETTE";
	$DATE = "\n1 DATE ".strtoupper(date("d M Y"))."\n2 TIME ".date("H:i:s");
	$GEDC = "\n1 GEDC\n2 VERS 5.5.1\n2 FORM Lineage-Linked";
	$CHAR = "\n1 CHAR UTF-8";
	$FILE = "\n1 FILE {$gedfile}";
	$LANG = "";
	$COPR = "";
	$SUBN = "";
	$SUBM = "\n1 SUBM @SUBM@\n0 @SUBM@ SUBM\n1 NAME ".KT_USER_NAME; // The SUBM record is mandatory

	// Preserve some values from the original header
	if (get_gedcom_setting($ged_id, 'imported')) {
		$head=find_gedcom_record("HEAD", $ged_id);
		if (preg_match("/\n1 LANG .+/", $head, $match)) {
			$LANG = $match[0];
		}
		if (preg_match("/\n1 SUBN .+/", $head, $match)) {
			$SUBN = $match[0];
		}
		if (preg_match("/\n1 COPR .+/", $head, $match)) {
			$COPR = $match[0];
		}
		// Link to SUBM/SUBN records, if they exist
		$subn=
			KT_DB::prepare("SELECT o_id FROM `##other` WHERE o_type=? AND o_file=?")
			->execute(array('SUBN', $ged_id))
			->fetchOne();
		if ($subn) {
			$SUBN = "\n1 SUBN @{$subn}@";
		}
		$subm=
			KT_DB::prepare("SELECT o_id FROM `##other` WHERE o_type=? AND o_file=?")
			->execute(array('SUBM', $ged_id))
			->fetchOne();
		if ($subm) {
			$SUBM = "\n1 SUBM @{$subm}@";
		}
	}

	return $HEAD . $SOUR . $DEST . $DATE . $GEDC . $CHAR . $FILE . $COPR . $LANG . $SUBN . $SUBM."\n";
}

// Prepend the GEDCOM_MEDIA_PATH to media filenames
function convert_media_path($rec, $path) {
	if ($path && preg_match('/\n1 FILE (.+)/', $rec, $match)) {
		$old_file_name = $match[1];
		if (!preg_match('~^(https?|ftp):~', $old_file_name)) { // Don’t modify external links
			// Adding a windows path?  Convert the slashes.
			if (strpos($path, '\\')!==false) {
				$new_file_name = preg_replace('~/+~', '\\', $old_file_name);
			} else {
				$new_file_name = $old_file_name;
			}
			// Path not present - add it.
			if (strpos($new_file_name, $path)===false) {
				$new_file_name = $path . $new_file_name;
			}
			$rec = str_replace("\n1 FILE ".$old_file_name, "\n1 FILE " . $new_file_name, $rec);
		}
	}
	return $rec;
}

/*
 * Export the database in GEDCOM format
 *
 *  input parameters:
 * $gedcom:         GEDCOM to be exported
 * $gedout:         Handle of output file
 * $exportOptions:  array of options for this Export operation as follows:
 *  'privatize':    which Privacy rules apply?  (none, visitor, user, manager)
 *  'toANSI':       should the output be produced in ANSI instead of UTF-8?  (yes, no)
 *  'path':         what constant should prefix all media file paths?  (eg: media/  or c:\my pictures\my family
 *  'slashes':      what folder separators apply to media file paths?  (forward, backward)
 */
function export_gedcom($gedcom, $gedout, $exportOptions) {
	global $GEDCOM;

	// Temporarily switch to the specified GEDCOM
	$oldGEDCOM	= $GEDCOM;
	$GEDCOM		= $gedcom;
	$ged_id		= get_id_from_gedcom($gedcom);

	switch($exportOptions['privatize']) {
	case 'gedadmin':
		$access_level=KT_PRIV_NONE;
		break;
	case 'user':
		$access_level=KT_PRIV_USER;
		break;
	case 'visitor':
		$access_level=KT_PRIV_PUBLIC;
		break;
	case 'none':
		$access_level=KT_PRIV_HIDE;
		break;
	}

	$head = gedcom_header($gedcom);
	if ($exportOptions['toANSI']=="yes") {
		$head = str_replace("UTF-8", "ANSI", $head);
		$head = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $head);
	}

	// Buffer the output.  Lots of small fwrite() calls can be very slow when writing large gedcoms.
	$buffer = reformat_record_export($head);

	$rows = KT_DB::prepare(
		"SELECT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec".
		" FROM `##individuals` WHERE i_file=? ORDER BY i_id"
	)->execute(array($ged_id))->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rows as $row) {
		[$rec] = KT_Person::getInstance($row)->privatizeGedcom($access_level);
		if ($exportOptions['toANSI']=="yes") {
			$rec = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $rec);
		}
		$buffer .= reformat_record_export($rec);
		if (strlen($buffer)>65536) {
			fwrite($gedout, $buffer);
			$buffer = '';
		}
	}

	$rows = KT_DB::prepare(
		"SELECT 'FAM' AS type, f_id AS xref, f_file AS ged_id, f_gedcom AS gedrec".
		" FROM `##families` WHERE f_file=? ORDER BY f_id"
	)->execute(array($ged_id))->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rows as $row) {
		[$rec] = KT_Family::getInstance($row)->privatizeGedcom($access_level);
		if ($exportOptions['toANSI']=="yes") {
			$rec = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $rec);
		}
		$buffer .= reformat_record_export($rec);
		if (strlen($buffer)>65536) {
			fwrite($gedout, $buffer);
			$buffer = '';
		}
	}

	$rows = KT_DB::prepare(
		"SELECT 'SOUR' AS type, s_id AS xref, s_file AS ged_id, s_gedcom AS gedrec".
		" FROM `##sources` WHERE s_file=? ORDER BY s_id"
	)->execute(array($ged_id))->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rows as $row) {
		[$rec] = KT_Source::getInstance($row)->privatizeGedcom($access_level);
		if ($exportOptions['toANSI'] == "yes") {
			$rec = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $rec);
		}
		$buffer .= reformat_record_export($rec);
		if (strlen($buffer) > 65536) {
			fwrite($gedout, $buffer);
			$buffer = '';
		}
	}

	$rows = KT_DB::prepare(
		"SELECT o_type AS type, o_id AS xref, o_file AS ged_id, o_gedcom AS gedrec".
		" FROM `##other` WHERE o_file=? AND o_type!=? AND o_type!=? ORDER BY o_id"
	)->execute(array($ged_id, 'HEAD', 'TRLR'))->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rows as $row) {
		[$rec] = KT_GedcomRecord::getInstance($row)->privatizeGedcom($access_level);
		if ($exportOptions['toANSI'] == "yes") {
			$rec=iconv("UTF-8", "ISO-8859-1//TRANSLIT", $rec);
		}
		$buffer .= reformat_record_export($rec);
		if (strlen($buffer)>65536) {
			fwrite($gedout, $buffer);
			$buffer = '';
		}
	}

	$rows = KT_DB::prepare(
		"SELECT 'OBJE' AS type, m_id AS xref, m_file AS ged_id, m_gedcom AS gedrec, m_titl, m_filename".
		" FROM `##media` WHERE m_file=? ORDER BY m_id"
	)->execute(array($ged_id))->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rows as $row) {
		[$rec] = KT_Media::getInstance($row)->privatizeGedcom($access_level);
		$rec = convert_media_path($rec, $exportOptions['path']);
		if ($exportOptions['toANSI'] == "yes") {
			$rec=iconv("UTF-8", "ISO-8859-1//TRANSLIT", $rec);
		}
		$buffer .= reformat_record_export($rec);
		if (strlen($buffer)>65536) {
			fwrite($gedout, $buffer);
			$buffer = '';
		}
	}

	fwrite($gedout, $buffer."0 TRLR".KT_EOL);

	$GEDCOM = $oldGEDCOM;
}
