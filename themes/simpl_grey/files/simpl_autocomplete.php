<?php
// Returns data for autocompletion
//
// webtrees: Web based Family History software
// Copyright (C) 2012 webtrees development team.
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
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
// $Id: autocomplete.php 14388 2012-10-04 08:32:27Z greg $

define('WT_SCRIPT_NAME', 'simpl_autocomplete.php');
require '../../../includes/session.php';

header('Content-Type: text/plain; charset=UTF-8');

// We have finished writing session data, so release the lock
Zend_Session::writeClose();

$term=safe_GET('term', WT_REGEX_UNSAFE); // we can search on '"><& etc.
$type=safe_GET('field');

switch ($type) {
case 'SPFX': // Name prefixes, that start with the search term
case 'NPFX':
case 'NSFX':
	// Do not filter by privacy.  Surnames on their own do not identify individuals.
	echo json_encode(
		WT_DB::prepare(
			"SELECT SQL_CACHE DISTINCT SUBSTRING_INDEX(SUBSTRING_INDEX(i_gedcom, CONCAT('\n2 ', ?, ' '), -1), '\n', 1)".
			" FROM `##individuals`".
			" WHERE i_gedcom LIKE CONCAT('%\n2 ', ?, ' ', ?, '%') AND i_file=?".
			" ORDER BY 1"
		)
		->execute(array($type, $type, $term, WT_GED_ID))
		->fetchOneColumn()
	);
	exit;
}
