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

define('KT_SCRIPT_NAME', 'autocomplete.php');
require './includes/session.php';

header('Content-Type: text/plain; charset=UTF-8');

// We have finished writing session data, so release the lock
Zend_Session::writeClose();

$term = KT_Filter::get('term'); // we can search on '"><& etc.
$type = KT_Filter::get('field');

switch ($type) {
	case 'ASSO': // Associates of an individuals, whose name contains the search terms
		$data = array();
		// Fetch all data, regardless of privacy
		$rows =
			KT_DB::prepare("
				SELECT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec, n_full
				 FROM `##individuals`
				 JOIN `##name` ON (i_id=n_id AND i_file=n_file)
				 WHERE (n_full LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%') OR n_surn LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%')) AND i_file=? ORDER BY n_full COLLATE '" . KT_I18N::$collation . "'
			")
			->execute(array($term, $term, KT_GED_ID))
			->fetchAll(PDO::FETCH_ASSOC);

		// Filter for privacy - and whether they could be alive at the right time
		$event_date = KT_Filter::get('extra');
		$date       = new KT_Date($event_date);
		$event_jd   = $date->JD();

		foreach ($rows as $row) {
			$person = KT_Person::getInstance($row);
			if ($person->canDisplayName()) {
				// filter ASSOciate
				if ($event_jd) {
					// Exclude individuals who were born after the event.
					$person_birth_jd = $person->getEstimatedBirthDate()->minJD();
					if ($person_birth_jd && $person_birth_jd > $event_jd) {
						continue;
					}
					// Exclude individuals who died before the event.
					$person_death_jd = $person->getEstimatedDeathDate()->MaxJD();
					if ($person_death_jd && $person_death_jd < $event_jd) {
						continue;
					}
				}
				// Display, with age or lifespan if possible
				$label = $person->getFullName();
				if ($event_jd && $person->getBirthDate()->isOK()) {
					$label .= ', <span class="age">(' . I18N::translate('Age') . ' ' . $person->getBirthDate()->minimumDate()->getAge(false, $event_jd) . ')</span>';
				} else {
					$label .= ', <i>' . $person->getLifeSpan() . '</i>';
				}
				$data[] = array('value'=>$row['xref'], 'label'=>$label);
			}
		}

		echo json_encode($data);

	exit;

	case 'CAUS': // Cause of death.
		$data = array();
		// Fetch all data, regardless of privacy
		$rows =
			KT_DB::prepare("
				SELECT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec, n_full
				 FROM `##individuals`
				 JOIN `##name` ON (i_id=n_id AND i_file=n_file)
				 WHERE (n_full LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%') OR n_surn LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%')) AND i_file=? ORDER BY n_full COLLATE '" . KT_I18N::$collation . "'
			")
			->execute(array($term, $term, KT_GED_ID))
			->fetchAll(PDO::FETCH_ASSOC);

		// Filter for privacy
		foreach ($rows as $row) {
			$person = KT_Person::getInstance($row);
			if ($person->canDisplayName()) {
				if (preg_match('/\n2 CAUS (.*'.preg_quote((string) $term, '/').'.*)/i', (string) $person->getGedcomRecord(), $match)) {
					if (!in_array($match[1], $data)) {
						$data[] = $match[1];
					}
				}
			}
		}

		echo json_encode($data);

	exit;

	case 'CEME': // Cemetery fields, that contain the search term
		$data = array();
		// Fetch all data, regardless of privacy
		$rows =
			KT_DB::prepare("
				SELECT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec
				 FROM `##individuals`
				 WHERE i_gedcom LIKE '%\n2 CEME %' AND i_file=?
				 ORDER BY SUBSTRING_INDEX(i_gedcom, '\n2 CEME ', -1) COLLATE '" . KT_I18N::$collation . "'
			")
			->execute(array(KT_GED_ID))
			->fetchAll(PDO::FETCH_ASSOC);

		// Filter for privacy
		foreach ($rows as $row) {
			$person = KT_Person::getInstance($row);
			if ($person->canDisplayName()) {
				if (preg_match('/\n2 CEME (.*'.preg_quote((string) $term, '/').'.*)/i', (string) $person->getGedcomRecord(), $match)) {
					if (!in_array($match[1], $data)) {
						$data[] = $match[1];
					}
				}
			}
		}

		echo json_encode($data);

	exit;

	case 'EVEN_TYPE': // Event types
		$data = array();
		// Fetch all data, regardless of privacy
		$rows=
			KT_DB::prepare("
				SELECT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec
				 FROM `##individuals`
				 WHERE i_gedcom REGEXP '(.*)\n1 EVEN.*\n2 TYPE ([^\n]*)" . $term . "*[^\n]*' AND i_file=?
				 ORDER BY SUBSTRING_INDEX(i_gedcom, '\n2 TYPE ', -1) COLLATE '" . KT_I18N::$collation . "'
			")
			->execute(array(KT_GED_ID))
			->fetchAll(PDO::FETCH_ASSOC);

		// Filter for privacy
		foreach ($rows as $row) {
			$person = KT_Person::getInstance($row);
			if ($person->canDisplayName()) {
				if (preg_match('/\n2 TYPE (.*'.preg_quote((string) $term, '/').'.*)/i', (string) $person->getGedcomRecord(), $match)) {
					if (!in_array($match[1], $data)) {
						$data[] = $match[1];
					}
				}
			}
		}

		echo json_encode($data);

	exit;

	case 'FACT_TYPE': // Fact types
		$data = array();
		// Fetch all data, regardless of privacy
		$rows=
			KT_DB::prepare("
				SELECT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec
				 FROM `##individuals`
				 WHERE i_gedcom REGEXP '(.*)\n1 FACT.*\n2 TYPE ([^\n]*)" . $term . "*[^\n]*' AND i_file=?
				 ORDER BY SUBSTRING_INDEX(i_gedcom, '\n2 TYPE ', -1) COLLATE '" . KT_I18N::$collation . "'
			")
			->execute(array(KT_GED_ID))
			->fetchAll(PDO::FETCH_ASSOC);

		// Filter for privacy
		foreach ($rows as $row) {
			$person = KT_Person::getInstance($row);
			if ($person->canDisplayName()) {
			if (preg_match('/\n2 TYPE (.*'.preg_quote((string) $term, '/').'.*)/i', (string) $person->getGedcomRecord(), $match)) {
					if (!in_array($match[1], $data)) {
						$data[] = $match[1];
					}
				}
			}
		}

		echo json_encode($data);

	exit;

	case 'EF_TYPE': // Event OR Fact types
		$data = array();
		// Fetch all data, regardless of privacy
		$rows=
			KT_DB::prepare("
				SELECT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec
				 FROM `##individuals`
				 WHERE i_gedcom REGEXP '(.*)\n1 (FACT|EVEN).*\n2 TYPE ([^\n]*)" . $term . "*[^\n]*' AND i_file=?
				 ORDER BY SUBSTRING_INDEX(i_gedcom, '\n2 TYPE ', -1) COLLATE '" . KT_I18N::$collation . "'
			")
			->execute(array(KT_GED_ID))
			->fetchAll(PDO::FETCH_ASSOC);

		// Filter for privacy
		foreach ($rows as $row) {
			$person = KT_Person::getInstance($row);
			if ($person->canDisplayName()) {
				if (preg_match('/\n2 TYPE (.*'.preg_quote((string) $term, '/').'.*)/i', (string) $person->getGedcomRecord(), $match)) {
					if (!in_array($match[1], $data)) {
						$data[] = $match[1];
					}
				}
			}
		}

		echo json_encode($data);

	exit;

	case 'FAM': // Families, whose name contains the search terms
		$data = array();
		// Fetch all data, regardless of privacy
		$rows = get_FAM_rows($term);
		// Filter for privacy
		foreach ($rows as $row) {
			$family = KT_Family::getInstance($row);
			if ($family->canDisplayName()) {
				$marriage_year = $family->getMarriageYear();
				if ($marriage_year) {
					$data[] = array('value'=>$family->getXref(), 'label'=>$family->getFullName().', <i>'.$marriage_year.'</i>');
				} else {
					$data[] = array('value'=>$family->getXref(), 'label'=>$family->getFullName());
				}
			}
		}
		echo json_encode($data);
	exit;

	case 'GIVN': // Given names, that start with the search term
		// Do not filter by privacy.  Given names on their own do not identify individuals.
		echo json_encode(
			KT_DB::prepare("
				SELECT DISTINCT n_givn
				 FROM `##name`
				 WHERE n_givn LIKE CONCAT(?, '%') AND n_file=?
				 ORDER BY n_givn COLLATE '" . KT_I18N::$collation . "'
			")
			->execute(array($term, KT_GED_ID))
			->fetchOneColumn()
		);

	exit;

	case 'NAME': // Any type of names, that include the search term
		$names = array();
		// select by surname
		$rows1 =
			KT_DB::prepare("
				SELECT DISTINCT n_surn AS name, n_id AS xref
				 FROM `##name`
				 WHERE n_surn LIKE CONCAT('%', ?, '%')
				 AND n_type= 'NAME'
				 AND n_file=?
				 ORDER BY n_full COLLATE '" . KT_I18N::$collation . "'
			")
			->execute(array($term, KT_GED_ID))
			->fetchAll(PDO::FETCH_ASSOC);

		// select by given names
		$rows2 =
			KT_DB::prepare("
				SELECT DISTINCT n_id AS xref, n_givn AS name
				 FROM `##name`
				 WHERE n_givn LIKE CONCAT('%', ?, '%')
				 AND n_type= 'NAME'
				 AND n_file=?
				 ORDER BY n_full COLLATE '" . KT_I18N::$collation . "'
			")
			->execute(array($term, KT_GED_ID))
			->fetchAll(PDO::FETCH_ASSOC);

		// combine surnames and given names in a single list
		$rows = array_merge($rows1, $rows2);

		// Filter for privacy
		foreach ($rows as $row) {
			$person = KT_Person::getInstance($row['xref']);
			if ($person->canDisplayName()) {
				$names[] = $row['name'];
			}
		}

		//remove duplicate results
		// array_unique() converts the keys from integer to string, which breaks
		// the JSON encoding - so need to call array_values() to convert them
		// back into integers.
		$data = array_values(array_unique($names));

		echo json_encode($data);

	exit;

	case 'INDI': // Individuals, whose name contains the search terms
		$data = array();
		// Fetch all data, regardless of privacy
		$rows =
			KT_DB::prepare("
				SELECT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec, n_full
				 FROM `##individuals`
				 JOIN `##name` ON (i_id=n_id AND i_file=n_file)
				 WHERE (n_full LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%') OR n_surn LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%')) AND i_file=? ORDER BY n_full COLLATE '" . KT_I18N::$collation . "'
			")
			->execute(array($term, $term, KT_GED_ID))
			->fetchAll(PDO::FETCH_ASSOC);

		// Filter for privacy
		foreach ($rows as $row) {
			$person = KT_Person::getInstance($row);
			if ($person->canDisplayName()) {
				$data[] = array('value'=>$row['xref'], 'label'=>str_replace(array('@N.N.', '@P.N.'), array($UNKNOWN_NN, $UNKNOWN_PN), $row['n_full']).', <i>'.$person->getLifeSpan().'</i>');
			}
		}

		echo json_encode($data);

	exit;

	case 'NOTE': // Notes which contain the search terms
		$data = array();
		// Fetch all data, regardless of privacy
		$rows = get_NOTE_rows($term);
		// Filter for privacy
		foreach ($rows as $row) {
			$note = KT_Note::getInstance($row);
			if ($note->canDisplayName()) {
				$data[] = array('value'=>$row['xref'], 'label'=>$note->getFullName());
			}
		}

		echo json_encode($data);

	exit;

	case 'SPFX': // Name prefixes, that start with the search term
	case 'NPFX':
	case 'NSFX':
		// Do not filter by privacy.  Surnames on their own do not identify individuals.
		echo json_encode(
			KT_DB::prepare("
				SELECT DISTINCT SUBSTRING_INDEX(SUBSTRING_INDEX(i_gedcom, CONCAT('\n2 ', ?, ' '), -1), '\n', 1)
				 FROM `##individuals`
				 WHERE i_gedcom LIKE CONCAT('%\n2 ', ?, ' ', ?, '%') AND i_file=?
				 ORDER BY 1
			")
			->execute(array($type, $type, $term, KT_GED_ID))
			->fetchOneColumn()
		);

	exit;

	case 'OBJE':
		$data = array();
		// Fetch all data, regardless of privacy
		$rows = get_OBJE_rows($term);
		// Filter for privacy
		foreach ($rows as $row) {
			$media = KT_Media::getInstance($row);
			if ($media->canDisplayName()) {
				$data[] = array('value'=>$row['xref'], 'label'=>'<img src="' . $media->getHtmlUrlDirect().'" width="25"> ' . $media->getFullName());
			}
		}

		echo json_encode($data);

	exit;

	case 'OCCU': // Occupation fields, that contain the search term
		$data = array();
		// Fetch all data, regardless of privacy
		$rows =
			KT_DB::prepare("
				SELECT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec
				 FROM `##individuals`
				 WHERE i_gedcom LIKE '%\n1 OCCU %' AND i_file=?
				 ORDER BY SUBSTRING_INDEX(i_gedcom, '\n1 OCCU ', -1) COLLATE '" . KT_I18N::$collation . "'
			")
			->execute(array(KT_GED_ID))
			->fetchAll(PDO::FETCH_ASSOC);

		// Filter for privacy
		foreach ($rows as $row) {
			$person = KT_Person::getInstance($row);
			if (preg_match('/\n1 OCCU (.*'.preg_quote((string) $term, '/').'.*)/i', (string) $person->getGedcomRecord(), $match)) {
				if (!in_array($match[1], $data)) {
					$data[] = $match[1];
				}
			}
		}

		echo json_encode($data);

	exit;

	case 'PLAC': // Place names (with hierarchy), that include the search term
		// Do not filter by privacy.  Place names on their own do not identify individuals.
		$data = array();

        switch(get_gedcom_setting(KT_GED_ID, 'AUTOCOMPLETE_PLACES')) {
            case 'basic':
                foreach (KT_Place::findPlacesInitial($term, KT_GED_ID) as $place) {
                    $data[] = $place->getGedcomName();
                }
            break;
            case 'advanced':
            default:
                $newPlace = false;

        		if (strpos($term, ', ')) {
        			$places	= preg_split('/,\s/', $term);
        			$term = array_pop($places);
        			$newPlace = implode(', ', $places);
        		}

        		foreach (KT_Place::findPlacesInitial($term, KT_GED_ID) as $place) {
        			if ($newPlace) {
        				$newPlace = ucwords(strtolower($newPlace), ". \t\r\n\f\v");
        				$data[] = $newPlace . ', ' . $place->getGedcomName();
        			} else {
        				$data[] = $place->getGedcomName();
        			}
        		}
            break;
        }

        if (!$data && get_gedcom_setting(KT_GED_ID, 'USE_GEONAMES')) {
			// No place found?  Use an external gazetteer
			$url =
                "http://api.geonames.org/searchJSON" .
                "?name_startsWith=" . urlencode($term) .
                "&lang=" . substr(KT_LOCALE, 0, 2) .
                "&fCode=CMTY&fCode=ADM4&fCode=PPL&fCode=PPLA&fCode=PPLC" .
                "&style=FULL" .
                "&maxRows=10" .
                "&username=kiwiwebtrees";
			// password qwertyuiop

			// try to use curl when file_get_contents not allowed
			if (ini_get('allow_url_fopen')) {
				$json = file_get_contents($url);
			} elseif (function_exists('curl_init')) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$json = curl_exec($ch);
				curl_close($ch);
			} else {
				return $data;
			}

			$places = json_decode($json, true);

			if (isset($places['geonames']) && is_array($places['geonames'])) {
				foreach ($places["geonames"] as $k => $place) {
					$data[]	=	$place["name"].", ".
								$place["adminName2"].", ".
								$place["adminName1"].", ".
								$place["countryName"];
				}
			}
		}

		echo json_encode($data);

	exit;

	case 'PLAC2': // Place names (without hierarchy), that include the search term
		// Do not filter by privacy.  Place names on their own do not identify individuals.
		echo json_encode(
			KT_DB::prepare("
				SELECT p_place
				 FROM `##places`
				 WHERE p_place LIKE CONCAT('%', ?, '%') AND p_file=?
				 ORDER BY p_place COLLATE '" . KT_I18N::$collation . "'
			")
			->execute(array($term, KT_GED_ID))
			->fetchOneColumn()
		);

	exit;

	case 'REPO': // Repositories, that include the search terms
		$data = array();
		// Fetch all data, regardless of privacy
		$rows = get_REPO_rows($term);

		// Filter for privacy
		foreach ($rows as $row) {
			$repository = KT_Repository::getInstance($row);
			if ($repository->canDisplayName()) {
				$data[] = array('value'=>$row['xref'], 'label'=>$row['n_full']);
			}
		}

		echo json_encode($data);

	exit;

	case 'REPO_NAME': // Repository names, that include the search terms
		$data = array();

		// Fetch all data, regardless of privacy
		$rows = get_REPO_rows($term);

		// Filter for privacy
		foreach ($rows as $row) {
			$repository = KT_Repository::getInstance($row);
			if ($repository->canDisplayName()) {
				$data[] = $row['n_full'];
			}
		}

		echo json_encode($data);

	exit;

	case 'SOUR': // Sources, that include the search terms
		$data = array();

		// Fetch all data, regardless of privacy
		$rows = get_SOUR_rows($term);

		// Filter for privacy
		foreach ($rows as $row) {
			$source = KT_Source::getInstance($row);
			if ($source->canDisplayName()) {
				$data[] = array('value'=>$row['xref'], 'label'=>$row['n_full']);
			}
		}

		echo json_encode($data);

	exit;

	case 'SOUR_PAGE': // Citation details, for a given source, that contain the search term, in INDI, FAM and OBJE records
		$data = array();
		$sid  = KT_Filter::get('extra', KT_REGEX_XREF);

		// Fetch all data, regardless of privacy
		$rows =
			KT_DB::prepare("
				SELECT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec
				 FROM `##individuals`
				 WHERE i_gedcom LIKE CONCAT('%\n_ SOUR @', ?, '@%', REPLACE(?, ' ', '%'), '%') AND i_file=?
			")
			->execute(array($sid, $term, KT_GED_ID))
			->fetchAll(PDO::FETCH_ASSOC);

		// Filter for privacy
		foreach ($rows as $row) {
			$person = KT_Person::getInstance($row);
			if (preg_match('/\n1 SOUR @'.$sid.'@(?:\n[2-9].*)*\n2 PAGE (.*'.str_replace(' ', '.+', preg_quote((string) $term, '/')).'.*)/i', (string) $person->getGedcomRecord(), $match)) {
				$data[] = $match[1];
			}
			if (preg_match('/\n2 SOUR @'.$sid.'@(?:\n[3-9].*)*\n3 PAGE (.*'.str_replace(' ', '.+', preg_quote((string) $term, '/')).'.*)/i', (string) $person->getGedcomRecord(), $match)) {
				$data[] = $match[1];
			}
		}

		// Fetch all data, regardless of privacy
		$rows=
			KT_DB::prepare("
				SELECT 'FAM' AS type, f_id AS xref, f_file AS ged_id, f_gedcom AS gedrec
				 FROM `##families`
				 WHERE f_gedcom LIKE CONCAT('%\n_ SOUR @', ?, '@%', REPLACE(?, ' ', '%'), '%') AND f_file=?
			")
			->execute(array($sid, $term, KT_GED_ID))
			->fetchAll(PDO::FETCH_ASSOC);

		// Filter for privacy
		foreach ($rows as $row) {
			$family = KT_Family::getInstance($row);
			if (preg_match('/\n1 SOUR @'.$sid.'@(?:\n[2-9].*)*\n2 PAGE (.*'.str_replace(' ', '.+', preg_quote((string) $term, '/')).'.*)/i', (string) $family->getGedcomRecord(), $match)) {
				$data[] = $match[1];
			}
			if (preg_match('/\n2 SOUR @'.$sid.'@(?:\n[3-9].*)*\n3 PAGE (.*'.str_replace(' ', '.+', preg_quote((string) $term, '/')).'.*)/i', (string) $family->getGedcomRecord(), $match)) {
				$data[] = $match[1];
			}
		}

        // Fetch all data, regardless of privacy
		$rows=
			KT_DB::prepare("
				SELECT 'OBJE' AS type, m_id AS xref, m_file AS ged_id, m_gedcom AS gedrec
				 FROM `##media`
				 WHERE m_gedcom LIKE CONCAT('%\n_ SOUR @', ?, '@%', REPLACE(?, ' ', '%'), '%') AND m_file=?
			")
			->execute(array($sid, $term, KT_GED_ID))
			->fetchAll(PDO::FETCH_ASSOC);

		// Filter for privacy
		foreach ($rows as $row) {
			if (preg_match('/\n1 SOUR @'.$sid.'@(?:\n[2-9].*)*\n2 PAGE (.*'.str_replace(' ', '.+', preg_quote((string) $term, '/')).'.*)/i', (string) $row['gedrec'], $match)) {
				$data[] = $match[1];
			}
			if (preg_match('/\n2 SOUR @'.$sid.'@(?:\n[3-9].*)*\n3 PAGE (.*'.str_replace(' ', '.+', preg_quote((string) $term, '/')).'.*)/i', $row['gedrec'], $match)) {
				$data[] = $match[0];
			}
		}

		// array_unique() converts the keys from integer to string, which breaks
		// the JSON encoding - so need to call array_values() to convert them
		// back into integers.
		$data = array_values(array_unique($data));

		echo json_encode($data);

	exit;

	case 'SOUR_TITL': // Source titles, that include the search terms
		$data = array();

		// Fetch all data, regardless of privacy
		$rows =
			KT_DB::prepare("
				SELECT 'SOUR' AS type, s_id AS xref, s_file AS ged_id, s_gedcom AS gedrec, s_name
				 FROM `##sources`
				 WHERE s_name LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%') AND s_file=? ORDER BY s_name COLLATE '" . KT_I18N::$collation . "'
			")
			->execute(array($term, KT_GED_ID))
			->fetchAll(PDO::FETCH_ASSOC);

		// Filter for privacy
		foreach ($rows as $row) {
			$source = KT_Source::getInstance($row);
			if ($source->canDisplayName()) {
				$data[] = $row['s_name'];
			}
		}

		echo json_encode($data);

	exit;

	case 'SURN': // Surnames, that start with the search term
		// Do not filter by privacy.  Surnames on their own do not identify individuals.
		echo json_encode(
			KT_DB::prepare("
				SELECT DISTINCT n_surname
				 FROM `##name`
				 WHERE n_surname LIKE CONCAT(?, '%') AND n_file=?
				 ORDER BY n_surname COLLATE '" . KT_I18N::$collation . "'
			")
			->execute(array($term, KT_GED_ID))
			->fetchOneColumn()
		);

	exit;

	case 'IFSRO':
		$data = array();

		// Fetch all data, regardless of privacy
		$rows = get_INDI_rows($term);

		// Filter for privacy
		foreach ($rows as $row) {
			$person = KT_Person::getInstance($row);
			if ($person->canDisplayName()) {
				$data[] = array('value'=>$row['xref'], 'label'=>str_replace(array('@N.N.', '@P.N.'), array($UNKNOWN_NN, $UNKNOWN_PN), $row['n_full']).', <i>'.$person->getLifeSpan().'</i>');
			}
		}

		// Fetch all data, regardless of privacy
		$rows = get_SOUR_rows($term);

		// Filter for privacy
		foreach ($rows as $row) {
			$source = KT_Source::getInstance($row);
			if ($source->canDisplayName()) {
				$data[] = array('value'=>$row['xref'], 'label'=>$row['n_full']);
			}
		}

		// Fetch all data, regardless of privacy
		$rows = get_REPO_rows($term);

		// Filter for privacy
		foreach ($rows as $row) {
			$repository = KT_Repository::getInstance($row);
			if ($repository->canDisplayName()) {
				$data[] = array('value'=>$row['xref'], 'label'=>$row['n_full']);
			}
		}

		// Fetch all data, regardless of privacy
		$rows = get_OBJE_rows($term);

		// Filter for privacy
		foreach ($rows as $row) {
			$media = KT_Media::getInstance($row);
			if ($media->canDisplayName()) {
				$data[] = array('value'=>$row['xref'], 'label'=>'<img src="'.$media->getHtmlUrlDirect().'" width="25"> '.$media->getFullName());
			}
		}

		// Fetch all data, regardless of privacy
		$rows = get_FAM_rows($term);

		// Filter for privacy
		foreach ($rows as $row) {
			$family = KT_Family::getInstance($row);
			if ($family->canDisplayName()) {
				$marriage_year = $family->getMarriageYear();
				if ($marriage_year) {
					$data[] = array('value'=>$family->getXref(), 'label'=>$family->getFullName().', <i>'.$marriage_year.'</i>');
				} else {
					$data[] = array('value'=>$family->getXref(), 'label'=>$family->getFullName());
				}
			}
		}

		// Fetch all data, regardless of privacy
		$rows = get_NOTE_rows($term);

		// Filter for privacy
		foreach ($rows as $row) {
			$note = KT_Note::getInstance($row);
			if ($note->canDisplayName()) {
				$data[] = array('value'=>$row['xref'], 'label'=>$note->getFullName());
			}
		}

		echo json_encode($data);

	exit;

	case 'IFS':
		$data = array();

		// Fetch all data, regardless of privacy
		$rows = get_INDI_rows($term);

		// Filter for privacy
		foreach ($rows as $row) {
			$person = KT_Person::getInstance($row);
			if ($person->canDispLayname()) {
				$data[] = array('value'=>$row['xref'], 'label'=>'<i class="icon-button_indi"></i>'. str_replace(array('@N.N.', '@P.N.'), array($UNKNOWN_NN, $UNKNOWN_PN), $row['n_full']).', <i>'.$person->getLifeSpan().'</i>');
			}
		}

		// Fetch all data, regardless of privacy
		$rows = get_SOUR_rows($term);

		// Filter for privacy
		foreach ($rows as $row) {
			$source = KT_Source::getInstance($row);
			if ($source->canDispLayname()) {
				$data[] = array('value'=>$row['xref'], 'label'=>'<i class="icon-button_source"></i>'. $row['n_full']);
			}
		}

		// Fetch all data, regardless of privacy
		$rows = get_FAM_rows($term);

		// Filter for privacy
		foreach ($rows as $row) {
			$family = KT_Family::getInstance($row);
			if ($family->canDispLayname()) {
				$marriage_year = $family->getMarriageYear();
				if ($marriage_year) {
					$data[] = array('value'=>$family->getXref(), 'label'=>'<i class="icon-button_family"></i>'. $family->getFullName().', <i>'.$marriage_year.'</i>');
				} else {
					$data[] = array('value'=>$family->getXref(), 'label'=>'<i class="icon-button_family"></i>'. $family->getFullName());
				}
			}
		}

		echo json_encode($data);

	exit;
}

function get_FAM_rows($term) {
	return
		KT_DB::prepare("
			SELECT DISTINCT 'FAM' AS type, f_id AS xref, f_file AS ged_id, f_gedcom AS gedrec, husb_name.n_sort, wife_name.n_sort
			 FROM `##families`
			 JOIN `##name` AS husb_name ON (f_husb=husb_name.n_id AND f_file=husb_name.n_file)
			 JOIN `##name` AS wife_name ON (f_wife=wife_name.n_id AND f_file=wife_name.n_file)
			 WHERE CONCAT(husb_name.n_full, ' ', wife_name.n_full) LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%') AND f_file=?
			 AND husb_name.n_type<>'_MARNM' AND wife_name.n_type<>'_MARNM'
			 ORDER BY husb_name.n_sort, wife_name.n_sort COLLATE '" . KT_I18N::$collation . "'
		")
		->execute(array($term, KT_GED_ID))
		->fetchAll(PDO::FETCH_ASSOC);
}

function get_INDI_rows($term) {
	return
		KT_DB::prepare("
			SELECT 'INDI' AS type, i_id AS xref, i_file AS ged_id, i_gedcom AS gedrec, n_full
			 FROM `##individuals`
			 JOIN `##name` ON (i_id=n_id AND i_file=n_file)
			 WHERE n_full LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%') AND i_file=? ORDER BY n_full COLLATE '" . KT_I18N::$collation . "'
		")
		->execute(array($term, KT_GED_ID))
		->fetchAll(PDO::FETCH_ASSOC);
}

function get_NOTE_rows($term) {
	return
		KT_DB::prepare("
			SELECT o_type AS type, o_id AS xref, o_file AS ged_id, o_gedcom AS gedrec, n_full
			 FROM `##other`
			 JOIN `##name` ON (o_id=n_id AND o_file=n_file)
			 WHERE o_gedcom LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%')
			 AND o_file=?
			 AND o_type='NOTE'
			 ORDER BY n_full COLLATE '" . KT_I18N::$collation . "'
		")
		->execute(array($term, KT_GED_ID))
		->fetchAll(PDO::FETCH_ASSOC);
}

function get_OBJE_rows($term) {
	return
		KT_DB::prepare("
			SELECT 'OBJE' AS type, m_id AS xref, m_file AS ged_id, m_gedcom AS gedrec, m_titl, m_filename
			 FROM `##media`
			 WHERE (m_titl LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%') OR m_id LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%')) AND m_file=?
		")
		->execute(array($term, $term, KT_GED_ID))
		->fetchAll(PDO::FETCH_ASSOC);
}

function get_REPO_rows($term) {
	return
		KT_DB::prepare("
			SELECT o_type AS type, o_id AS xref, o_file AS ged_id, o_gedcom AS gedrec, n_full
			FROM `##other`
			JOIN `##name` ON (o_id=n_id AND o_file=n_file)
			WHERE o_gedcom LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%')
			AND o_file=?
			AND o_type='REPO'
			ORDER BY n_full COLLATE '" . KT_I18N::$collation . "'
		")
		->execute(array($term, KT_GED_ID))
		->fetchAll(PDO::FETCH_ASSOC);
}

function get_SOUR_rows($term) {
	return
		KT_DB::prepare("
			SELECT 'SOUR' AS type, s_id AS xref, s_file AS ged_id, s_gedcom AS gedrec, s_name AS n_full
			 FROM `##sources`
			 WHERE s_name LIKE CONCAT('%', REPLACE(?, ' ', '%'), '%')
			 AND s_file=?
			 ORDER BY s_name COLLATE '" . KT_I18N::$collation . "'
		")
		->execute(array($term, KT_GED_ID))
		->fetchAll(PDO::FETCH_ASSOC);
}
