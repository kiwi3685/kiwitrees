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

// Print a fact record, for the individual/family/source/repository/etc. pages.
//
// Although a KT_Event has a parent object, we also need to know
// the KT_GedcomRecord for which we are printing it. For example,
// we can show the death of X on the page of Y, or the marriage
// of X+Y on the page of Z. We need to know both records to
// calculate ages, relationships, etc.
//
// This is a copy of function print_fact() (functions_print_facts.php) without date and place for different formatting purposes
//
function print_resourcefactDetails(KT_Event $fact, KT_GedcomRecord $record) {
	global $HIDE_GEDCOM_ERRORS;
	$SHOW_PARENTS_AGE = false; // not required on resource prints
	$html = '';

	static $n_chil = 0, $n_gchi = 0;

	if (!$fact->canShow()) {
		return;
	}

	// Print the value of this fact/event
	switch ($fact->getTag()) {
	case 'ADDR':
		$html .= print_address_structure($fact->getGedcomRecord(), 1);
		break;
	case 'AFN':
		$html .= '<a href="https://familysearch.org/search/tree/results#count=20&query=afn:' . rawurlencode((string) $fact->getDetail()) . '" target="new">' . htmlspecialchars((string) $fact->getDetail()) . '</a>';
		break;
	case 'ASSO':
		// include RELA if recorded
		preg_match_all('/^1 ASSO @('.KT_REGEX_XREF.')@((\n[2-9].*)*)/', $fact->getGedcomRecord(), $amatches1, PREG_SET_ORDER);
		preg_match_all('/\n2 _?ASSO @('.KT_REGEX_XREF.')@((\n[3-9].*)*)/', $fact->getGedcomRecord(), $amatches2, PREG_SET_ORDER);
		// For each ASSO record
		foreach (array_merge($amatches1, $amatches2) as $amatch) {
			$person = KT_Person::getInstance($amatch[1]);
			if (!$person) {
				// If the target of the ASSO does not exist, create a dummy person, so
				// the user can see that something is present.
				$person = new KT_Person('');
			}
			if (preg_match('/\n[23] RELA (.+)/', $amatch[2], $rmatch)) {
				$html .= '<span dir="auto">' . KT_Gedcom_Tag::getLabel('RELA') . ':&nbsp;' . KT_Gedcom_Code_Rela::getValue($rmatch[1], $person) . ':&nbsp;' . $person->getFullName() . '</span>';
			} else {
				$html .= '&nbsp;';
			}
		}
		break;
	case 'BURI':
		// include CEME if recorded
		if (preg_match('/\n2 CEME (.+)/', $fact->getGedcomRecord(), $match)) {
			$html .= KT_Gedcom_Tag::getLabelValue('CEME', $match[1]);
		} else {
			$html .= '&nbsp;';
		}
		// include ADDR if recorded
		$html .= print_address_structure($fact->getGedcomRecord(), 2, 'simple');
		break;
	case 'EDUC':
	case 'GRAD':
	case 'OCCU':
		$html .= '<span dir="auto">' . htmlspecialchars((string) $fact->getDetail()) . '</span>';
		// include AGNC if recorded
		if (preg_match('/\n2 AGNC (.+)/', $fact->getGedcomRecord(), $match)) {
			$html .= KT_Gedcom_Tag::getLabelValue($fact->getTag() . ':AGNC', $match[1], null, 'span');
		} else {
			$html .= '&nbsp;';
		}
		// include ADDR if recorded
		if (preg_match('/\n2 ADDR (.+)/', $fact->getGedcomRecord(), $match)) {
			$html .= KT_Gedcom_Tag::getLabelValue('ADDR', $match[1]);
		} else {
			$html .= '&nbsp;';
		}
		break;
	case 'EMAIL':
	case 'EMAI':
	case '_EMAIL':
		$html .= '<a href="mailto:' . htmlspecialchars((string) $fact->getDetail()) . '">' . htmlspecialchars((string) $fact->getDetail()) . '</a>';
		break;
	case 'FILE':
		if (KT_USER_CAN_EDIT || KT_USER_CAN_ACCEPT) {
			$html .= htmlspecialchars((string) $fact->getDetail());
		}
		break;
	case 'RESN':
		$html .= '';
		switch ($fact->getDetail()) {
		case 'none':
			// Note: "1 RESN none" is not valid gedcom.
			// However, kiwitrees privacy rules will interpret it as "show an otherwise private record to public".
			$html .= '<i class="icon-resn-none"></i> ' . KT_I18N::translate('Show to visitors');
			break;
		case 'privacy':
			$html .= '<i class="icon-class-none"></i> ' . KT_I18N::translate('Show to members');
			break;
		case 'confidential':
			$html .= '<i class="icon-confidential-none"></i> ' . KT_I18N::translate('Show to managers');
			break;
		case 'locked':
			$html .= '<i class="icon-locked-none"></i> ' . KT_I18N::translate('Only managers can edit');
			break;
		default:
			$html .= htmlspecialchars((string) $fact->getDetail());
			break;
		}
		$html .= '';
		break;
	case 'PUBL': // Publication details might contain URLs.
		$html .= expand_urls(htmlspecialchars((string) $fact->getDetail()));
		break;
	case 'REPO':
		if (preg_match('/^@('.KT_REGEX_XREF.')@$/' . $fact->getDetail() . $match)) {
			print_repository_record($match[1]);
		} else {
			$html .= '<div class="error">' . htmlspecialchars((string) $fact->getDetail()) . '</div>';
		}
		break;
	case 'URL':
	case '_URL':
	case 'WWW':
		$html .= '<a href="' . htmlspecialchars((string) $fact->getDetail()) . '">' . htmlspecialchars((string) $fact->getDetail()) . '</a>';
		break;
	case 'TEXT': // 0 SOUR / 1 TEXT
		$html .= nl2br(htmlspecialchars((string) $fact->getDetail()));
		break;
	default:
		// Display the value for all other facts/events
		switch ($fact->getDetail()) {
		case '':
			// Nothing to display
			$html .= '&nbsp;';
			break;
		case 'N':
			// Not valid GEDCOM
			$html .= KT_I18N::translate('No');
			break;
		case 'Y':
			// Do not display "Yes".
			break;
		default:
			if (preg_match('/^@('.KT_REGEX_XREF.')@$/', $fact->getDetail(), $match)) {
				$target = KT_GedcomRecord::getInstance($match[1]);
				if ($target) {
					$html .= '<div><a href="' . $target->getHtmlUrl() . '">' . $target->getFullName() . '</a></div>';
				} else {
					$html .= '<div class="error">' . htmlspecialchars((string) $fact->getDetail()) . '</div>';
				}
			} else {
				$html .= '<span dir="auto">' . htmlspecialchars((string) $fact->getDetail()) . '</span>';
			}
			break;
		}
		break;
	}

	return $html;

}
//end print resource fact function

function report_images($person) {
	$level = 1;
	$regexp = '/\n' . $level . ' OBJE @(.*)@/';

	//-- get a list of the current objects in the record
	$current_objes = array();
	$ct = preg_match_all($regexp, $person->getGedcomRecord(), $match, PREG_SET_ORDER);
	for ($i = 0; $i < $ct; $i++) {
		if (!isset($current_objes[$match[$i][1]])) {
			$current_objes[$match[$i][1]] = 1;
		} else {
			$current_objes[$match[$i][1]]++;
		}
		$obje_links[$match[$i][1]][] = $match[$i][0];
	}
}

// Print a row for the sources for an event or fact
function report_sources(KT_Event $fact, $level, $short=false) {
	$fact	= $fact->getGedcomRecord();
	$data 		= array();
	// -- find sources for each fact
	$ct = preg_match_all("/($level SOUR (.+))/", $fact, $match, PREG_SET_ORDER);
	$spos2 = 0;
	for ($j = 0; $j < $ct; $j++) {
		$details = '';
		$sid	= trim($match[$j][2], '@');
		$spos1	= stripos($fact, $match[$j][1], $spos2);
		$spos2	= stripos($fact, "\n$level", $spos1);
		if (!$spos2) $spos2 = strlen($fact);
		$srec	= substr($fact, $spos1, $spos2-$spos1);
		$source	= KT_Source::getInstance($sid);
		if ($source) {
			// AUTH
			$auth = htmlspecialchars((string) $source->getAuth());
			if (!empty($auth)) {
				if (stripos($auth, "@") == 0 && substr($auth, -1) == '@') {
					$pid = str_replace('@', '', $auth);
					$details .= '<i>' . KT_Person::getInstance($pid)->getFullName() . '</i>, ';
				} else {
					$details .= $auth . ', ';
				}
			}
			// TITLE
			$details .= '<u>' . $source->getFullName() . '</u>';
			// PUBL
			$publ = get_gedcom_value('PUBL', '1', $source->getGedcomRecord());
			if (!empty($publ)) {
				$details .= ': ' . $publ;
			}
			// PAGE (citation)
			$page = expand_urls(getSourceStructure($srec)['PAGE']);
			if (!empty($page)) {
				$details .= ': ' . $page;
			}
			// TEXT
			if(!$short){
				foreach (getSourceStructure($srec)['TEXT'] as $text_list) {
				$text = expand_urls($text_list);
				}
				if (!empty($text)) {
					$details .= ': ' . $text;
				}
			}
		}

		$data[] = $details;
	}

	return $data;
}

function print_fact_label(KT_Event $fact, KT_GedcomRecord $record) {

	if (!$fact->canShow()) {
		return;
	}

	// Who is this fact about? Need it to translate fact label correctly
	if ($fact->getSpouse()) {
		// Event of close relative
		$label_person = $fact->getSpouse();
	} else if (preg_match('/2 _WTS @('.KT_REGEX_XREF.')@/', $fact->getGedcomRecord(), $match)) {
		// Event of close relative
		$label_person = KT_Person::getInstance($match[1]);
	} else if ($fact->getParentObject() instanceof KT_Family) {
		// Family event
		$husb = $fact->getParentObject()->getHusband();
		$wife = $fact->getParentObject()->getWife();
		if (empty($wife) && !empty($husb)) $label_person = $husb;
		else if (empty($husb) && !empty($wife)) $label_person = $wife;
		else $label_person = $fact->getParentObject();
	} else {
		// The actual person
		$label_person = $fact->getParentObject();
	}

	// Does this fact have a type?
	if (preg_match('/\n2 TYPE (.+)/', $fact->getGedcomRecord(), $match)) {
		$type = $match[1];
	} else {
		$type='';
	}

	switch ($fact->getTag()) {
	case 'EVEN':
	case 'FACT':
		if (KT_Gedcom_Tag::isTag($type)) {
			// Some users (just Meliza?) use "1 EVEN/2 TYPE BIRT". Translate the TYPE.
			$label = KT_Gedcom_Tag::getLabel($type, $label_person);
			$type=''; // Do not print this again
		} elseif ($type) {
			// We don't have a translation for $type - but a custom translation might exist.
			$label = KT_I18N::translate(htmlspecialchars((string) $type));
			$type=''; // Do not print this again
		} else {
			// An unspecified fact/event
			$label = KT_Gedcom_Tag::getLabel($fact->getTag(), $label_person);
		}
		break;
	case 'MARR':
		$marr_type = strtoupper((string) $type);
		if ($marr_type=='CIVIL' || $marr_type=='PARTNERS' || $marr_type=='RELIGIOUS' || $marr_type=='COML' || $marr_type=='UNKNOWN') {
			$marr_fact = 'MARR_' . $marr_type;
		} else {
			$marr_fact = 'MARR';
		}
		$label = KT_Gedcom_Tag::getLabel($marr_fact, $label_person);
		break;
	default:
		// Normal fact/event
		$label = KT_Gedcom_Tag::getLabel($fact->getTag(), $label_person);
		break;
	}

	echo $label;
}

function print_resourcenotes(KT_Event $fact, $level, $textOnly = false, $return = false) {
	global $GEDCOM;
	$ged_id = get_id_from_gedcom($GEDCOM);
	$fact	= $fact->getGedcomRecord();

	$data = "";
	$previous_spos = 0;
	$nlevel = $level + 1;
	$ct = preg_match_all("/$level NOTE(.*)/", $fact, $match, PREG_SET_ORDER);
	for ($j=0; $j<$ct; $j++) {
		$nid = str_replace("@","",$match[$j][1]);
		$spos1 = stripos($fact, $match[$j][0], $previous_spos);
		$spos2 = stripos($fact."\n$level", "\n$level", $spos1+1);
		if (!$spos2) $spos2 = strlen($fact);
		$nrec = substr($fact, $spos1, $spos2-$spos1);
		if (!isset($match[$j][1])) $match[$j][1]="";
		$previous_spos = $spos2;
		$nt = preg_match("/@(.*)@/", $match[$j][1], $nmatch);
		$closeSpan = false;
		if ($nt == 0) {
			//-- print embedded note records
			$closeSpan = print_note_record($match[$j][1], $nlevel, $nrec, $textOnly, true);
			$data .= $closeSpan;
		} else {
			$note = KT_Note::getInstance($nmatch[1]);
			if ($note) {
				if ($note->canDisplayDetails()) {
					$noterec = $note->getGedcomRecord();
					//-- print linked note records
					$nt = preg_match("/0 @$nmatch[1]@ NOTE (.*)/", $noterec, $n1match);
					$closeSpan = print_note_record(($nt>0)?$n1match[1]:"", 1, $noterec, $textOnly, true);
					$data .= $closeSpan;
					if (!$textOnly) {
						if (stripos($noterec, "1 SOUR") !== false) {
							require_once KT_ROOT.'includes/functions/functions_print_facts.php';
							$data .= print_fact_sources($noterec, 1, true);
						}
					}
				}
			} else {
				$data = '<div class="fact_NOTE"><span class="label">' . KT_I18N::translate('Note') . '</span>: <span class="field error">' . $nid . '</span></div>';
			}
		}
		if (!$textOnly) {
			if (stripos($fact, "$nlevel SOUR") !== false) {
				$data .= '<div class="indent">' . print_fact_sources($nrec, $nlevel, true) . '</div>';
			}
		}
	}
	if (!$return) echo $data;
	else return $data;
}

function report_findfact($fact, $type='') {
	$list = array();
	// Fetch all data, regardless of privacy
	if ($fact == 'EVEN' || $fact == 'FACT'){
		$sql = "
			SELECT i_id AS xref
			FROM `##individuals`
			WHERE `i_gedcom` REGEXP '(.*)\n1 " . $fact . ".*\n2 TYPE ([^\n]*)" . $type . "*[^\n]*'
			AND i_file=?
		";
	} else {
		$sql = "
			SELECT i_id AS xref, i_file
			FROM `##individuals`
			WHERE `i_gedcom` REGEXP '(.*)\n1 " . $fact . "'
			AND i_file=?
		";
	}
	$rows = KT_DB::prepare($sql)->execute(array(KT_GED_ID))->fetchAll();
	foreach ($rows as $row) {
		$person = KT_Person::getInstance($row->xref);
		$indifacts = $person->getIndiFacts();
		foreach ($indifacts as $item) {
			if ($item->getTag() == $fact) {
					$list[] = $row;
			}
		}
	}
	// remove duplicate individuals
	$list = array_unique($list, SORT_REGULAR);

	return $list;
}

function filter_facts ($item, $person, $year_from, $year_to, $place, $detail, $type = false) {
	if ($year_from || $year_to || $place || $detail || $type) {
		$result_place	= format_fact_place($item, true);
		$result_date	= format_fact_date($item, $person, false, true, false);
		$result_detail	= print_resourcefactDetails($item, $person);
		$result_type	= false;
		if ($type) {
			$ct = preg_match("/2 TYPE (.*)/", $item->getGedcomRecord(), $ematch);
			if ($ct > 0) {
				$result_type = trim($ematch[1]);
			}
		}

		if ($year_from || $year_to) {
			preg_match_all("/\d{4}/", format_fact_date($item, $person, false, true, false), $matches);
			$ct = count($matches[0]);
			if (
					($ct === 1 && (
							($year_from && !$year_to && $matches[0][0] >= $year_from) ||
							($year_to && !$year_from && $matches[0][0] <= $year_to) ||
							($year_from && $year_to && $matches[0][0] >= $year_from && $matches[0][0] <= $year_to)
						)
					) ||
					($ct === 2 && (
							($year_from && $matches[0][0] >= $year_from) && ($year_to && $matches[0][0] <= $year_to) &&
							($year_from && $matches[0][1] >= $year_from) && ($year_to && $matches[0][1] <= $year_to)
						)
					)
				) {
					if (!$place && !$detail && !$type) {
						return true;
					} elseif (!$place && !$detail && $type && stripos(strip_tags($result_type), $type) !== false) {
						return true;
					} elseif (!$place && $detail && !$type && stripos(strip_tags($result_detail), $detail) !== false) {
						return true;
					} elseif (!$place && $detail && $type && stripos(strip_tags($result_detail), $detail) !== false && stripos(strip_tags($result_type), $type) !== false) {
						return true;
					} elseif ($place && !$detail && !$type && stripos(strip_tags($result_place), $place) !== false) {
						return true;
					} elseif ($place && !$detail && $type && stripos(strip_tags($result_place), $place) !== false && stripos(strip_tags($result_type), $type) !== false) {
						return true;
					} elseif ($place && $detail && !$type && stripos(strip_tags($result_place), $place) !== false && stripos(strip_tags($result_detail), $detail) !== false) {
						return true;
					} elseif ($place && $detail && $type && stripos(strip_tags($result_place), $place) !== false && stripos(strip_tags($result_detail), $detail) !== false && stripos(strip_tags($result_type), $type) !== false) {
						return true;
					}
			}
		}
		if ($place && !$year_from && !$year_to) {
			if (stripos(strip_tags($result_place), $place) !== false) {
				if (!$detail && !$type) {
					return true;
				} elseif ($detail && !$type && stripos(strip_tags($result_detail), $detail) !== false ) {
					return true;
				} elseif (!$detail && $type && stripos(strip_tags($result_type), $type) !== false ) {
					return true;
				}
			}
		}
		if ($detail && !$year_from && !$year_to && !$place) {
			if (stripos(strip_tags($result_detail), $detail) !== false) {
				return true;
			}
		}
		if ($type && !$year_from && !$year_to && !$place){
			if (stripos(strip_tags($result_type), $type) !== false) {
				return true;
			}
		}
	} else {
		return true;
	}
}

//--- based on function add_descendancy() in functions.php
function add_report_descendancy($i, $person, $parents = false, $generations = -1) {
	$families = $person->getSpouseFamilies();
	foreach ($families as $family) {
		$spouse = $family->getSpouse($person);
		$marriage = $family->getMarriage();
		$children = $family->getChildren();
		if (!empty($spouse)) {
			if ($parents) {
				$i++;
				$related_individuals[$i]['relationship']	= $marriage ? KT_I18N::translate('Spouse') : KT_I18N::translate('Partner');
				$related_individuals[$i]['name']			= $spouse->getFullName();
				$related_individuals[$i]['birth']			= $spouse->getBirthDate()->Display();
				$related_individuals[$i]['bdate']			= $spouse->getBirthDate()->JD();
				$related_individuals[$i]['bplac']			= $spouse->getBirthPlace();
				$related_individuals[$i]['marr']			= $family->getMarriageDate()->Display();
				$related_individuals[$i]['death']			= $spouse->getDeathDate()->Display();
				$related_individuals[$i]['ddate']			= $spouse->getDeathDate()->JD();
				$related_individuals[$i]['dplac']			= $spouse->getDeathPlace();
				$related_individuals[$i]['father']			= $spouse->getPrimaryChildFamily() ? $spouse->getPrimaryChildFamily()->getHusband()->getLifespanName() : '';
				$related_individuals[$i]['mother']			= $spouse->getPrimaryChildFamily() ? $spouse->getPrimaryChildFamily()->getWife()->getLifespanName() : '';
			}
			foreach ($children as $child) {
				if (!empty($child) && $child != $person) {
					$i++;
					$related_individuals[$i]['relationship']	= get_relationship_name(get_relationship($person, $child));
					$related_individuals[$i]['name']			= $child->getFullName();
					$related_individuals[$i]['birth']			= $child->getBirthDate()->Display();
					$related_individuals[$i]['bdate']			= $child->getBirthDate()->JD();
					$related_individuals[$i]['bplac']			= $child->getBirthPlace();
					$related_individuals[$i]['marr']			= '';
					$related_individuals[$i]['death']			= $child->getDeathDate()->Display();
					$related_individuals[$i]['ddate']			= $child->getDeathDate()->JD();
					$related_individuals[$i]['dplac']			= $child->getDeathPlace();
					$related_individuals[$i]['father']			= $family->getHusband()->getLifespanName();
					$related_individuals[$i]['mother']			= $family->getWife()->getLifespanName();
				}
			}
			if ($generations == -1) {
				foreach ($children as $child) {
					add_report_descendancy($i, $child); // recurse on the childs family
				}
			}
		}
	}
	return array ($related_individuals, $i);

}

function marriageDetails($family) {
	$marr_type = strtoupper((string) $family->getMarriageType());
	if ($marr_type == 'CIVIL' || $marr_type=='PARTNERS' || $marr_type=='RELIGIOUS' || $marr_type=='COML' || $marr_type=='UNKNOWN') {
		$marr_fact = 'MARR_' . $marr_type;
	} else {
		$marr_fact = 'MARR';
	}
	$place	= $family->getMarriagePlace();
	$date	= $family->getMarriageDate();
	if ($date && $date->isOK() || $place) {
		if ($date) {
			$details = $date->Display(false);
		}
		if ($place) {
			if ($details) {
				$details .= ' — ';
			}
			$tmp = new KT_Place($place, KT_GED_ID);
			$details .= $tmp->getShortName();
		}
		return KT_Gedcom_Tag::getLabelValue($marr_fact, $details);
	} else if (get_sub_record(1, "1 _NMR", find_family_record($family, KT_GED_ID))) {
		$husb = $family->getHusband();
		$wife = $family->getWife();
		if (empty($wife) && !empty($husb)) {
			return KT_Gedcom_Tag::getLabel('_NMR', $husb);
		} elseif (empty($husb) && !empty($wife)) {
			return KT_Gedcom_Tag::getLabel('_NMR', $wife);
		} else {
			return KT_Gedcom_Tag::getLabel('_NMR');
		}
	} else {
		return KT_Gedcom_Tag::getLabelValue($marr_fact, KT_I18N::translate('yes'));
	}

}

// list vital records for individuals
function report_vital_records ($name, $place, $b_fromJD, $b_toJD, $d_fromJD, $d_toJD, $ged){
	$sql_select   = "SELECT i_id AS xref, i_gedcom AS gedcom FROM `##individuals` ";
	$sql_join     = "";
	$sql_where    = " WHERE i_file = " . $ged;

	// Name filter
	if ($name) {
		$sql_join .= " JOIN `##name` ON (n_file=i_file AND n_id=i_id)";
		$names = explode(" ", $name);
		foreach ($names as $name) {
			$sql_where .= " AND n_full LIKE CONCAT('%', '" . $name . "', '%')";
		}
	}
	// Place filter
	if ($place) {
		$sql_where .= " AND i_gedcom LIKE CONCAT('%', '" . $place . "', '%')";
	}
	// Date filters
	if ($b_fromJD || $b_toJD || $d_fromJD || $d_toJD) {
		if ($b_fromJD || $b_toJD) {
			$sql_join	.= " JOIN `##dates` AS B ON (B.d_file=i_file AND B.d_gid=i_id)";
			$sql_where	.= " AND B.d_fact = 'BIRT'";
			if ($b_fromJD) {
				$sql_where .= " AND B.d_julianday1 >= $b_fromJD";
			}
			if ($b_toJD) {
				$sql_where .= " AND B.d_julianday2 <= $b_toJD";
			}
		}
		if ($d_fromJD || $d_toJD) {
			$sql_join	.= " JOIN `##dates` AS D ON (D.d_file=i_file AND D.d_gid=i_id)";
			$sql_where	.= " AND D.d_fact = 'DEAT'";
			if ($d_fromJD) {
				$sql_where .= " AND D.d_julianday1 >= $d_fromJD";
			}
			if ($d_toJD) {
				$sql_where .= " AND D.d_julianday2 <= $d_toJD";
			}
		}
	}

	$list = array();
	$rows = KT_DB::prepare($sql_select . $sql_join . $sql_where)->execute()->fetchAll();
	foreach ($rows as $row) {
		$list[$row->xref] = KT_Person::getInstance($row->xref);
	}

	return $list;
}

// fact details for family report
function getResourcefact($fact, $family, $sup, $source_list, $number) {
	$resourcefact			= array();
	$resourcefact['date']	= $fact->getDate()->isOK() ?  format_fact_date($fact, $family, false, false, false) : '';
	$place					= format_fact_place($fact, true, true, true);
	$resourcefact['place']	= $place ? '<span class="place">' . $place . '</span>' : '';
	$address				= $fact->getPlace() != '' ? print_address_structure($fact->getGedcomRecord(), 2, 'inline') : '';
	$resourcefact['addr']	= $address ? '<span class="addr">' . $address . '</span>' : '';
	if (!in_array($fact->getTag(), array('BURI'))) {
		$detail	= print_resourcefactDetails($fact, $family);
		$resourcefact['detail'] = $detail !== "&nbsp;" ?  '<span class="field">' . $detail . '</span>' : '';
	} else $resourcefact['detail'] = '';

	// -- count source(s) for this fact/event as footnote reference
	$ct = preg_match_all("/\d SOUR @(.*)@/", $fact->getGedcomRecord(), $match, PREG_SET_ORDER);
	if ($ct > 0) {
		$sources = report_sources($fact, 2);
		$sup = '<sup class="source">';
		foreach ($sources as $source) {
			$duplicate = false;
			foreach($source_list as $src) {
				if ($source == $src['value']) {
					$sup .= $src['key'] . ',&nbsp;';
					$duplicate = true;
					break;
				}
			}
			if (!$duplicate) {
			   $number ++;
			   $source_list[] = array('key' => $number, 'value' => $source);
			   $sup .= $number . ',&nbsp;';
			}
		}
		$sup = rtrim($sup,',&nbsp;') . '</sup>';
	} else {
		$sup = '';
	}

	return array($sup, $source_list, $number, $resourcefact);
}

// list marriage records
function report_marriages ($place, $m_fromJD, $m_toJD, $ged, $name = ''){

	$sql_select   = "SELECT DISTINCT f_id AS xref, f_gedcom AS gedcom FROM `##families` ";
	$sql_join     = "";
	$sql_where    = " WHERE f_file = " . $ged . " AND f_gedcom LIKE '%1 MARR%'";

	// Date filters
	if ($m_fromJD || $m_toJD) {
		$sql_join	.= " JOIN `##dates` AS B ON (B.d_file=f_file AND B.d_gid=f_id)";
		$sql_where	.= " AND B.d_fact = 'MARR'";
		if ($m_fromJD) {
			$sql_where .= " AND B.d_julianday1 >= $m_fromJD";
		}
		if ($m_toJD) {
			$sql_where .= " AND B.d_julianday2 <= $m_toJD";
		}
	}

	$rows = KT_DB::prepare($sql_select . $sql_join . $sql_where)->execute()->fetchAll();

	// Limit rows to marriages in selected place (easier here than in DB)
	if ($place) {
		foreach ($rows as $row) {
			$family = KT_Family::getInstance($row->xref);
			if ($family->getMarriagePlace() && stristr($family->getMarriagePlace(), $place)) {
				$newrows[] = $row;
			}
		}
		$rows = $newrows;
	}

	$list = array();
	foreach ($rows as $row) {
		if ($name) {
			// Name filter - must be done here because family number not available earlier.
			$family = KT_Family::getInstance($row->xref);
			if (stristr($family->getFullName(), $name)) {
				$list[] = $family;
			}
		} else {
			$list[] = KT_Family::getInstance($row->xref);
		}
	}

	return $list;
}

function personDetails($person) {
	$birth_date = $person->getBirthDate();
	$birth_plac = $person->getBirthPlace();
	$death_date = $person->getDeathDate();
	$death_plac = $person->getDeathPlace();
	$birth = '';
	$death = '';

	if ($birth_date->isOK() || $birth_plac != '') {
		$birth = KT_Gedcom_Tag::getLabel('BIRT') . ':&nbsp;' .
			$birth_date->Display() . '&nbsp;' .
			$birth_plac;
	}

	if ($death_date->isOK() || $death_plac != '') {
		$death = ($birth == '' ? '' : '&nbsp;-&nbsp;') .
			KT_Gedcom_Tag::getLabel('DEAT') . ':&nbsp;' .
			$death_date->Display() . '&nbsp;' .
			$death_plac;
	}

	return $birth . $death;
}
