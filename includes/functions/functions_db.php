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

////////////////////////////////////////////////////////////////////////////////
// Fetch records linked to a given record
////////////////////////////////////////////////////////////////////////////////
function fetch_linked_indi($xref, $link, $ged_id) {
	$rows=KT_DB::prepare(
		"SELECT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec".
		" FROM `##individuals`".
		" JOIN `##link` ON (i_file=l_file AND i_id=l_from)".
		" LEFT JOIN `##name` ON (i_file=n_file AND i_id=n_id AND n_num=0)".
		" WHERE i_file=? AND l_type=? AND l_to=?".
		" ORDER BY n_sort COLLATE '".KT_I18N::$collation."'"
	)->execute(array($ged_id, $link, $xref))->fetchAll(PDO::FETCH_ASSOC);

	$list=array();
	foreach ($rows as $row) {
		$list[]=KT_Person::getInstance($row);
	}
	return $list;
}
function fetch_linked_fam($xref, $link, $ged_id) {
	$rows=KT_DB::prepare(
		"SELECT 'FAM' AS type, f_id AS xref, f_file AS ged_id, f_gedcom AS gedrec".
		" FROM `##families`".
		" JOIN `##link` ON (f_file=l_file AND f_id=l_from)".
		" LEFT JOIN `##name` ON (f_file=n_file AND f_id=n_id AND n_num=0)".
		" WHERE f_file=? AND l_type=? AND l_to=?".
		" ORDER BY n_sort" // n_sort is not used for families.  Sorting here has no effect???
	)->execute(array($ged_id, $link, $xref))->fetchAll(PDO::FETCH_ASSOC);

	$list=array();
	foreach ($rows as $row) {
		$list[]=KT_Family::getInstance($row);
	}
	return $list;
}
function fetch_linked_note($xref, $link, $ged_id) {
	$rows=KT_DB::prepare(
		"SELECT 'NOTE' AS type, o_id AS xref, o_file AS ged_id, o_gedcom AS gedrec".
		" FROM `##other`".
		" JOIN `##link` ON (o_file=l_file AND o_id=l_from)".
		" LEFT JOIN `##name` ON (o_file=n_file AND o_id=n_id AND n_num=0)".
		" WHERE o_file=? AND o_type='NOTE' AND l_type=? AND l_to=?".
		" ORDER BY n_sort COLLATE '".KT_I18N::$collation."'"
	)->execute(array($ged_id, $link, $xref))->fetchAll(PDO::FETCH_ASSOC);

	$list=array();
	foreach ($rows as $row) {
		$list[]=KT_Note::getInstance($row);
	}
	return $list;
}
function fetch_linked_sour($xref, $link, $ged_id) {
	$rows=KT_DB::prepare(
			"SELECT 'SOUR' AS type, s_id AS xref, s_file AS ged_id, s_gedcom AS gedrec".
			" FROM `##sources`".
			" JOIN `##link` ON (s_file=l_file AND s_id=l_from)".
			" WHERE s_file=? AND l_type=? AND l_to=?".
			" ORDER BY s_name COLLATE '".KT_I18N::$collation."'"
		)->execute(array($ged_id, $link, $xref))->fetchAll(PDO::FETCH_ASSOC);

	$list=array();
	foreach ($rows as $row) {
		$list[]=KT_Source::getInstance($row);
	}
	return $list;
}
function fetch_linked_repo($xref, $link, $ged_id) {
	$rows=KT_DB::prepare(
		"SELECT 'REPO' AS type, o_id AS xref, o_file AS ged_id, o_gedcom AS gedrec".
		" FROM `##other`".
		" JOIN `##link` ON (o_file=l_file AND o_id=l_from)".
		" LEFT JOIN `##name` ON (o_file=n_file AND o_id=n_id AND n_num=0)".
		" WHERE o_file=? AND o_type='REPO' AND l_type=? AND l_to=?".
		" ORDER BY n_sort COLLATE '".KT_I18N::$collation."'"
	)->execute(array($ged_id, $link, $xref))->fetchAll(PDO::FETCH_ASSOC);

	$list=array();
	foreach ($rows as $row) {
		$list[]=KT_Note::getInstance($row);
	}
	return $list;
}
function fetch_linked_obje($xref, $link, $ged_id) {
	$rows=KT_DB::prepare(
		"SELECT 'OBJE' AS type, m_id AS xref, m_file AS ged_id, m_gedcom AS gedrec, m_titl, m_filename".
		" FROM `##media`".
		" JOIN `##link` ON (m_file=l_file AND m_id=l_from)".
		" WHERE m_file=? AND l_type=? AND l_to=?".
		" ORDER BY m_titl COLLATE '".KT_I18N::$collation."'"
	)->execute(array($ged_id, $link, $xref))->fetchAll(PDO::FETCH_ASSOC);

	$list=array();
	foreach ($rows as $row) {
		$list[]=KT_Media::getInstance($row);
	}
	return $list;
}

////////////////////////////////////////////////////////////////////////////////
// Fetch all records linked to a record - when deleting an object, we must
// also delete all links to it.
////////////////////////////////////////////////////////////////////////////////
function fetch_all_links($xref, $ged_id) {
	return
		KT_DB::prepare("SELECT l_from FROM `##link` WHERE l_file=? AND l_to=?")
		->execute(array($ged_id, $xref))
		->fetchOneColumn();
}

// find the gedcom record for a family
function find_family_record($xref, $ged_id) {
	static $statement = null;

	if (is_null($statement)) {
		$statement = KT_DB::prepare(
			"SELECT f_gedcom FROM `##families` WHERE f_id=? AND f_file=?"
		);
	}
	return $statement->execute(array($xref, $ged_id))->fetchOne();
}

// find the gedcom record for an individual
function find_person_record($xref, $ged_id) {
	static $statement = null;

	if (is_null($statement)) {
		$statement = KT_DB::prepare(
			"SELECT i_gedcom FROM `##individuals` WHERE i_id=? AND i_file=?"
		);
	}
	return $statement->execute(array($xref, $ged_id))->fetchOne();
}

// find the gedcom record for a source
function find_source_record($xref, $ged_id) {
	static $statement = null;

	if (is_null($statement)) {
		$statement = KT_DB::prepare(
			"SELECT s_gedcom FROM `##sources` WHERE s_id=? AND s_file=?"
		);
	}
	return $statement->execute(array($xref, $ged_id))->fetchOne();
}

/**
* Find a repository record by its ID
* @param string $rid the record id
* @param string $gedfile the gedcom file id
*/
function find_other_record($xref, $ged_id) {
	static $statement = null;

	if (is_null($statement)) {
		$statement = KT_DB::prepare(
			"SELECT o_gedcom FROM `##other` WHERE o_id=? AND o_file=?"
		);
	}
	return $statement->execute(array($xref, $ged_id))->fetchOne();
}

/**
* Find a media record by its ID
* @param string $rid the record id
*/
function find_media_record($xref, $ged_id) {
	static $statement = null;

	if (is_null($statement)) {
		$statement = KT_DB::prepare(
			"SELECT m_gedcom FROM `##media` WHERE m_id=? AND m_file=?"
		);
	}
	return $statement->execute(array($xref, $ged_id))->fetchOne();
}

// Find the gedcom data for a record. Optionally include pending changes.
function find_gedcom_record($xref, $ged_id, $pending=false) {
	if ($pending) {
		// This will return NULL if no record exists, or an empty string if the record has been deleted.
		$gedcom = find_updated_record($xref, $ged_id);
	} else {
		$gedcom = null;
	}

	if (is_null($gedcom)) {
		$gedcom = find_person_record($xref, $ged_id);
	}
	if (is_null($gedcom)) {
		$gedcom = find_family_record($xref, $ged_id);
	}
	if (is_null($gedcom)) {
		$gedcom = find_source_record($xref, $ged_id);
	}
	if (is_null($gedcom)) {
		$gedcom = find_media_record($xref, $ged_id);
	}
	if (is_null($gedcom)) {
		$gedcom = find_other_record($xref, $ged_id);
	}
	return $gedcom;
}

/**
 * find and return an updated gedcom record
 * @param string $gid the id of the record to find
 * @param string $gedfile the gedcom file to get the record from.. defaults to currently active gedcom
 */
function find_updated_record($xref, $ged_id) {
	static $statement = null;

	if (is_null($statement)) {
		$statement = KT_DB::prepare(
			"SELECT new_gedcom FROM `##change` WHERE gedcom_id=? AND xref=? AND status='pending' ".
			"ORDER BY change_id DESC LIMIT 1"
		);
	}

	// This will return NULL if no record exists, or an empty string if the record has been deleted.
	return $gedcom=$statement->execute(array($ged_id, $xref))->fetchOne();
}

// Find the type of a gedcom record. Check the cache before querying the database.
// Returns 'INDI', 'FAM', etc., or null if the record does not exist.
function gedcom_record_type($xref, $ged_id) {
	global $gedcom_record_cache;
	static $statement = null;

	if (is_null($statement)) {
		$statement = KT_DB::prepare(
			"SELECT 'INDI' FROM `##individuals` WHERE i_id=? AND i_file=? UNION ALL ".
			"SELECT 'FAM'  FROM `##families`    WHERE f_id=? AND f_file=? UNION ALL ".
			"SELECT 'SOUR' FROM `##sources`     WHERE s_id=? AND s_file=? UNION ALL ".
			"SELECT 'OBJE' FROM `##media`       WHERE m_id=? AND m_file=? UNION ALL ".
			"SELECT o_type FROM `##other`       WHERE o_id=? AND o_file=?"
		);
	}

	if (isset($gedcom_record_cache[$xref][$ged_id])) {
		return $gedcom_record_cache[$xref][$ged_id]->getType();
	} else {
		return $statement->execute(array($xref, $ged_id, $xref, $ged_id, $xref, $ged_id, $xref, $ged_id, $xref, $ged_id))->fetchOne();
	}
}

// Find out if there are any pending changes that a given user may accept
function exists_pending_change($user_id=KT_USER_ID, $ged_id=KT_GED_ID) {
	return
		KT_Tree::get($ged_id)->canAcceptChanges($user_id) &&
		KT_DB::prepare(
			"SELECT 1".
			" FROM `##change`".
			" WHERE status='pending' AND gedcom_id=?"
		)->execute(array($ged_id))->fetchOne();
}

// get a list of all the sources
function get_source_list($ged_id) {
	$rows =
		KT_DB::prepare("SELECT 'SOUR' AS type, s_id AS xref, s_file AS ged_id, s_gedcom AS gedrec FROM `##sources` s WHERE s_file=?")
		->execute(array($ged_id))
		->fetchAll(PDO::FETCH_ASSOC);

	$list = array();
	foreach ($rows as $row) {
		$list[] = KT_Source::getInstance($row);
	}
	usort($list, array('KT_GedcomRecord', 'Compare'));
	return $list;
}

// Get a list of repositories from the database
// $ged_id - the gedcom to search
function get_repo_list($ged_id) {
	$rows=
		KT_DB::prepare("SELECT 'REPO' AS type, o_id AS xref, o_file AS ged_id, o_gedcom AS gedrec FROM `##other` WHERE o_type='REPO' AND o_file=?")
		->execute(array($ged_id))
		->fetchAll(PDO::FETCH_ASSOC);

	$list=array();
	foreach ($rows as $row) {
		$list[]=KT_Repository::getInstance($row);
	}
	usort($list, array('KT_GedcomRecord', 'Compare'));
	return $list;
}

//-- get the shared note list from the datastore
function get_note_list($ged_id) {
	$rows=
		KT_DB::prepare("SELECT 'NOTE' AS type, o_id AS xref, {$ged_id} AS ged_id, o_gedcom AS gedrec FROM `##other` WHERE o_type=? AND o_file=?")
		->execute(array('NOTE', $ged_id))
		->fetchAll(PDO::FETCH_ASSOC);

	$list=array();
	foreach ($rows as $row) {
		$list[]=KT_Note::getInstance($row);
	}
	usort($list, array('KT_GedcomRecord', 'Compare'));
	return $list;
}

// Search the gedcom records of indis
// $query - array of search terms
// $geds - array of gedcoms to search
// $match - AND or OR
function search_indis($query, $geds, $match) {
	global $GEDCOM;

	// No query => no results
	if (!$query) {
		return array();
	}

	// Convert the query into a SQL expression
	$querysq	= array();
	// Convert the query into a regular expression
	$queryregex	= array();

	foreach ($query as $q) {
		$queryregex[]=preg_quote(utf8_strtoupper($q), '/');
		$querysql[]="i_gedcom LIKE ".KT_DB::quote("%{$q}%")." COLLATE '".KT_I18N::$collation."'";
	}

	$sql="
		SELECT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec
		 FROM `##individuals`
		 WHERE (" . implode(" {$match} ", $querysql) . ") AND
		 i_file IN (" . implode(',', $geds) . ")
	";

	// Group results by gedcom, to minimise switching between privacy files
	$sql.=' ORDER BY ged_id';

	$list=array();
	$rows=KT_DB::prepare($sql)->fetchAll(PDO::FETCH_ASSOC);
	$GED_ID=KT_GED_ID;
	foreach ($rows as $row) {
		// Switch privacy file if necessary
		if ($row['ged_id']!=$GED_ID) {
			$GEDCOM=get_gedcom_from_id($row['ged_id']);
			load_gedcom_settings($row['ged_id']);
			$GED_ID=$row['ged_id'];
		}
		// SQL may have matched on private data or gedcom tags, so check again against privatized data.
		$record=KT_Person::getInstance($row);
		// Ignore non-genealogical data
		$gedrec=preg_replace('/\n\d (_UID|_KT_USER|FILE|FORM|TYPE|CHAN|REFN|RESN) .*/', '', $record->getGedcomRecord());
		// Ignore links and tags
		$gedrec=preg_replace('/\n\d '.KT_REGEX_TAG.'( @'.KT_REGEX_XREF.'@)?/', '', $gedrec);
		// Re-apply the filtering
		$gedrec=utf8_strtoupper($gedrec);
		foreach ($queryregex as $regex) {
			if (!preg_match('/'.$regex.'/', $gedrec)) {
				continue 2;
			}
		}
		$list[]=$record;
	}
	// Switch privacy file if necessary
	if ($GED_ID!=KT_GED_ID) {
		$GEDCOM=KT_GEDCOM;
		load_gedcom_settings(KT_GED_ID);
	}
	return $list;
}

// Search the names of indis
// $query - array of search terms
// $geds - array of gedcoms to search
// $match - AND or OR
function search_indis_names($query, $geds, $match) {
	global $GEDCOM;

	// No query => no results
	if (!$query) {
		return array();
	}

	// Convert the query into a SQL expression
	$querysql=array();
	foreach ($query as $q) {
		$querysql[]="n_full LIKE ".KT_DB::quote("%{$q}%")." COLLATE '".KT_I18N::$collation."'";
	}
	$sql="SELECT DISTINCT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec, n_num FROM `##individuals` JOIN `##name` ON i_id=n_id AND i_file=n_file WHERE (".implode(" {$match} ", $querysql).') AND i_file IN ('.implode(',', $geds).')';

	// Group results by gedcom, to minimise switching between privacy files
	$sql.=' ORDER BY ged_id';

	$list=array();
	$rows=KT_DB::prepare($sql)->fetchAll(PDO::FETCH_ASSOC);
	$GED_ID=KT_GED_ID;
	foreach ($rows as $row) {
		// Switch privacy file if necessary
		if ($row['ged_id']!=$GED_ID) {
			$GEDCOM=get_gedcom_from_id($row['ged_id']);
			load_gedcom_settings($row['ged_id']);
			$GED_ID=$row['ged_id'];
		}
		$indi=KT_Person::getInstance($row);
		if ($indi->canDisplayName()) {
			$indi->setPrimaryName($row['n_num']);
			// We need to clone $indi, as we may have multiple references to the
			// same person in this list, and the "primary name" would otherwise
			// be shared amongst all of them.  This has some performance/memory
			// implications, and there is probably a better way.  This, however,
			// is clean, easy and works.
			$list[]=clone $indi;
		}
	}
	// Switch privacy file if necessary
	if ($GED_ID!=KT_GED_ID) {
		$GEDCOM=KT_GEDCOM;
		load_gedcom_settings(KT_GED_ID);
	}
	return $list;
}

// Search for individuals names/places using soundex
// $soundex - standard or dm
// $lastname, $firstname, $place - search terms
// $geds - array of gedcoms to search
function search_indis_soundex($soundex, $lastname, $firstname, $place, $geds) {
	$sql="SELECT DISTINCT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec FROM `##individuals`";
	if ($place) {
		$sql.=" JOIN `##placelinks` ON (pl_file=i_file AND pl_gid=i_id)";
		$sql.=" JOIN `##places` ON (p_file=pl_file AND pl_p_id=p_id)";
	}
	if ($firstname || $lastname) {
		$sql.=" JOIN `##name` ON (i_file=n_file AND i_id=n_id)";
			}
	$sql.=' WHERE i_file IN ('.implode(',', $geds).')';
	switch ($soundex) {
	case 'Russell':
		$givn_sdx=explode(':', KT_Soundex::soundex_std($firstname));
		$surn_sdx=explode(':', KT_Soundex::soundex_std($lastname));
		$plac_sdx=explode(':', KT_Soundex::soundex_std($place));
		$field='std';
		break;
	default:
	case 'DaitchM':
		$givn_sdx=explode(':', KT_Soundex::soundex_dm($firstname));
		$surn_sdx=explode(':', KT_Soundex::soundex_dm($lastname));
		$plac_sdx=explode(':', KT_Soundex::soundex_dm($place));
		$field='dm';
		break;
	}
	if ($firstname && $givn_sdx) {
		foreach ($givn_sdx as $k=>$v) {
			$givn_sdx[$k]="n_soundex_givn_{$field} LIKE ".KT_DB::quote("%{$v}%");
	}
		$sql.=' AND ('.implode(' OR ', $givn_sdx).')';
		}
	if ($lastname && $surn_sdx) {
		foreach ($surn_sdx as $k=>$v) {
			$surn_sdx[$k]="n_soundex_surn_{$field} LIKE ".KT_DB::quote("%{$v}%");
		}
		$sql.=' AND ('.implode(' OR ', $surn_sdx).')';
			}
	if ($place && $plac_sdx) {
		foreach ($plac_sdx as $k=>$v) {
			$plac_sdx[$k]="p_{$field}_soundex LIKE ".KT_DB::quote("%{$v}%");
		}
		$sql.=' AND ('.implode(' OR ', $plac_sdx).')';
	}

	// Group results by gedcom, to minimise switching between privacy files
	$sql.=' ORDER BY ged_id';

	$list=array();
	$rows=KT_DB::prepare($sql)->fetchAll(PDO::FETCH_ASSOC);
	$GED_ID=KT_GED_ID;
	foreach ($rows as $row) {
		// Switch privacy file if necessary
		if ($row['ged_id']!=$GED_ID) {
			$GEDCOM=get_gedcom_from_id($row['ged_id']);
			load_gedcom_settings($row['ged_id']);
			$GED_ID=$row['ged_id'];
		}
		$indi=KT_Person::getInstance($row);
		if ($indi->canDisplayName()) {
			$list[]=$indi;
		}
	}
	// Switch privacy file if necessary
	if ($GED_ID!=KT_GED_ID) {
		$GEDCOM=KT_GEDCOM;
		load_gedcom_settings(KT_GED_ID);
	}
	return $list;
}

/**
* get recent changes since the given julian day inclusive
* @author yalnifj
* @param int $jd, leave empty to include all
*/
function get_recent_changes($jd=0, $allgeds=false) {
	$sql="SELECT d_gid FROM `##dates` WHERE d_fact='CHAN' AND d_julianday1>=?";
	$vars=array($jd);
	if (!$allgeds) {
		$sql.=" AND d_file=?";
		$vars[]=KT_GED_ID;
	}
	$sql.=" ORDER BY d_julianday1 DESC";

	return KT_DB::prepare($sql)->execute($vars)->fetchOneColumn();
}

// Seach for individuals with events on a given day
function search_indis_dates($day, $month, $year, $facts) {
	$sql="SELECT DISTINCT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec FROM `##individuals` JOIN `##dates` ON i_id=d_gid AND i_file=d_file WHERE i_file=?";
	$vars=array(KT_GED_ID);
	if ($day) {
		$sql.=" AND d_day=?";
		$vars[]=$day;
	}
	if ($month) {
		$sql.=" AND d_month=?";
		$vars[]=$month;
	}
	if ($year) {
		$sql.=" AND d_year=?";
		$vars[]=$year;
	}
	if ($facts) {
		$facts=preg_split('/[, ;]+/', $facts);
		foreach ($facts as $key=>$value) {
			if ($value[0]=='!') {
				$facts[$key]="d_fact!=?";
				$vars[]=substr($value,1);
			} else {
				$facts[$key]="d_fact=?";
				$vars[]=$value;
			}
		}
		$sql.=' AND '.implode(' AND ', $facts);
	}

	$list=array();
	$rows=KT_DB::prepare($sql)->execute($vars)->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rows as $row) {
		$list[]=KT_Person::getInstance($row);
	}
	return $list;
}

// Search the gedcom records of families
// $query - array of search terms
// $geds - array of gedcoms to search
// $match - AND or OR
function search_fams($query, $geds, $match) {
	global $GEDCOM;

	// No query => no results
	if (!$query) {
		return array();
	}

	// Convert the query into a SQL expression
	$querysql=array();
	// Convert the query into a regular expression
	$queryregex=array();

	foreach ($query as $q) {
		$queryregex[]=preg_quote(utf8_strtoupper($q), '/');
		$querysql[]="f_gedcom LIKE ".KT_DB::quote("%{$q}%")." COLLATE '".KT_I18N::$collation."'";
	}

	$sql="SELECT 'FAM' AS type, f_id AS xref, f_file AS ged_id, f_gedcom AS gedrec FROM `##families` WHERE (".implode(" {$match} ", $querysql).') AND f_file IN ('.implode(',', $geds).')';

	// Group results by gedcom, to minimise switching between privacy files
	$sql.=' ORDER BY ged_id';

	$list=array();
	$rows=KT_DB::prepare($sql)->fetchAll(PDO::FETCH_ASSOC);
	$GED_ID=KT_GED_ID;
	foreach ($rows as $row) {
		// Switch privacy file if necessary
		if ($row['ged_id']!=$GED_ID) {
			$GEDCOM=get_gedcom_from_id($row['ged_id']);
			load_gedcom_settings($row['ged_id']);
			$GED_ID=$row['ged_id'];
		}
		// SQL may have matched on private data or gedcom tags, so check again against privatized data.
		$record=KT_Person::getInstance($row);
		// Ignore non-genealogical data
		$gedrec=preg_replace('/\n\d (_UID|_KT_USER|FILE|FORM|TYPE|CHAN|REFN|RESN) .*/', '', $record->getGedcomRecord());
		// Ignore links and tags
		$gedrec=preg_replace('/\n\d '.KT_REGEX_TAG.'( @'.KT_REGEX_XREF.'@)?/', '', $gedrec);
		// Ignore tags
		$gedrec=preg_replace('/\n\d '.KT_REGEX_TAG.' ?/', '', $gedrec);
		// Re-apply the filtering
		$gedrec=utf8_strtoupper($gedrec);
		foreach ($queryregex as $regex) {
			if (!preg_match('/'.$regex.'/', $gedrec)) {
				continue 2;
			}
		}
		$list[]=$record;
	}
	// Switch privacy file if necessary
	if ($GED_ID!=KT_GED_ID) {
		$GEDCOM=KT_GEDCOM;
		load_gedcom_settings(KT_GED_ID);
	}
	return $list;
}

// Search the names of the husb/wife in a family
// $query - array of search terms
// $geds - array of gedcoms to search
// $match - AND or OR
function search_fams_names($query, $geds, $match) {
	global $GEDCOM;

	// No query => no results
	if (!$query) {
		return array();
	}

	// Convert the query into a SQL expression
	$querysql=array();
	foreach ($query as $q) {
		$querysql[]="(husb.n_full LIKE ".KT_DB::quote("%{$q}%")." COLLATE '".KT_I18N::$collation."' OR wife.n_full LIKE ".KT_DB::quote("%{$q}%")." COLLATE '".KT_I18N::$collation."')";
	}

	$sql="SELECT DISTINCT 'FAM' AS type, f_id AS xref, f_file AS ged_id, f_gedcom AS gedrec FROM `##families` LEFT OUTER JOIN `##name` husb ON f_husb=husb.n_id AND f_file=husb.n_file LEFT OUTER JOIN `##name` wife ON f_wife=wife.n_id AND f_file=wife.n_file WHERE (".implode(" {$match} ", $querysql).') AND f_file IN ('.implode(',', $geds).')';

	// Group results by gedcom, to minimise switching between privacy files
	$sql.=' ORDER BY ged_id';

	$list=array();
	$rows=KT_DB::prepare($sql)->fetchAll(PDO::FETCH_ASSOC);
	$GED_ID=KT_GED_ID;
	foreach ($rows as $row) {
		// Switch privacy file if necessary
		if ($row['ged_id']!=$GED_ID) {
			$GEDCOM=get_gedcom_from_id($row['ged_id']);
			load_gedcom_settings($row['ged_id']);
			$GED_ID=$row['ged_id'];
		}
		$indi=KT_Family::getInstance($row);
		if ($indi->canDisplayName()) {
			$list[]=$indi;
		}
	}
	// Switch privacy file if necessary
	if ($GED_ID!=KT_GED_ID) {
		$GEDCOM=KT_GEDCOM;
		load_gedcom_settings(KT_GED_ID);
	}
	return $list;
}

// Search the gedcom records of sources
// $query - array of search terms
// $geds - array of gedcoms to search
// $match - AND or OR
function search_sources($query, $geds, $match) {
	global $GEDCOM;

	// No query => no results
	if (!$query) {
		return array();
	}

	// Convert the query into a SQL expression
	$querysql=array();
	// Convert the query into a regular expression
	$queryregex=array();

	foreach ($query as $q) {
		$queryregex[]=preg_quote(utf8_strtoupper($q), '/');
		$querysql[]="s_gedcom LIKE ".KT_DB::quote("%{$q}%")." COLLATE '".KT_I18N::$collation."'";
	}

	$sql="SELECT 'SOUR' AS type, s_id AS xref, s_file AS ged_id, s_gedcom AS gedrec FROM `##sources` WHERE (".implode(" {$match} ", $querysql).') AND s_file IN ('.implode(',', $geds).')';

	// Group results by gedcom, to minimise switching between privacy files
	$sql.=' ORDER BY ged_id';

	$list=array();
	$rows = KT_DB::prepare($sql)->fetchAll(PDO::FETCH_ASSOC);
	$GED_ID=KT_GED_ID;
	foreach ($rows as $row) {
		// Switch privacy file if necessary
		if ($row['ged_id']!=$GED_ID) {
			$GEDCOM=get_gedcom_from_id($row['ged_id']);
			load_gedcom_settings($row['ged_id']);
			$GED_ID=$row['ged_id'];
		}
		// SQL may have matched on private data or gedcom tags, so check again against privatized data.
		$record=KT_Person::getInstance($row);
		// Ignore non-genealogical data
		$gedrec=preg_replace('/\n\d (_UID|_KT_USER|FILE|FORM|TYPE|CHAN|REFN|RESN) .*/', '', $record->getGedcomRecord());
		// Ignore links and tags
		$gedrec=preg_replace('/\n\d '.KT_REGEX_TAG.'( @'.KT_REGEX_XREF.'@)?/', '', $gedrec);
		// Ignore tags
		$gedrec=preg_replace('/\n\d '.KT_REGEX_TAG.' ?/', '', $gedrec);
		// Re-apply the filtering
		$gedrec=utf8_strtoupper($gedrec);
		foreach ($queryregex as $regex) {
			if (!preg_match('/'.$regex.'/', $gedrec)) {
				continue 2;
			}
		}
		$list[]=$record;
	}
	// Switch privacy file if necessary
	if ($GED_ID!=KT_GED_ID) {
		$GEDCOM=KT_GEDCOM;
		load_gedcom_settings(KT_GED_ID);
	}
	return $list;
}

// Search the gedcom records of shared notes
// $query - array of search terms
// $geds - array of gedcoms to search
// $match - AND or OR
function search_notes($query, $geds, $match) {
	global $GEDCOM;

	// No query => no results
	if (!$query) {
		return array();
	}

	// Convert the query into a SQL expression
	$querysql=array();
	// Convert the query into a regular expression
	$queryregex=array();

	foreach ($query as $q) {
		$queryregex[]=preg_quote(utf8_strtoupper($q), '/');
		$querysql[]="o_gedcom LIKE ".KT_DB::quote("%{$q}%")." COLLATE '".KT_I18N::$collation."'";
	}

	$sql="SELECT 'NOTE' AS type, o_id AS xref, o_file AS ged_id, o_gedcom AS gedrec FROM `##other` WHERE (".implode(" {$match} ", $querysql).") AND o_type='NOTE' AND o_file IN (".implode(',', $geds).')';

	// Group results by gedcom, to minimise switching between privacy files
	$sql.=' ORDER BY ged_id';

	$list=array();
	$rows=KT_DB::prepare($sql)->fetchAll(PDO::FETCH_ASSOC);
	$GED_ID=KT_GED_ID;
	foreach ($rows as $row) {
		// Switch privacy file if necessary
		if ($row['ged_id']!=$GED_ID) {
			$GEDCOM=get_gedcom_from_id($row['ged_id']);
			load_gedcom_settings($row['ged_id']);
			$GED_ID=$row['ged_id'];
		}
		// SQL may have matched on private data or gedcom tags, so check again against privatized data.
		$record=KT_Person::getInstance($row);
		// Ignore non-genealogical data
		$gedrec=preg_replace('/\n\d (_UID|_KT_USER|FILE|FORM|TYPE|CHAN|REFN|RESN) .*/', '', $record->getGedcomRecord());
		// Ignore links and tags
		$gedrec=preg_replace('/\n\d '.KT_REGEX_TAG.'( @'.KT_REGEX_XREF.'@)?/', '', $gedrec);
		// Ignore tags
		$gedrec=preg_replace('/\n\d '.KT_REGEX_TAG.' ?/', '', $gedrec);
		// Re-apply the filtering
		$gedrec=utf8_strtoupper($gedrec);
		foreach ($queryregex as $regex) {
			if (!preg_match('/'.$regex.'/', $gedrec)) {
				continue 2;
			}
		}
		$list[]=$record;
	}

	// Switch privacy file if necessary
	if ($GED_ID!=KT_GED_ID) {
		$GEDCOM=KT_GEDCOM;
		load_gedcom_settings(KT_GED_ID);
	}
	return $list;
}


// Search the gedcom records of repositories
// $query - array of search terms
// $geds - array of gedcoms to search
// $match - AND or OR
function search_repos($query, $geds, $match) {
	global $GEDCOM;

	// No query => no results
	if (!$query) {
		return array();
	}

	// Convert the query into a SQL expression
	$querysql=array();
	// Convert the query into a regular expression
	$queryregex=array();

	foreach ($query as $q) {
		$queryregex[]=preg_quote(utf8_strtoupper($q), '/');
		$querysql[]="o_gedcom LIKE ".KT_DB::quote("%{$q}%")." COLLATE '".KT_I18N::$collation."'";
	}

	$sql="SELECT 'REPO' AS type, o_id AS xref, o_file AS ged_id, o_gedcom AS gedrec FROM `##other` WHERE (".implode(" {$match} ", $querysql).") AND o_type='REPO' AND o_file IN (".implode(',', $geds).')';

	// Group results by gedcom, to minimise switching between privacy files
	$sql.=' ORDER BY ged_id';

	$list=array();
	$rows=KT_DB::prepare($sql)->fetchAll(PDO::FETCH_ASSOC);
	$GED_ID=KT_GED_ID;
	foreach ($rows as $row) {
		// Switch privacy file if necessary
		if ($row['ged_id']!=$GED_ID) {
			$GEDCOM=get_gedcom_from_id($row['ged_id']);
			load_gedcom_settings($row['ged_id']);
			$GED_ID=$row['ged_id'];
		}
		// SQL may have matched on private data or gedcom tags, so check again against privatized data.
		$record=KT_Person::getInstance($row);
		// Ignore non-genealogical data
		$gedrec=preg_replace('/\n\d (_UID|_KT_USER|FILE|FORM|TYPE|CHAN|REFN|RESN) .*/', '', $record->getGedcomRecord());
		// Ignore links and tags
		$gedrec=preg_replace('/\n\d '.KT_REGEX_TAG.'( @'.KT_REGEX_XREF.'@)?/', '', $gedrec);
		// Ignore tags
		$gedrec=preg_replace('/\n\d '.KT_REGEX_TAG.' ?/', '', $gedrec);
		// Re-apply the filtering
		$gedrec=utf8_strtoupper($gedrec);
		foreach ($queryregex as $regex) {
			if (!preg_match('/'.$regex.'/', $gedrec)) {
				continue 2;
			}
		}
		$list[]=$record;
	}
	// Switch privacy file if necessary
	if ($GED_ID!=KT_GED_ID) {
		$GEDCOM=KT_GEDCOM;
		load_gedcom_settings(KT_GED_ID);
	}
	return $list;
}

// Search the stories module contents
// $query - array of search terms
// $geds - array of gedcoms to search
// $match - AND or OR
function search_stories($query, $geds, $match) {
	global $GEDCOM;

	// No query => no results
	if (!$query) {
		return array();
	}

	// Convert the query into a SQL expression
	$querysql = array();
	foreach ($query as $q) {
		$querysql[] = "setting_value LIKE " . KT_DB::quote("%{$q}%") . " COLLATE '" . KT_I18N::$collation . "'";
	}

	$sql = "
		SELECT ##block_setting.`setting_value` as xref, ##block.`block_id` AS block_id, ##block.`gedcom_id` AS ged_id
		FROM ##block_setting
		JOIN ##block ON ##block.`block_id` = ##block_setting.`block_id`
		WHERE ##block.`block_id` IN (
			SELECT DISTINCT ##block.`block_id`
			FROM ##block_setting
			JOIN ##block ON ##block.`block_id` = ##block_setting.`block_id`
			WHERE (`setting_name` = 'story_body' AND " . implode(" {$match} ", $querysql) . ")
			OR (`setting_name` = 'title' AND " . implode(" {$match} ", $querysql) . ")
			AND ##block.`module_name` = 'stories'
			AND ##block.`gedcom_id` IN (" . implode(',', $geds) . ")
		)
		AND ##block_setting.`setting_name` = 'xref'
	";

	// Group results by gedcom, to minimise switching between privacy files
	$sql	 .= ' ORDER BY ged_id';
	$stories = KT_DB::prepare($sql)->fetchAll(PDO::FETCH_ASSOC);
	return $stories;
}

//-- function to find the gedcom id for the given rin
function find_rin_id($rin) {
	$xref=
		KT_DB::prepare("SELECT i_id FROM `##individuals` WHERE i_rin=? AND i_file=?")
		->execute(array($rin, KT_GED_ID))
		->fetchOne();

	return $xref ? $xref : $rin;
}

/**
 * Get array of common surnames
 *
 * This function returns a simple array of the most common surnames
 * found in the individuals list.
 * @param int $min the number of times a surname must occur before it is added to the array
 */
function get_common_surnames($min) {
	$COMMON_NAMES_ADD   =get_gedcom_setting(KT_GED_ID, 'COMMON_NAMES_ADD');
	$COMMON_NAMES_REMOVE=get_gedcom_setting(KT_GED_ID, 'COMMON_NAMES_REMOVE');

	$topsurns=get_top_surnames(KT_GED_ID, $min, 0);
	foreach (explode(',', $COMMON_NAMES_ADD) as $surname) {
		if ($surname && !array_key_exists($surname, $topsurns)) {
			$topsurns[$surname]=$min;
		}
	}
	foreach (explode(',', $COMMON_NAMES_REMOVE) as $surname) {
		unset($topsurns[utf8_strtoupper($surname)]);
	}

	//-- check if we found some, else recurse
	if (empty($topsurns) && $min>2) {
		return get_common_surnames($min/2);
	} else {
		uksort($topsurns, 'utf8_strcasecmp');
		foreach ($topsurns as $key=>$value) {
			$topsurns[$key]=array('name'=>$key, 'match'=>$value);
		}
		return $topsurns;
	}
}

/**
* get the top surnames
* @param int $ged_id fetch surnames from this gedcom
* @param int $min only fetch surnames occuring this many times
* @param int $max only fetch this number of surnames (0=all)
* @return array
*/
function get_top_surnames($ged_id, $min, $max) {
	// Use n_surn, rather than n_surname, as it is used to generate url's for
	// the indi-list, etc.
	$max=(int)$max;
	if ($max==0) {
		return
			KT_DB::prepare("SELECT n_surn, COUNT(n_surn) FROM `##name` WHERE n_file=? AND n_type!=? AND n_surn NOT IN (?, ?, ?, ?) GROUP BY n_surn HAVING COUNT(n_surn)>=? ORDER BY 2 DESC")
			->execute(array($ged_id, '_MARNM', '@N.N.', '', '?', 'UNKNOWN', $min))
			->fetchAssoc();
	} else {
		return
			KT_DB::prepare("SELECT n_surn, COUNT(n_surn) FROM `##name` WHERE n_file=? AND n_type!=? AND n_surn NOT IN (?, ?, ?, ?) GROUP BY n_surn HAVING COUNT(n_surn)>=? ORDER BY 2 DESC LIMIT ".$max)
			->execute(array($ged_id, '_MARNM', '@N.N.', '', '?', 'UNKNOWN', $min))
			->fetchAssoc();
	}
}

////////////////////////////////////////////////////////////////////////////////
// Get a list of events whose anniversary occured on a given julian day.
// Used on the on-this-day/upcoming blocks and the day/month calendar views.
// $jd     - the julian day
// $facts  - restrict the search to just these facts or leave blank for all
// $ged_id - the id of the gedcom to search
////////////////////////////////////////////////////////////////////////////////
function get_anniversary_events($jd, $facts='', $ged_id=KT_GED_ID) {
	// If no facts specified, get all except these
	$skipfacts = "CHAN,BAPL,SLGC,SLGS,ENDL,CENS,RESI,NOTE,ADDR,OBJE,SOUR,PAGE,DATA,TEXT";
	if ($facts!='_TODO') {
		$skipfacts.=',_TODO';
	}

	$found_facts=array();
	foreach (array(new KT_Date_Gregorian($jd), new KT_Date_Julian($jd), new KT_Date_French($jd), new KT_Date_Jewish($jd), new KT_Date_Hijri($jd), new KT_Date_Jalali($jd)) as $anniv) {
		// Build a SQL where clause to match anniversaries in the appropriate calendar.
		$where="WHERE d_type='".$anniv->Format('%@')."'";
		// SIMPLE CASES:
		// a) Non-hebrew anniversaries
		// b) Hebrew months TVT, SHV, IYR, SVN, TMZ, AAV, ELL
		if (!$anniv instanceof KT_Date_Jewish || in_array($anniv->m, array(1, 5, 9, 10, 11, 12, 13))) {
			// Dates without days go on the first day of the month
			// Dates with invalid days go on the last day of the month
			if ($anniv->d==1) {
				$where.=" AND d_day<=1";
			} else
				if ($anniv->d==$anniv->DaysInMonth()) {
					$where.=" AND d_day>={$anniv->d}";
				} else {
					$where.=" AND d_day={$anniv->d}";
				}
			$where.=" AND d_mon={$anniv->m}";
		} else {
			// SPECIAL CASES:
			switch ($anniv->m) {
    			case 2:
    				// 29 CSH does not include 30 CSH (but would include an invalid 31 CSH if there were no 30 CSH)
    				if ($anniv->d==1) {
    					$where.=" AND d_day<=1 AND d_mon=2";
    				} elseif ($anniv->d==30) {
    					$where.=" AND d_day>=30 AND d_mon=2";
    				} elseif ($anniv->d==29 && $anniv->DaysInMonth()==29) {
    					$where.=" AND (d_day=29 OR d_day>30) AND d_mon=2";
    				} else {
    					$where.=" AND d_day={$anniv->d} AND d_mon=2";
    				}
    			break;
    			case 3:
    				// 1 KSL includes 30 CSH (if this year didn't have 30 CSH)
    				// 29 KSL does not include 30 KSL (but would include an invalid 31 KSL if there were no 30 KSL)
    				if ($anniv->d===1) {
    					$tmp=new KT_Date_Jewish(array($anniv->y, 'csh', 1));
    					if ($tmp->DaysInMonth()==29) {
    						$where.=" AND (d_day<=1 AND d_mon=3 OR d_day=30 AND d_mon=2)";
    					} else {
    						$where.=" AND d_day<=1 AND d_mon=3";
    					}
    				} else
    					if ($anniv->d==30) {
    						$where.=" AND d_day>=30 AND d_mon=3";
    					} elseif ($anniv->d==29 && $anniv->DaysInMonth()==29) {
    						$where.=" AND (d_day=29 OR d_day>30) AND d_mon=3";
    					} else {
    						$where.=" AND d_day={$anniv->d} AND d_mon=3";
    					}
    			break;
    			case 4:
    				// 1 TVT includes 30 KSL (if this year didn't have 30 KSL)
    				if ($anniv->d===1) {
    					$tmp=new KT_Date_Jewish($anniv->y, 'ksl', 1);
    					if ($tmp->DaysInMonth()==29) {
    						$where.=" AND (d_day<=1 AND d_mon=4 OR d_day=30 AND d_mon=3)";
    					} else {
    						$where.=" AND d_day<=1 AND d_mon=4";
    					}
    				} else
    					if ($anniv->d===$anniv->DaysInMonth()) {
    						$where.=" AND d_day>={$anniv->d} AND d_mon=4";
    					} else {
    						$where.=" AND d_day={$anniv->d} AND d_mon=4";
    					}
    			break;
    			case 6: // ADR (non-leap) includes ADS (leap)
    				if ($anniv->d===1) {
    					$where.=" AND d_day<=1";
    				} elseif ($anniv->d==$anniv->DaysInMonth()) {
    					$where.=" AND d_day>={$anniv->d}";
    				} else {
    					$where.=" AND d_day={$anniv->d}";
    				}
    				if ($anniv->IsLeapYear()) {
    					$where.=" AND (d_mon=6 AND MOD(7*d_year+1, 19)<7)";
    				} else {
    					$where.=" AND (d_mon=6 OR d_mon=7)";
    				}
    			break;
    			case 7: // ADS includes ADR (non-leap)
    				if ($anniv->d===1) {
    					$where.=" AND d_day<=1";
    				} elseif ($anniv->d==$anniv->DaysInMonth()) {
    					$where.=" AND d_day>={$anniv->d}";
    				} else {
    					$where.=" AND d_day={$anniv->d}";
    				}
    				$where.=" AND (d_mon=6 AND MOD(7*d_year+1, 19)>=7 OR d_mon=7)";
    			break;
    			case 8: // 1 NSN includes 30 ADR, if this year is non-leap
    				if ($anniv->d===1) {
    					if ($anniv->IsLeapYear()) {
    						$where.=" AND d_day<=1 AND d_mon=8";
    					} else {
    						$where.=" AND (d_day<=1 AND d_mon=8 OR d_day=30 AND d_mon=6)";
    					}
    				} elseif ($anniv->d==$anniv->DaysInMonth()) {
    					$where.=" AND d_day>={$anniv->d} AND d_mon=8";
    				} else {
    					$where.=" AND d_day={$anniv->d} AND d_mon=8";
    				}
    			break;
			}
		}
		// Only events in the past (includes dates without a year)
		$where.=" AND d_year<={$anniv->y}";
		// Restrict to certain types of fact
		if (empty($facts)) {
			$excl_facts="'".preg_replace('/\W+/', "','", $skipfacts)."'";
			$where.=" AND d_fact NOT IN ({$excl_facts})";
		} else {
			$incl_facts="'".preg_replace('/\W+/', "','", $facts)."'";
			$where.=" AND d_fact IN ({$incl_facts})";
		}
		// Only get events from the current gedcom
		$where.=" AND d_file=".$ged_id;

		// Now fetch these anniversaries
		$ind_sql="SELECT DISTINCT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec, d_type, d_day, d_month, d_year, d_fact FROM `##dates`, `##individuals` {$where} AND d_gid=i_id AND d_file=i_file ORDER BY d_day ASC, d_year DESC";
		$fam_sql="SELECT DISTINCT 'FAM' AS type, f_id AS xref, f_file AS ged_id, f_gedcom AS gedrec, d_type, d_day, d_month, d_year, d_fact FROM `##dates`, `##families` {$where} AND d_gid=f_id AND d_file=f_file ORDER BY d_day ASC, d_year DESC";
		foreach (array($ind_sql, $fam_sql) as $sql) {
			$rows=KT_DB::prepare($sql)->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rows as $row) {
				if ($row['type']=='INDI') {
					$record=KT_Person::getInstance($row);
				} else {
					$record=KT_Family::getInstance($row);
				}
				if ($record->canDisplayDetails()) {
					// Generate a regex to match the retrieved date - so we can find it in the original gedcom record.
					// TODO having to go back to the original gedcom is lame.  This is why it is so slow.
					// We should store the level1 fact here (or in a "facts" table)
					if ($row['d_type']=='@#DJULIAN@') {
						if ($row['d_year']<0) {
							$year_regex=$row['d_year'].' ?[Bb]\.? ?[Cc]\.\ ?';
						} else {
							$year_regex="({$row['d_year']}|".($row['d_year']-1)."\/".($row['d_year']%100).")";
						}
					} else
						$year_regex="0*".$row['d_year'];
					$ged_date_regex="/2 DATE.*(".($row['d_day']>0 ? "0?{$row['d_day']}\s*" : "").$row['d_month']."\s*".($row['d_year']!=0 ? $year_regex : "").")/i";
					preg_match_all('/\n(1 ('.KT_REGEX_TAG.').*(\n[2-9] .*)*)/', $row['gedrec'], $matches);
					foreach ($matches[1] as $factrec) {
						if (preg_match('/^1 '.$row['d_fact'].'[ \n]/', $factrec) && preg_match($ged_date_regex, $factrec, $match)) {
							$date=new KT_Date($match[1]);
							if (preg_match('/2 PLAC (.+)/', $factrec, $match)) {
								$plac=$match[1];
							} else {
								$plac='';
							}
							if (canDisplayFact($row['xref'], $ged_id, $factrec)) {
								$found_facts[]=array(
									'record'=>$record,
									'id'=>$row['xref'],
									'objtype'=>$row['type'],
									'fact'=>$row['d_fact'],
									'factrec'=>$factrec,
									'jd'=>$jd,
									'anniv'=>($row['d_year']==0?0:$anniv->y-$row['d_year']),
									'date'=>$date,
									'plac'=>$plac
								);
							}
						}
					}
				}
			}
		}
	}
	return $found_facts;
}

////////////////////////////////////////////////////////////////////////////////
// Get a list of events which occured during a given date range.
// TODO: Used by the recent-changes block and the calendar year view.
// $jd1, $jd2 - the range of julian day
// $facts     - restrict the search to just these facts or leave blank for all
// $ged_id    - the id of the gedcom to search
////////////////////////////////////////////////////////////////////////////////
function get_calendar_events($jd1, $jd2, $facts = '', $ged_id = KT_GED_ID) {
	// If no facts specified, get all except these
	$skipfacts = "CHAN,BAPL,SLGC,SLGS,ENDL,CENS,RESI,NOTE,ADDR,OBJE,SOUR,PAGE,DATA,TEXT";
	if ($facts != '_TODO') {
		$skipfacts .= ',_TODO';
	}

	$found_facts = array();

	// This where clause gives events that start/end/overlap the period
	// e.g. 1914-1918 would show up on 1916
	//$where="WHERE d_julianday1 <={$jd2} AND d_julianday2>={$jd1}";
	// This where clause gives only events that start/end during the period
	$where = "WHERE (d_julianday1>={$jd1} AND d_julianday1<={$jd2} OR d_julianday2>={$jd1} AND d_julianday2<={$jd2})";

	// Restrict to certain types of fact
	if (empty($facts)) {
		$excl_facts = "'" . preg_replace('/\W+/', "','", $skipfacts) . "'";
		$where .= " AND d_fact NOT IN ({$excl_facts})";
	} else {
		$incl_facts = "'" . preg_replace('/\W+/', "','", $facts) . "'";
		$where .= " AND d_fact IN ({$incl_facts})";
	}
	// Only get events from the current gedcom
	$where .= " AND d_file=" . $ged_id;

	// Now fetch these events
	// Using "DISTINCT" allows multiple _TODO events in a single INDI or FAM record without duplicating the output.
	$ind_sql = "SELECT DISTINCT d_gid, i_gedcom, 'INDI', d_type, d_day, d_month, d_year, d_fact, d_type FROM `##dates`, `##individuals` {$where} AND d_gid = i_id AND d_file = i_file";
	$fam_sql = "SELECT DISTINCT d_gid, f_gedcom, 'FAM',  d_type, d_day, d_month, d_year, d_fact, d_type FROM `##dates`, `##families`    {$where} AND d_gid = f_id AND d_file = f_file";
	foreach (array($ind_sql, $fam_sql) as $sql) {
		$rows = KT_DB::prepare($sql)->fetchAll(PDO::FETCH_NUM);
		foreach ($rows as $row) {
			// Generate a regex to match the retrieved date - so we can find it in the original gedcom record.
			// TODO having to go back to the original gedcom is inneficient and slow.
			// We should store the level1 fact here (or somewhere)
			if ($row[8] == '@#DJULIAN@') {
				if ($row[6] < 0) {
					$year_regex = $row[6] . ' ?[Bb]\.? ?[Cc]\.\ ?';
				} else {
					$year_regex = "({$row[6]}|" . ($row[6]-1) . "\/" . ($row[6]%100) . ")";
				}
			} else {
				$year_regex = "0*" . $row[6];
			}
			$ged_date_regex = "/2 DATE.*(".($row[4] >
			0 ? "0?{$row[4]}\s*" : "") . $row[5] . "\s*" . ($row[6] != 0 ? $year_regex : "") . ")/i";
			preg_match_all('/\n(1 (' . KT_REGEX_TAG . ').*(\n[2-9] .*)*)/', $row[1], $matches);
			foreach ($matches[1] as $factrec) {
				if (preg_match('/^1 ' . $row[7] . '[ \n]/', $factrec) && preg_match($ged_date_regex, $factrec, $match)) {
					$date = new KT_Date($match[1]);
					if (preg_match('/2 PLAC (.+)/', $factrec, $match)) {
						$plac = $match[1];
					} else {
						$plac = '';
					}
					if (canDisplayFact($row[0], $ged_id, $factrec)) {
						$found_facts[] = array(
							'id'		=> $row[0],
							'objtype'	=> $row[2],
							'fact'		=> $row[7],
							'factrec'	=> $factrec,
							'jd'		=> $jd1,
							'anniv'		=> 0,
							'date'		=> $date,
							'plac'		=> $plac
						);
					}
				}
			}
		}
	}
	return $found_facts;
}

/**
* Get the list of current and upcoming events, sorted by anniversary date
*
* This function is used by the Todays and Upcoming blocks on the Index and Portal
* pages.
*
* Special note on unknown day-of-month:
* When the anniversary date is imprecise, the sort will pretend that the day-of-month
* is either tomorrow or the first day of next month.  These imprecise anniversaries
* will sort to the head of the chosen day.
*
* Special note on Privacy:
* This routine does not check the Privacy of the events in the list.  That check has
* to be done by the routine that makes use of the event list.
*/
function get_events_list($jd1, $jd2, $events='') {
	$found_facts=array();
	for ($jd=$jd1; $jd<=$jd2; ++$jd) {
		$found_facts=array_merge($found_facts, get_anniversary_events($jd, $events));
	}
	return $found_facts;
}

////////////////////////////////////////////////////////////////////////////////
// Check if a media file is shared (i.e. used by another gedcom)
////////////////////////////////////////////////////////////////////////////////
function is_media_used_in_other_gedcom($file_name, $ged_id) {
	return
		(bool)KT_DB::prepare("SELECT COUNT(*) FROM `##media` WHERE m_filename LIKE ? AND m_file<>?")
		->execute(array("%{$file_name}", $ged_id))
		->fetchOne();
}

////////////////////////////////////////////////////////////////////////////////
// Functions to access the ##GEDCOM table
////////////////////////////////////////////////////////////////////////////////

function get_gedcom_from_id($ged_id) {
	// No need to look up the default gedcom
	if (defined('KT_GED_ID') && defined('KT_GEDCOM') && $ged_id==KT_GED_ID) {
		return KT_GEDCOM;
	}

	return
		KT_DB::prepare("SELECT gedcom_name FROM `##gedcom` WHERE gedcom_id=?")
		->execute(array($ged_id))
		->fetchOne();
}

// Convert an (external) gedcom name to an (internal) gedcom ID.
function get_id_from_gedcom($ged_name) {
	// No need to look up the default gedcom
	if (defined('KT_GED_ID') && defined('KT_GEDCOM') && $ged_name==KT_GEDCOM) {
		return KT_GED_ID;
	}

	return
		KT_DB::prepare("SELECT gedcom_id FROM `##gedcom` WHERE gedcom_name=?")
		->execute(array($ged_name))
		->fetchOne();
}

////////////////////////////////////////////////////////////////////////////////
// Functions to access the ##GEDCOM_SETTING table
////////////////////////////////////////////////////////////////////////////////

function get_gedcom_setting($gedcom_id, $setting_name) {
	return KT_Tree::get($gedcom_id)->preference($setting_name);
}

function set_gedcom_setting($gedcom_id, $setting_name, $setting_value) {
	KT_Tree::get($gedcom_id)->preference($setting_name, $setting_value);
}

////////////////////////////////////////////////////////////////////////////////
// Functions to access the ##USER table
////////////////////////////////////////////////////////////////////////////////

function create_user($username, $realname, $email, $password) {
	if (version_compare(PHP_VERSION, '5.3') > 0) {
		// Some PHP5.2 implementations of crypt() appear to be broken - #802316
		// PHP5.3 will always support BLOWFISH - see php.net/crypt
		// This salt will select the BLOWFISH algorithm with 2^12 rounds
		$salt		= '$2a$12$';
		$salt_chars	= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789./';
		for ($i = 0; $i < 22; ++$i) {
			$salt .= substr($salt_chars, mt_rand(0,63), 1);
		}
		$password_hash = crypt($password, $salt);
	} else {
		// Our prefered hash algorithm is not available.  Use the default.
		$password_hash = crypt($password);
	}
	try {
		KT_DB::prepare("INSERT INTO `##user` (user_name, real_name, email, password) VALUES (?, ?, ?, ?)")
			->execute(array($username, $realname, $email, $password_hash));
		$user_id = KT_DB::getInstance()->lastInsertId();
	} catch (PDOException $ex) {
		// User already exists?
	}
	$user_id =
		KT_DB::prepare("SELECT user_id FROM `##user` WHERE user_name=?")
		->execute(array($username))->fetchOne();
	return $user_id;
}

function rename_user($user_id, $new_username) {
	KT_DB::prepare("UPDATE `##user` SET user_name=?   WHERE user_id  =?")->execute(array($new_username, $user_id));
}

function delete_user($user_id) {
	$exists_pending = false;
	// Don't delete the logs, set user id to NULL instead.
	KT_DB::prepare("UPDATE `##log` SET user_id=NULL   WHERE user_id =?")->execute(array($user_id));

	// Prevent deletion of users with pending changes.
	$exists_pending = KT_DB::prepare("SELECT 1 FROM `##change` WHERE user_id=? AND status='pending'")->execute(array($user_id))->fetchOne();
	if (!$exists_pending) {
		KT_DB::prepare("DELETE FROM `##change`WHERE user_id=? AND status='rejected'")->execute(array($user_id));
		KT_DB::prepare("DELETE `##block_setting` FROM `##block_setting` JOIN `##block` USING (block_id) WHERE user_id=?")->execute(array($user_id));
		KT_DB::prepare("DELETE FROM `##block` WHERE user_id=?")->execute(array($user_id));
		KT_DB::prepare("DELETE FROM `##user_gedcom_setting` WHERE user_id=?")->execute(array($user_id));
		KT_DB::prepare("DELETE FROM `##gedcom_setting` WHERE setting_value=? AND setting_name IN ('CONTACT_USER_ID', 'WEBMASTER_USER_ID')")->execute(array((string) $user_id));
		KT_DB::prepare("DELETE FROM `##user_setting` WHERE user_id=?")->execute(array($user_id));
		table_exists("##message") ? KT_DB::prepare("DELETE FROM `##message` WHERE user_id=?")->execute(array($user_id)) : '';
		KT_DB::prepare("DELETE FROM `##user` WHERE user_id=?")->execute(array($user_id));
	} else {
		KT_FlashMessages::addMessage(KT_I18N::translate('<span class="error">Unable to delete user. This user has pending data changes.</span>'));
	}

}

function get_all_users($order='ASC', $key='realname') {
	if ($key=='username') {
		return
			KT_DB::prepare("SELECT user_id, user_name FROM `##user` WHERE user_id>0 ORDER BY user_name")
			->fetchAssoc();
	} elseif ($key=='realname') {
		return
			KT_DB::prepare("SELECT user_id, user_name FROM `##user` WHERE user_id>0 ORDER BY real_name")
			->fetchAssoc();
	} else {
		return
			KT_DB::prepare(
				"SELECT u.user_id, user_name".
				" FROM `##user` u".
				" LEFT JOIN `##user_setting` us1 ON (u.user_id=us1.user_id AND us1.setting_name=?)".
				" WHERE u.user_id>0".
				" ORDER BY us1.setting_value {$order}"
			)->execute(array($key))
			->fetchAssoc();
	}
}

function get_user_count() {
	return KT_DB::prepare("SELECT COUNT(*) FROM `##user` WHERE user_id>0")->fetchOne();
}

/**
 * Find the user with a specified user_id.
 *
 * @param int|null $user_id
 *
 * @return User|null
 */
function find($user_id) {
	return KT_DB::prepare("SELECT user_id, user_name, real_name, email FROM `##user` WHERE user_id = ?")->execute([$user_id])->fetchOneRow();
}

/**
 * Find the user with a specified user_name.
 *
 * @param string $user_name
 *
 * @return User|null
 */
function findByUserName($user_name) {
	$user_id = KT_DB::prepare(
		"SELECT user_id FROM `##user` WHERE user_name = ?"
	)->execute([$user_name])->fetchOne();

	return find($user_id);
}

/**
 * Find the user with a specified email address.
 *
 * @param string $email
 *
 * @return User|null
 */
function findByEmail($email) {
	return KT_DB::prepare("SELECT user_id FROM `##user` WHERE email=?")->execute(array($email))->fetchOne();
}

function get_admin_user_count() {
	return
		KT_DB::prepare("SELECT COUNT(*) FROM `##user_setting` WHERE setting_name=? AND setting_value=? AND user_id>0")
		->execute(array('canadmin', '1'))
		->fetchOne();
}

function get_non_admin_user_count() {
	return
		KT_DB::prepare("SELECT COUNT(*) FROM `##user_setting` WHERE  setting_name=? AND setting_value<>? AND user_id>0")
		->execute(array('canadmin', '1'))
		->fetchOne();
}

// Get a list of logged-in users
function get_logged_in_users() {
	// If the user is logged in on multiple times, this query would fetch
	// multiple rows.  fetchAssoc() will eliminate the duplicates
	return
		KT_DB::prepare(
			"SELECT user_id, user_name".
			" FROM `##user` u".
			" JOIN `##session` USING (user_id)"
		)
		->fetchAssoc();
}

// Get the ID for a username
function get_user_id($username) {
	return KT_DB::prepare("SELECT user_id FROM `##user` WHERE user_name=?")
		->execute(array($username))
		->fetchOne();
}

// Get the username for a user ID
function get_user_name($user_id) {
	return KT_DB::prepare("SELECT user_name FROM `##user` WHERE user_id=?")
		->execute(array($user_id))
		->fetchOne();
}

function get_newest_registered_user() {
	return KT_DB::prepare(
		"SELECT u.user_id".
		" FROM `##user` u".
		" LEFT JOIN `##user_setting` us ON (u.user_id=us.user_id AND us.setting_name=?) ".
		" ORDER BY us.setting_value DESC LIMIT 1"
	)->execute(array('reg_timestamp'))
		->fetchOne();
}

function set_user_password($user_id, $password) {
	if (version_compare(PHP_VERSION, '5.3')>0) {
		// Some PHP5.2 implementations of crypt() appear to be broken - #802316
		// PHP5.3 will always support BLOWFISH - see php.net/crypt
		// This salt will select the BLOWFISH algorithm with 2^12 rounds
		$salt='$2a$12$';
		$salt_chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789./';
		for ($i=0;$i<22;++$i) {
			$salt.=substr($salt_chars, mt_rand(0,63), 1);
		}
		$password_hash=crypt($password, $salt);
	} else {
		// Our prefered hash algorithm is not available.  Use the default.
		$password_hash=crypt($password);
	}
	KT_DB::prepare("UPDATE `##user` SET password=? WHERE user_id=?")
		->execute(array($password_hash, $user_id));
	AddToLog('User ID: '.$user_id. ' ('.get_user_name($user_id).') changed password', 'auth');
}

function check_user_password($user_id, $password) {
	// crypt() needs the password-hash to use as a salt
	$password_hash=
		KT_DB::prepare("SELECT password FROM `##user` WHERE user_id=?")
		->execute(array($user_id))
		->fetchOne();
	if (crypt($password, $password_hash)==$password_hash) {
		// Update older passwords to use BLOWFISH with 2^12 rounds
		if (version_compare(PHP_VERSION, '5.3')>0 && substr($password_hash, 0, 7)!='$2a$12$') {
			set_user_password($user_id, $password);
		}
		return true;
	} else {
		return false;
	}
}
////////////////////////////////////////////////////////////////////////////////
// Functions to access the ##USER_SETTING table
////////////////////////////////////////////////////////////////////////////////

function get_user_setting($user_id, $setting_name, $default_value=null) {
	static $statement = null;
	if ($statement===null) {
		$statement = KT_DB::prepare(
			"SELECT setting_value FROM `##user_setting` WHERE user_id=? AND setting_name=?"
		);
	}
	$setting_value=$statement->execute(array($user_id, $setting_name))->fetchOne();
	return $setting_value===null ? $default_value : $setting_value;
}

function set_user_setting($user_id, $setting_name, $setting_value) {
	if ($setting_value===null) {
		KT_DB::prepare("DELETE FROM `##user_setting` WHERE user_id=? AND setting_name=?")
			->execute(array($user_id, $setting_name));
	} else {
		KT_DB::prepare("REPLACE INTO `##user_setting` (user_id, setting_name, setting_value) VALUES (?, ?, LEFT(?, 255))")
			->execute(array($user_id, $setting_name, $setting_value));
	}
}

function admin_user_exists() {
	return get_admin_user_count()>0;
}

////////////////////////////////////////////////////////////////////////////////
// Functions to access the ##USER_GEDCOM_SETTING table
////////////////////////////////////////////////////////////////////////////////

function get_user_from_gedcom_xref($ged_id, $xref) {
	return
		KT_DB::prepare(
			"SELECT user_id FROM `##user_gedcom_setting`".
			" WHERE gedcom_id=? AND setting_name=? AND setting_value=?"
		)->execute(array($ged_id, 'gedcomid', $xref))->fetchOne();
}

function get_gedcomid($user_id, $gedcom_id) {
	return
		KT_DB::prepare(
			"SELECT setting_value FROM `##user_gedcom_setting`".
			" WHERE user_id=? AND gedcom_id=? AND setting_name=?"
		)->execute(array($user_id, $gedcom_id, 'gedcomid'))->fetchOne();
}

function get_admin_id($gedcom_id) {
	return
		KT_DB::prepare(
			"SELECT user_id FROM `##user_gedcom_setting` WHERE gedcom_id=? AND setting_value='admin'"
		)->execute(array($gedcom_id))->fetchOne();
}

////////////////////////////////////////////////////////////////////////////////
// Functions to access the ##BLOCK table
////////////////////////////////////////////////////////////////////////////////

// NOTE - this function is only correct when $gedcom_id==KT_GED_ID
// since the privacy depends on KT_USER_ACCESS_LEVEL, which depends
// on KT_GED_ID
function get_gedcom_blocks($gedcom_id) {
	$blocks	= array('main' => array(), 'side' => array());
	$rows	= KT_DB::prepare("
		SELECT location, block_id, module_name
		 FROM  `##block`
		 JOIN  `##module` USING (module_name)
		 JOIN  `##module_privacy` USING (module_name, gedcom_id)
		 WHERE gedcom_id=?
		 AND   status='enabled'
		 AND   access_level>=?
		 ORDER BY location, block_order"
	)->execute(array($gedcom_id, KT_USER_ACCESS_LEVEL))->fetchAll();
	foreach ($rows as $row) {
		$blocks[$row->location][$row->block_id] = $row->module_name;
	}
	return $blocks;
}

function get_block_location($block_id) {
	return KT_DB::prepare(
		"SELECT location FROM `##block` WHERE block_id=?"
	)->execute(array($block_id))->fetchOne();;
}

function get_block_order($block_id) {
	return KT_DB::prepare(
		"SELECT block_order FROM `##block` WHERE block_id=?"
	)->execute(array($block_id))->fetchOne();;
}

function get_block_setting($block_id, $setting_name, $default_value = null) {
	static $statement;
	if ($statement === null) {
		$statement = KT_DB::prepare(
			"SELECT setting_value FROM `##block_setting` WHERE block_id=? AND setting_name=?"
		);
	}
	$setting_value = $statement->execute(array($block_id, $setting_name))->fetchOne();
	return $setting_value === null ? $default_value : $setting_value;
}

function set_block_setting($block_id, $setting_name, $setting_value) {
	if ($setting_value === null) {
		KT_DB::prepare("DELETE FROM `##block_setting` WHERE block_id=? AND setting_name=?")
			->execute(array($block_id, $setting_name));
	} else {
		KT_DB::prepare("REPLACE INTO `##block_setting` (block_id, setting_name, setting_value) VALUES (?, ?, ?)")
			->execute(array($block_id, $setting_name, $setting_value));
	}
}

function get_module_setting($module_name, $setting_name, $default_value=null) {
	static $statement;
	if ($statement === null) {
		$statement = KT_DB::prepare(
			"SELECT setting_value FROM `##module_setting` WHERE module_name=? AND setting_name=?"
		);
	}
	$setting_value = $statement->execute(array($module_name, $setting_name))->fetchOne();
	return $setting_value === null ? $default_value : $setting_value;
}

function set_module_setting($module_name, $setting_name, $setting_value) {
	if ($setting_value === null) {
		KT_DB::prepare("DELETE FROM `##module_setting` WHERE module_name=? AND setting_name=?")
			->execute(array($module_name, $setting_name));
	} else {
		KT_DB::prepare("REPLACE INTO `##module_setting` (module_name, setting_name, setting_value) VALUES (?, ?, ?)")
			->execute(array($module_name, $setting_name, $setting_value));
	}
}

// update favorites after merging records
function update_favorites($xref_from, $xref_to, $ged_id=KT_GED_ID) {
	return
		KT_DB::prepare("UPDATE `##favorite` SET xref=? WHERE xref=? AND gedcom_id=?")
		->execute(array($xref_to, $xref_from, $ged_id))
		->rowCount();
}

//=================================
// server space functions
//=================================
function db_size () {
	$sql  = 'SHOW TABLE STATUS';
	$size = 0;
	$rows = KT_DB::prepare($sql)->fetchAll(PDO::FETCH_ASSOC);

	foreach ($rows as $row) {
		$size += $row['data_length'] + $row['index_length'];
	}

	return $size;
}

function directory_size() {
    $size = 0;

    foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(KT_ROOT)) as $file){
        $size += $file->getSize();
    }

	return $size;
}

function format_size($size) {
	switch ($size) {
		case $size < 1024:
			return KT_I18N::number($size) . ' Bytes';
			break;
		case $size < 1024000:
			return KT_I18N::number(($size / 1024 ), 0) . 'KB';
			break;
		case $size < 1024000000:
			return KT_I18N::number(($size / 1024000), 1) . ' MB';
			break;
		default:
			return KT_I18N::number(($size / 1024000000), 2) . ' GB';
			break;
	}
}

function table_exists ($table) {
    $sql  = 'SELECT * FROM `##placelocation`';
    try {
        $rows = KT_DB::prepare($sql)->fetchAll(PDO::FETCH_ASSOC);
        return true;
    } catch (PDOException $ex) {
    	return false;
    }
}
