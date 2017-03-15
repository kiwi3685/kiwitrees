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

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class WT_Controller_Search extends WT_Controller_Page {
	public $action;
	// TODO: decide if these variables are public/private/protected (or unused)
	var $isPostBack = false;
	var $srfams;
	var $srindi;
	var $srnote;
	var $srsour;
	var $srstor;
	var $resultsPageNum = 0;
	var $resultsPerPage = 50;
	var $totalResults = -1;
	var $totalGeneralResults = -1;
	var $indiResultsPrinted = -1;
	var $famResultsPrinted = -1;
	var $srcResultsPrinted = -1;
	var $query;
	var $myquery = "";
	var $soundex = "DaitchM";
	var $subaction = "";
	var $nameprt = "";
	var $showasso = "off";
	var $name="";
	var $myname;
	var $birthdate="";
	var $mybirthdate;
	var $birthplace="";
	var $mybirthplace;
	var $deathdate="";
	var $mydeathdate;
	var $deathplace="";
	var $mydeathplace;
	var $gender="";
	var $mygender;
	var $firstname="";
	var $lastname="";
	var $place="";
	var $year="";
	var $sgeds = array ();
	var $myindilist = array ();
	var $mysourcelist = array ();
	var $myfamlist = array ();
	var $mynotelist = array ();
	var $mystorieslist = array ();
	var $inputFieldNames = array ();
	var $replace = false;
	var $replaceNames = false;
	var $replacePlaces = false;
	var $replaceAll = false;
	var $replacePlacesWord = false;
	var $printplace = array();
	var $fields = array();
	var $values = array();
	var $plusminus = array();
	var $errors = array();


	function __construct() {
		parent::__construct();

		// action comes from $_GET (menus) or $_POST (form submission)
		$this->action = safe_REQUEST($_REQUEST, 'action', array('advanced', 'general', 'soundex', 'replace'), 'general');

		$topsearch = safe_POST_bool('topsearch');

		if ($topsearch) {
			$this->isPostBack = true;
			$this->srfams = 'yes';
			$this->srindi = 'yes';
			$this->srsour = 'yes';
			$this->srnote = 'yes';
			$this->srstor = 'yes';
		}

		// Get the query and remove slashes
		if (isset ($_REQUEST["query"])) {
			// Reset the "Search" text from the page header
			if (strlen($_REQUEST["query"])<2) {
				$this->query="";
				$this->myquery="";
			} else {
				$this->query = $_REQUEST["query"];
				$this->myquery = htmlspecialchars($this->query);
			}
		}
		if (isset ($_REQUEST["replace"])) {
			$this->replace = $_REQUEST["replace"];

			if (isset($_REQUEST["replaceNames"])) $this->replaceNames = true;
			if (isset($_REQUEST["replacePlaces"])) $this->replacePlaces = true;
			if (isset($_REQUEST["replacePlacesWord"])) $this->replacePlacesWord = true;
			if (isset($_REQUEST["replaceAll"])) $this->replaceAll = true;
		}

		// TODO: fetch each variable independently, using appropriate validation
		// Aquire all the variables values from the $_REQUEST
		$varNames = array ("isPostBack", "srfams", "srindi", "srsour", "srnote", "srstor", "view", "soundex", "subaction", "nameprt", "showasso", "resultsPageNum", "resultsPerPage", "totalResults", "totalGeneralResults", "indiResultsPrinted", "famResultsPrinted", "srcResultsPrinted", "myindilist", "mysourcelist", "mynotelist", "myfamlist");
		$this->setRequestValues($varNames);

		if (!$this->isPostBack) {
			// Enable the default gedcom for search
			$str = str_replace(array (".", "-", " "), array ("_", "_", "_"), WT_GEDCOM);
			$_REQUEST["$str"] = $str;
		}

		// Retrieve the gedcoms to search in
		if (count(WT_Tree::getAll())>1 && WT_Site::preference('ALLOW_CHANGE_GEDCOM')) {
			foreach (WT_Tree::getAll() as $tree) {
				$str = str_replace(array (".", "-", " "), array ("_", "_", "_"), $tree->tree_name);
				if (isset ($_REQUEST["$str"]) || $topsearch) {
					$this->sgeds[$tree->tree_id] = $tree->tree_name;
					$_REQUEST["$str"] = 'yes';
				}
			}
		} else {
			$this->sgeds[WT_GED_ID] = WT_GEDCOM;
		}

		// vars use for soundex search
		$this->firstname = WT_Filter::post('firstname');
		$this->lastname  = WT_Filter::post('lastname');
		$this->place     = WT_Filter::post('place');
		$this->year      = WT_Filter::post('year');

		// Set the search result titles for soundex searches
		if ($this->firstname || $this->lastname || $this->place) {
			$this->myquery = htmlspecialchars(implode(' ', array($this->firstname, $this->lastname, $this->place)));
		};

		if (!empty ($_REQUEST["name"])) {
			$this->name = $_REQUEST["name"];
			$this->myname = $this->name;
		} else {
			$this->name="";
			$this->myname = "";
		}
		if (!empty ($_REQUEST["birthdate"])) {
			$this->birthdate = $_REQUEST["birthdate"];
			$this->mybirthdate = $this->birthdate;
		} else {
			$this->birthdate="";
			$this->mybirthdate = "";
		}
		if (!empty ($_REQUEST["birthplace"])) {
			$this->birthplace = $_REQUEST["birthplace"];
			$this->mybirthplace = $this->birthplace;
		} else {
			$this->birthplace="";
			$this->mybirthplace = "";
		}
		if (!empty ($_REQUEST["deathdate"])) {
			$this->deathdate = $_REQUEST["deathdate"];
			$this->mydeathdate = $this->deathdate;
		} else {
			$this->deathdate="";
			$this->mydeathdate = "";
		}
		if (!empty ($_REQUEST["deathplace"])) {
			$this->deathplace = $_REQUEST["deathplace"];
			$this->mydeathplace = $this->deathplace;
		} else {
			$this->deathplace="";
			$this->mydeathplace = "";
		}
		if (!empty ($_REQUEST["gender"])) {
			$this->gender = $_REQUEST["gender"];
			$this->mygender = $this->gender;
		} else {
			$this->gender="";
			$this->mygender = "";
		}

		if ($this->action == "advanced") {
			if (isset($_REQUEST['fields'])) {
				$this->fields = $_REQUEST['fields'];
				ksort($this->fields);
			}
			if (isset($_REQUEST['values'])) {
				$this->values = $_REQUEST['values'];
			}
			if (isset($_REQUEST['plusminus'])) {
				$this->plusminus = $_REQUEST['plusminus'];
			}
			$this->reorderFields();
			$this->AdvancedSearch();
		}

		if (!$this->fields) {
			$this->fields = array(
				'NAME:GIVN:SDX',
				'NAME:SURN:SDX',
				'BIRT:DATE',
				'BIRT:PLAC',
				'FAMS:MARR:DATE',
				'FAMS:MARR:PLAC',
				'DEAT:DATE',
				'DEAT:PLAC',
				'FAMC:HUSB:NAME:GIVN:SDX',
				'FAMC:HUSB:NAME:SURN:SDX',
				'FAMC:WIFE:NAME:GIVN:SDX',
				'FAMC:WIFE:NAME:SURN:SDX',
			);
		}

		$this->inputFieldNames[] = "action";
		$this->inputFieldNames[] = "isPostBack";
		$this->inputFieldNames[] = "resultsPerPage";
		$this->inputFieldNames[] = "query";
		$this->inputFieldNames[] = "srindi";
		$this->inputFieldNames[] = "srfams";
		$this->inputFieldNames[] = "srsour";
		$this->inputFieldNames[] = "srnote";
		$this->inputFieldNames[] = "srstor";
		$this->inputFieldNames[] = "showasso";
		$this->inputFieldNames[] = "firstname";
		$this->inputFieldNames[] = "lastname";
		$this->inputFieldNames[] = "place";
		$this->inputFieldNames[] = "year";
		$this->inputFieldNames[] = "soundex";
		$this->inputFieldNames[] = "nameprt";
		$this->inputFieldNames[] = "subaction";
		$this->inputFieldNames[] = "name";
		$this->inputFieldNames[] = "birthdate";
		$this->inputFieldNames[] = "birthplace";
		$this->inputFieldNames[] = "deathdate";
		$this->inputFieldNames[] = "deathplace";
		$this->inputFieldNames[] = "gender";
		$this->inputFieldNames[] = "srstor";

		// Get the search results based on the action
		if ($topsearch) {
			$this->TopSearch();
		}
		// If we want to show associated persons, build the list
		switch ($this->action) {
			case 'general':
				$this->GeneralSearch();
				break;
			case 'soundex':
				$this->SoundexSearch();
				break;
			case 'replace':
				$this->SearchAndReplace();
				return;
			case 'advanced':
				$this->AdvancedSearch();
				return;
		}
	}

	/**
	 * setRequestValues - Checks if the variable names ($varNames) are in
	 * the $_REQUEST and if so assigns their values to
	 * $this based on the variable name ($this->$varName).
	 *
	 * @param array $varNames - Array of variable names(strings).
	 */
	function setRequestValues($varNames) {
		foreach ($varNames as $varName) {
			if (isset ($_REQUEST[$varName])) {
				if ($varName == 'action' && $_REQUEST[$varName] == 'replace' && !WT_USER_CAN_EDIT) {
					$this->action='general';
					continue;
				}
				$this->$varName = $_REQUEST[$varName];
			}
		}
	}

	function getOtherFields() {
		$ofields = array(
			'ADDR','ADDR:CITY','ADDR:STAE','ADDR:CTRY','ADDR:POST',
			'ADOP:DATE','ADOP:PLAC',
			'AFN',
			'BAPL:DATE','BAPL:PLAC',
			'BAPM:DATE','BAPM:PLAC',
			'BARM:DATE','BARM:PLAC',
			'BASM:DATE','BASM:PLAC',
			'BLES:DATE','BLES:PLAC',
			'BURI:DATE','BURI:PLAC',
			'CAST',
			'CENS:DATE','CENS:PLAC',
			'CHAN:DATE', 'CHAN:_WT_USER',
			'CHR:DATE','CHR:PLAC',
			'CREM:DATE','CREM:PLAC',
			'DSCR',
			'EMAIL',
			'EMIG:DATE','EMIG:PLAC',
			'ENDL:DATE','ENDL:PLAC',
			'EVEN', 'EVEN:TYPE', 'EVEN:DATE', 'EVEN:PLAC',
 			'FACT', 'FACT:TYPE',
 			'FAMS:CENS:DATE','FAMS:CENS:PLAC',
			'FAMS:DIV:DATE',
			'FAMS:NOTE',
			'FAMS:SLGS:DATE','FAMS:SLGS:PLAC',
			'FAX',
			'FCOM:DATE','FCOM:PLAC',
			'IMMI:DATE','IMMI:PLAC',
			'NAME:NICK','NAME:_MARNM','NAME:_HEB','NAME:ROMN',
			'NATI',
			'NATU:DATE','NATU:PLAC',
			'NOTE',
			'OCCU',
			'ORDN:DATE','ORDN:PLAC',
			'RELI',
			'RESI','RESI:DATE','RESI:PLAC',
			'SLGC:DATE','SLGC:PLAC',
			'TITL',
			'_BRTM:DATE','_BRTM:PLAC',
			'_MILI',
		);
		// Allow (some of) the user-specified fields to be selected
		preg_match_all('/(' . WT_REGEX_TAG . ')/', get_gedcom_setting(WT_GED_ID, 'INDI_FACTS_ADD'), $facts);
		foreach ($facts[1] as $fact) {
			if (
				$fact!='BIRT' &&
				$fact!='DEAT' &&
				$fact!='ASSO' &&
				!in_array($fact, $ofields) &&
				!in_array("{$fact}:DATE", $ofields) &&
				!in_array("{$fact}:PLAC", $ofields)
			) {
				$ofields[]=$fact;
			}
		}
		$fields = array();
		foreach ($ofields as $field) {
			$fields[$field] = WT_Gedcom_Tag::GetLabel($field);
		}
		uksort($fields, array('WT_Controller_Search', 'tagSort'));
		return $fields;
	}

	public static function tagSort($x, $y) {
		list($x1) = explode(':', $x.':');
		list($y1) = explode(':', $y.':');
		$tmp = utf8_strcasecmp(WT_Gedcom_Tag::getLabel($x1), WT_Gedcom_Tag::getLabel($y1));
		if ($tmp) {
			return $tmp;
		} else {
			return utf8_strcasecmp(WT_Gedcom_Tag::getLabel($x), WT_Gedcom_Tag::getLabel($y));
		}
	}

	function getValue($i) {
		$val = "";
		if (isset($this->values[$i])) $val = $this->values[$i];
		return $val;
	}

	function getField($i) {
		$val = "";
		if (isset($this->fields[$i])) $val = htmlentities($this->fields[$i]);
		return $val;
	}

	function getIndex($field) {
		return array_search($field, $this->fields);
	}

	function getLabel($tag) {
		return WT_Gedcom_Tag::getLabel(preg_replace('/:(SDX|BEGINS|EXACT|CONTAINS)$/', '', $tag));

	}

	function reorderFields() {
		$i = 0;
		$newfields = array();
		$newvalues = array();
		$newplus = array();
		$rels = array();
		foreach ($this->fields as $j=>$field) {
			if (strpos($this->fields[$j], "FAMC:HUSB:NAME") === 0 || strpos($this->fields[$j], "FAMC:WIFE:NAME") === 0) {
				$rels[$this->fields[$j]] = $this->values[$j];
				continue;
			}
			$newfields[$i] = $this->fields[$j];
			if (isset($this->values[$j])) $newvalues[$i] = $this->values[$j];
			if (isset($this->plusminus[$j])) $newplus[$i] = $this->plusminus[$j];
			$i++;
		}
		$this->fields = $newfields;
		$this->values = $newvalues;
		$this->plusminus = $newplus;
		foreach ($rels as $field=>$value) {
			$this->fields[] = $field;
			$this->values[] = $value;
		}
	}

	/**
	 * Handles searches entered in the top search box in the themes and
	 * prepares the search to do a general search on indi's, fams, sources and stories.
	 */
	function TopSearch() {
		// first set some required variables. Search only in current gedcom, only in indi's and stories attached to indi's.
		$this->srindi = "yes";
		$this->srstor = "yes";

		// Enable the default gedcom for search
		$str = str_replace(array (".", "-", " "), array ("_", "_", "_"), WT_GEDCOM);
		$_REQUEST["$str"] = "yes";

		// Then see if an ID is typed in. If so, we might want to jump there.
		if (isset ($this->query)) {
			$record = WT_GedcomRecord::getInstance($this->query);
			if ($record && $record->canDisplayDetails()) {
				header('Location: '. WT_SERVER_NAME . WT_SCRIPT_PATH.$record->getRawUrl());
				exit;
			}
		}
	}

	/**
	 * Gathers results for a general search
	 */
	function GeneralSearch() {
		// Split search terms into an array
		$query_terms = array();
		$query=$this->query;
		// Words in double quotes stay together
		while (preg_match('/"([^"]+)"/', $query, $match)) {
			$query_terms[]=trim($match[1]);
			$query = str_replace($match[0], '', $query);
		}
		// Other words get treated separately
		while (preg_match('/[\S]+/', $query, $match)) {
			$query_terms[]=trim($match[0]);
			$query = str_replace($match[0], '', $query);
		}

		//-- perform the search
		if ($query_terms && $this->sgeds) {
			// Write a log entry
			$logstring = "Type: General\nQuery: ".$this->query;
			AddToSearchlog($logstring, $this->sgeds);

			// Search the indi's
			if (isset ($this->srindi)) {
				$this->myindilist = search_indis($query_terms, array_keys($this->sgeds), 'AND');
			} else {
				$this->myindilist = array();
			}

			// Search the fams
			if (isset ($this->srfams)) {
				$this->myfamlist = array_merge(
					search_fams($query_terms, array_keys($this->sgeds), 'AND'),
					search_fams_names($query_terms, array_keys($this->sgeds), 'AND')
				);
				$this->myfamlist = array_unique($this->myfamlist);
			} else {
				$this->myfamlist = array();
			}

			// Search the sources
			if (isset ($this->srsour)) {
				if (!empty ($this->query))
				$this->mysourcelist = search_sources($query_terms, array_keys($this->sgeds), 'AND');
			} else {
				$this->mysourcelist = array();
			}

			// Search the notes
			if (isset ($this->srnote)) {
				if (!empty ($this->query))
				$this->mynotelist = search_notes($query_terms, array_keys($this->sgeds), 'AND');
			} else {
				$this->mynotelist = array();
			}

			// Search the stories
			if (isset ($this->srstor) && array_key_exists('stories', WT_Module::getActiveModules())) {
				if (!empty ($this->query))
				$this->mystorieslist = search_stories($query_terms, array_keys($this->sgeds), 'AND');
			} else {
				$this->mystorieslist = array();
			}

			// If only 1 item is returned, automatically forward to that item
			// If ID cannot be displayed, continue to the search page.
			if (count($this->myindilist) == 1 && !$this->myfamlist && !$this->mysourcelist && !$this->mynotelist && !$this->mystorieslist) {
				$indi = $this->myindilist[0];
				if ($indi->canDisplayName()) {
					Zend_Session::writeClose();
					header('Location: '. WT_SERVER_NAME . WT_SCRIPT_PATH.$indi->getRawUrl());
					exit;
				}
			}
			if (!$this->myindilist && count($this->myfamlist) == 1 && !$this->mysourcelist && !$this->mynotelist && !$this->mystorieslist) {
				$fam = $this->myfamlist[0];
				if ($fam->canDisplayName()) {
					Zend_Session::writeClose();
					header('Location: '. WT_SERVER_NAME . WT_SCRIPT_PATH.$fam->getRawUrl());
					exit;
				}
			}
			if (!$this->myindilist && !$this->myfamlist && count($this->mysourcelist) == 1 && !$this->mynotelist && !$this->mystorieslist) {
				$sour = $this->mysourcelist[0];
				if ($sour->canDisplayName()) {
					Zend_Session::writeClose();
					header('Location: '. WT_SERVER_NAME . WT_SCRIPT_PATH.$sour->getRawUrl());
					exit;
				}
			}
			if (!$this->myindilist && !$this->myfamlist && !$this->mysourcelist && count($this->mynotelist) == 1 && !$this->mystorieslist) {
				$note = $this->mynotelist[0];
				if ($note->canDisplayName()) {
					Zend_Session::writeClose();
					header('Location: '. WT_SERVER_NAME . WT_SCRIPT_PATH.$note->getRawUrl());
					exit;
				}
			}
			if (!$this->myindilist && !$this->myfamlist && !$this->mysourcelist && !$this->mynotelist && count($this->mystorieslist) == 1) {
				$story = $this->mystorieslist[0];
				$person = WT_Person::getInstance($story['xref']);
				if ($person->canDisplayName()) {
					Zend_Session::writeClose();
					header('Location: '. WT_SERVER_NAME . WT_SCRIPT_PATH. $person->getRawUrl() . '#stories');
					exit;
				}
			}
		}
	}

	/**
	 *  Preforms a search and replace
	 */
	function SearchAndReplace() {
		global $GEDCOM, $manual_save, $STANDARD_NAME_FACTS, $ADVANCED_NAME_FACTS;

		$this->sgeds = array(WT_GED_ID=>WT_GEDCOM);
		$this->srindi = "yes";
		$this->srfams = "yes";
		$this->srsour = "yes";
		$this->srnote = "yes";
		$oldquery = $this->query;
		$this->GeneralSearch();

		//-- don't try to make any changes if nothing was found
		if (!$this->myindilist && !$this->myfamlist && !$this->mysourcelist && !$this->mynotelist) {
			return;
		}

		AddToLog("Search And Replace old:".$oldquery." new:".$this->replace, 'edit');
		$manual_save = true;
		// Include edit functions.
		require_once WT_ROOT.'includes/functions/functions_edit.php';

		$adv_name_tags = preg_split("/[\s,;: ]+/", $ADVANCED_NAME_FACTS);
		$name_tags = array_unique(array_merge($STANDARD_NAME_FACTS, $adv_name_tags));
		$name_tags[] = '_MARNM';
		foreach ($this->myindilist as $id=>$individual) {
			$indirec = find_gedcom_record($individual->getXref(), WT_GED_ID, true);
			$oldRecord = $indirec;
			$newRecord = $indirec;
			if ($this->replaceAll) {
				$newRecord = preg_replace("~".$oldquery."~i", $this->replace, $newRecord);
			} else {
				if ($this->replaceNames) {
					foreach ($name_tags as $tag) {
						$newRecord = preg_replace("~(\d) ".$tag." (.*)".$oldquery."(.*)~i", "$1 ".$tag." $2".$this->replace."$3", $newRecord);
					}
				}
				if ($this->replacePlaces) {
					if ($this->replacePlacesWord) $newRecord = preg_replace('~(\d) PLAC (.*)([,\W\s])'.$oldquery.'([,\W\s])~i', "$1 PLAC $2$3".$this->replace."$4",$newRecord);
					else $newRecord = preg_replace("~(\d) PLAC (.*)".$oldquery."(.*)~i", "$1 PLAC $2".$this->replace."$3",$newRecord);
				}
			}
			//-- if the record changed replace the record otherwise remove it from the search results
			if ($newRecord != $oldRecord) {
				replace_gedrec($individual->getXref(), WT_GED_ID, $newRecord);
			} else {
				unset($this->myindilist[$id]);
			}
		}

		foreach ($this->myfamlist as $id=>$family) {
			$indirec = find_gedcom_record($family->getXref(), WT_GED_ID, true);
			$oldRecord = $indirec;
			$newRecord = $indirec;

			if ($this->replaceAll) {
				$newRecord = preg_replace("~".$oldquery."~i", $this->replace, $newRecord);
			}
			else {
				if ($this->replacePlaces) {
					if ($this->replacePlacesWord) $newRecord = preg_replace('~(\d) PLAC (.*)([,\W\s])'.$oldquery.'([,\W\s])~i', "$1 PLAC $2$3".$this->replace."$4",$newRecord);
					else $newRecord = preg_replace("~(\d) PLAC (.*)".$oldquery."(.*)~i", "$1 PLAC $2".$this->replace."$3",$newRecord);
				}
			}
			//-- if the record changed replace the record otherwise remove it from the search results
			if ($newRecord != $oldRecord) {
				replace_gedrec($family->getXref(), WT_GED_ID, $newRecord);
			} else {
				unset($this->myfamlist[$id]);
			}
		}

		foreach ($this->mysourcelist as $id=>$source) {
			$indirec = find_gedcom_record($source->getXref(), WT_GED_ID, true);
			$oldRecord = $indirec;
			$newRecord = $indirec;

			if ($this->replaceAll) {
				$newRecord = preg_replace("~".$oldquery."~i", $this->replace, $newRecord);
			} else {
				if ($this->replaceNames) {
					$newRecord = preg_replace("~(\d) TITL (.*)".$oldquery."(.*)~i", "$1 TITL $2".$this->replace."$3", $newRecord);
					$newRecord = preg_replace("~(\d) ABBR (.*)".$oldquery."(.*)~i", "$1 ABBR $2".$this->replace."$3", $newRecord);
				}
				if ($this->replacePlaces) {
					if ($this->replacePlacesWord) $newRecord = preg_replace('~(\d) PLAC (.*)([,\W\s])'.$oldquery.'([,\W\s])~i', "$1 PLAC $2$3".$this->replace."$4",$newRecord);
					else $newRecord = preg_replace("~(\d) PLAC (.*)".$oldquery."(.*)~i", "$1 PLAC $2".$this->replace."$3",$newRecord);
				}
			}
			//-- if the record changed replace the record otherwise remove it from the search results
			if ($newRecord != $oldRecord) {
				replace_gedrec($source->getXref(), WT_GED_ID, $newRecord);
			} else {
				unset($this->mysourcelist[$id]);
			}
		}

		foreach ($this->mynotelist as $id=>$note) {
			$indirec = find_gedcom_record($note->getXref(), WT_GED_ID, true);
			$oldRecord = $indirec;
			$newRecord = $indirec;

			if ($this->replaceAll) {
				$newRecord = preg_replace("~".$oldquery."~i", $this->replace, $newRecord);
			}
			//-- if the record changed replace the record otherwise remove it from the search results
			if ($newRecord != $oldRecord) {
				replace_gedrec($note->getXref(), WT_GED_ID, $newRecord);
			} else {
				unset($this->mynotelist[$id]);
			}
		}
	}

	/**
	 *  Gathers results for a soundex search
	 *
	 *  TODO
	 *  ====
	 *  Does not search on the selected gedcoms, searches on all the gedcoms
	 *  Does not work on first names, instead of the code, value array is used in the search
	 *  Returns all the names even when Names with hit selected
	 *  Does not sort results by first name
	 *  Does not work on separate double word surnames
	 *  Does not work on duplicate code values of the searched text and does not give the correct code
	 *     Cohen should give DM codes 556000, 456000, 460000 and 560000, in 4.1 we search only on 560000??
	 *
	 *  The names' Soundex SQL table contains all the soundex values twice
	 *  The places table contains only one value
	 *
	 *  The code should be improved - see RFE
	 *
	 */
	function SoundexSearch() {
		if (((!empty ($this->lastname)) || (!empty ($this->firstname)) || (!empty ($this->place))) && (count($this->sgeds) > 0)) {
			$logstring = "Type: Soundex\n";
			if (!empty ($this->lastname))
			$logstring .= "Last name: ".$this->lastname."\n";
			if (!empty ($this->firstname))
			$logstring .= "First name: ".$this->firstname."\n";
			if (!empty ($this->place))
			$logstring .= "Place: ".$this->place."\n";
			if (!empty ($this->year))
			$logstring .= "Year: ".$this->year."\n";
			AddToSearchlog($logstring, $this->sgeds);

			if ($this->sgeds) {
				$this->myindilist = search_indis_soundex($this->soundex, $this->lastname, $this->firstname, $this->place, array_keys($this->sgeds));
			} else {
				$this->myindilist = array();
			}
		}

		// Now we have the final list of indi's to be printed.
		// We may add the assos at this point.

		if ($this->showasso == "on") {
			foreach ($this->myindilist as $indi) {
				foreach (fetch_linked_indi($indi->getXref(), 'ASSO', $indi->getGedId()) as $asso) {
					$this->myindilist[]=$asso;
				}
				foreach (fetch_linked_fam($indi->getXref(), 'ASSO', $indi->getGedId()) as $asso) {
					$this->myfamlist[]=$asso;
				}
			}
		}

		//-- if only 1 item is returned, automatically forward to that item
		if (count($this->myindilist) == 1 && $this->action!="replace") {
			reset($this->myindilist);
			header('Location: '. WT_SERVER_NAME . WT_SCRIPT_PATH.$indi->getRawUrl());
			exit;
		}
		usort($this->myindilist, array('WT_GedcomRecord', 'Compare'));
		usort($this->myfamlist, array('WT_GedcomRecord', 'Compare'));
	}

	function AdvancedSearch ($justSql = false, $table = "individuals") {
		$this->myindilist = array ();
		$fct = count($this->fields);
		if ($fct == 0) {
			return;
		}

		// Dynamic SQL query, plus bind variables
		$sql	= "SELECT DISTINCT 'INDI' AS type, ind.i_id AS xref, ind.i_file AS ged_id, ind.i_gedcom AS gedrec FROM `##individuals` ind";
		$bind	= array();

		// Join the following tables
		$father_name     = false;
		$mother_name     = false;
		$spouse_family   = false;
		$indi_name       = false;
		$indi_date       = false;
		$fam_date        = false;
		$indi_plac       = false;
		$fam_plac        = false;

		foreach ($this->fields as $n=>$field) {
			if ($this->values[$n]) {
				if (substr($field, 0, 14) == 'FAMC:HUSB:NAME') {
					$father_name = true;
				} elseif (substr($field, 0, 14) == 'FAMC:WIFE:NAME') {
					$mother_name = true;
				} elseif (substr($field, 0, 4) == 'NAME') {
					$indi_name = true;
				} elseif (strpos($field, ':DATE')!==false) {
					if (substr($field, 0, 4) == 'FAMS') {
						$fam_date = true;
						$spouse_family = true;
					} else {
						$indi_date = true;
					}
				} elseif (strpos($field, ':PLAC')!==false) {
					if (substr($field, 0, 4) == 'FAMS') {
						$fam_plac = true;
						$spouse_family = true;
					} else {
						$indi_plac = true;
					}
				} elseif ($field == 'FAMS:NOTE') {
					$spouse_family = true;
				}
			}
		}

		if ($father_name || $mother_name) {
			$sql .= " JOIN `##link`   l_1 ON (l_1.l_file=ind.i_file AND l_1.l_from=ind.i_id AND l_1.l_type='FAMC')";
		}
		if ($father_name) {
			$sql .= " JOIN `##link`   l_2 ON (l_2.l_file=ind.i_file AND l_2.l_from=l_1.l_to AND l_2.l_type='HUSB')";
			$sql .= " JOIN `##name`   f_n ON (f_n.n_file=ind.i_file AND f_n.n_id  =l_2.l_to)";
		}
		if ($mother_name) {
			$sql .= " JOIN `##link`   l_3 ON (l_3.l_file=ind.i_file AND l_3.l_from=l_1.l_to AND l_3.l_type='WIFE')";
			$sql .= " JOIN `##name`   m_n ON (m_n.n_file=ind.i_file AND m_n.n_id  =l_3.l_to)";
		}
		if ($spouse_family) {
			$sql .= " JOIN `##link`     l_4 ON (l_4.l_file=ind.i_file AND l_4.l_from=ind.i_id AND l_4.l_type='FAMS')";
			$sql .= " JOIN `##families` fam ON (fam.f_file=ind.i_file AND fam.f_id  =l_4.l_to)";
		}
		if ($indi_name) {
			$sql .= " JOIN `##name`   i_n ON (i_n.n_file=ind.i_file AND i_n.n_id=ind.i_id)";
		}
		if ($indi_date) {
			$sql .= " JOIN `##dates`  i_d ON (i_d.d_file=ind.i_file AND i_d.d_gid=ind.i_id)";
		}
		if ($fam_date) {
			$sql .= " JOIN `##dates`  f_d ON (f_d.d_file=ind.i_file AND f_d.d_gid=fam.f_id)";
		}
		if ($indi_plac) {
			$sql .= " JOIN `##placelinks`   i_pl ON (i_pl.pl_file=ind.i_file AND i_pl.pl_gid =ind.i_id)";
			$sql .= " JOIN (".
					"SELECT CONCAT_WS(', ', p1.p_place, p2.p_place, p3.p_place, p4.p_place, p5.p_place, p6.p_place, p7.p_place, p8.p_place, p9.p_place) AS place, p1.p_id AS id, p1.p_file AS file".
					" FROM      `##places` AS p1".
					" LEFT JOIN `##places` AS p2 ON (p1.p_parent_id=p2.p_id)".
					" LEFT JOIN `##places` AS p3 ON (p2.p_parent_id=p3.p_id)".
					" LEFT JOIN `##places` AS p4 ON (p3.p_parent_id=p4.p_id)".
					" LEFT JOIN `##places` AS p5 ON (p4.p_parent_id=p5.p_id)".
					" LEFT JOIN `##places` AS p6 ON (p5.p_parent_id=p6.p_id)".
					" LEFT JOIN `##places` AS p7 ON (p6.p_parent_id=p7.p_id)".
					" LEFT JOIN `##places` AS p8 ON (p7.p_parent_id=p8.p_id)".
					" LEFT JOIN `##places` AS p9 ON (p8.p_parent_id=p9.p_id)".
					") AS i_p ON (i_p.file  =ind.i_file AND i_pl.pl_p_id= i_p.id)";
		}
		if ($fam_plac) {
			$sql .= " JOIN `##placelinks`   f_pl ON (f_pl.pl_file=ind.i_file AND f_pl.pl_gid =fam.f_id)";
			$sql .= " JOIN (".
					"SELECT CONCAT_WS(', ', p1.p_place, p2.p_place, p3.p_place, p4.p_place, p5.p_place, p6.p_place, p7.p_place, p8.p_place, p9.p_place) AS place, p1.p_id AS id, p1.p_file AS file".
					" FROM      `##places` AS p1".
					" LEFT JOIN `##places` AS p2 ON (p1.p_parent_id=p2.p_id)".
					" LEFT JOIN `##places` AS p3 ON (p2.p_parent_id=p3.p_id)".
					" LEFT JOIN `##places` AS p4 ON (p3.p_parent_id=p4.p_id)".
					" LEFT JOIN `##places` AS p5 ON (p4.p_parent_id=p5.p_id)".
					" LEFT JOIN `##places` AS p6 ON (p5.p_parent_id=p6.p_id)".
					" LEFT JOIN `##places` AS p7 ON (p6.p_parent_id=p7.p_id)".
					" LEFT JOIN `##places` AS p8 ON (p7.p_parent_id=p8.p_id)".
					" LEFT JOIN `##places` AS p9 ON (p8.p_parent_id=p9.p_id)".
					") AS f_p ON (f_p.file  =ind.i_file AND f_pl.pl_p_id= f_p.id)";
		}
		// Add the where clause
		$sql	.= " WHERE ind.i_file=?";
		$bind[] = WT_GED_ID;
		$dfct = 0; // count date values entered
		$pfct = 0; // count place values entered

		for ($i = 0; $i<$fct; $i++) {
			$field = $this->fields[$i];
			$value = $this->values[$i];
			if ($value === '') continue;
			$parts = preg_split("/:/", $field . '::::');
			if ($dfct > 0 && $parts[1] == 'DATE' && $value != '') {
				$sql .= " AND i_d.d_gid IN (
				   SELECT i_d.d_gid
				   FROM `##individuals` ind
				   JOIN `##dates` i_d ON (i_d.d_file=ind.i_file AND i_d.d_gid=ind.i_id)
				   WHERE ind.i_file=?";
				   $bind[] = WT_GED_ID;
			}

			if ($pfct > 0 && $parts[1] == 'PLAC' && $value != '') {
				$sql .= " AND i_pl.pl_gid IN (
					SELECT i_pl.pl_gid
					FROM `kt_individuals` ind
					JOIN `kt_placelinks` i_pl ON (i_pl.pl_file=ind.i_file AND i_pl.pl_gid=ind.i_id)
					JOIN (
						SELECT CONCAT_WS(', ', p1.p_place, p2.p_place, p3.p_place, p4.p_place, p5.p_place, p6.p_place, p7.p_place, p8.p_place, p9.p_place) AS place, p1.p_id AS id, p1.p_file AS file
						FROM `kt_places` AS p1
						LEFT JOIN `kt_places` AS p2 ON (p1.p_parent_id=p2.p_id)
						LEFT JOIN `kt_places` AS p3 ON (p2.p_parent_id=p3.p_id)
						LEFT JOIN `kt_places` AS p4 ON (p3.p_parent_id=p4.p_id)
						LEFT JOIN `kt_places` AS p5 ON (p4.p_parent_id=p5.p_id)
						LEFT JOIN `kt_places` AS p6 ON (p5.p_parent_id=p6.p_id)
						LEFT JOIN `kt_places` AS p7 ON (p6.p_parent_id=p7.p_id)
						LEFT JOIN `kt_places` AS p8 ON (p7.p_parent_id=p8.p_id)
						LEFT JOIN `kt_places` AS p9 ON (p8.p_parent_id=p9.p_id)
					) AS i_p ON (i_p.file =ind.i_file AND i_pl.pl_p_id= i_p.id)
					WHERE ind.i_file=?";
				   $bind[] = WT_GED_ID;
			}

			if ($parts[0] == 'NAME') {
				// NAME:*
				switch ($parts[1]) {
				case 'GIVN':
					switch ($parts[2]) {
					case 'EXACT':
						$sql .= " AND i_n.n_givn=?";
						$bind[]=$value;
						break;
					case 'BEGINS':
						$sql .= " AND i_n.n_givn LIKE CONCAT(?, '%')";
						$bind[]=$value;
						break;
					case 'CONTAINS':
						$sql .= " AND i_n.n_givn LIKE CONCAT('%', ?, '%')";
						$bind[]=$value;
						break;
					case 'SDX_STD':
						$sdx = explode(':', WT_Soundex::soundex_std($value));
						foreach ($sdx as $k=>$v) {
							$sdx[$k]="i_n.n_soundex_givn_std LIKE CONCAT('%', ?, '%')";
							$bind[]=$v;
						}
						$sql.=' AND ('.implode(' OR ', $sdx).')';
						break;
					case 'SDX': // SDX uses DM by default.
					case 'SDX_DM':
						$sdx = explode(':', WT_Soundex::soundex_dm($value));
						foreach ($sdx as $k=>$v) {
							$sdx[$k]="i_n.n_soundex_givn_dm LIKE CONCAT('%', ?, '%')";
							$bind[]=$v;
						}
						$sql.=' AND ('.implode(' OR ', $sdx).')';
						break;
					}
					break;
				case 'SURN':
					switch ($parts[2]) {
					case 'EXACT':
						$sql .= " AND i_n.n_surname=?";
						$bind[]=$value;
						break;
					case 'BEGINS':
						$sql .= " AND i_n.n_surname LIKE CONCAT(?, '%')";
						$bind[]=$value;
						break;
					case 'CONTAINS':
						$sql .= " AND i_n.n_surname LIKE CONCAT('%', ?, '%')";
						$bind[]=$value;
						break;
					case 'SDX_STD':
						$sdx = explode(':', WT_Soundex::soundex_std($value));
						foreach ($sdx as $k=>$v) {
							$sdx[$k]="i_n.n_soundex_surn_std LIKE CONCAT('%', ?, '%')";
							$bind[]=$v;
						}
						$sql .= " AND (".implode(' OR ', $sdx).")";
						break;
					case 'SDX': // SDX uses DM by default.
					case 'SDX_DM':
						$sdx = explode(':', WT_Soundex::soundex_dm($value));
						foreach ($sdx as $k=>$v) {
							$sdx[$k]="i_n.n_soundex_surn_dm LIKE CONCAT('%', ?, '%')";
							$bind[]=$v;
						}
						$sql .= " AND (".implode(' OR ', $sdx).")";
						break;
					}
					break;
				case 'NICK':
				case '_MARNM':
				case '_HEB':
				case '_AKA':
					$sql .= " AND i_n.n_type=? AND i_n.n_full LIKE CONCAT('%', ?, '%')";
					$bind[]=$parts[1];
					$bind[]=$value;
					break;
				}
			} elseif ($parts[1] == 'DATE') {
				$dfct ++;
				// *:DATE
				$date = new WT_Date($value);
				if ($date->isOK()) {
					$jd1 = $date->date1->minJD;
					if ($date->date2) {
						$jd2 = $date->date2->maxJD;
					} else {
						$jd2 = $date->date1->maxJD;
					}
					if (!empty($this->plusminus[$i]) && $this->plusminus[$i] != 'BEF' && $this->plusminus[$i] != 'AFT') {
						$adjd = $this->plusminus[$i] * 365;
						$jd1 = $jd1 - $adjd;
						$jd2 = $jd2 + $adjd;
					}
					if ($this->plusminus[$i] == 'BEF') {
						$sql .= " AND i_d.d_fact=? AND i_d.d_julianday1<? AND i_d.d_julianday2<?";
					} elseif ($this->plusminus[$i] == 'AFT') {
						$sql .= " AND i_d.d_fact=? AND i_d.d_julianday1>? AND i_d.d_julianday2>?";
					} else {
						$sql .= " AND i_d.d_fact=? AND i_d.d_julianday1>=? AND i_d.d_julianday2<=?";
					}
					$bind[]=$parts[0];
					$bind[]=$jd1;
					$bind[]=$jd2;
				}
			} elseif ($parts[0] == 'FAMS' && $parts[2] == 'DATE') {
				$dfct ++;
				// FAMS:*:DATE
				$date = new WT_Date($value);
				if ($date->isOK()) {
					$jd1 = $date->date1->minJD;
					if ($date->date2) $jd2 = $date->date2->maxJD;
					else $jd2 = $date->date1->maxJD;
					if (!empty($this->plusminus[$i])) {
						$adjd = $this->plusminus[$i]*365;
						$jd1 = $jd1 - $adjd;
						$jd2 = $jd2 + $adjd;
					}
					$sql .= " AND f_d.d_fact=? AND f_d.d_julianday1>=? AND f_d.d_julianday2<=?";
					$bind[]=$parts[1];
					$bind[]=$jd1;
					$bind[]=$jd2;
				}
			} elseif ($parts[1] == 'PLAC') {
				$pfct ++;
				// *:PLAC
				// SQL can only link a place to a person/family, not to an event.
				$sql .= " AND i_p.place LIKE CONCAT('%', ?, '%')";
				$bind[]=$value;
			} elseif ($parts[0] == 'FAMS' && $parts[2] == 'PLAC') {
				$pfct ++;
				// FAMS:*:PLAC
				// SQL can only link a place to a person/family, not to an event.
				$sql .= " AND f_p.place LIKE CONCAT('%', ?, '%')";
				$bind[]=$value;
			} elseif ($parts[0] == 'FAMC' && $parts[2] == 'NAME') {
				$table=$parts[1] == 'HUSB' ? 'f_n' : 'm_n';
				// NAME:*
				switch ($parts[3]) {
				case 'GIVN':
					switch ($parts[4]) {
					case 'EXACT':
						$sql .= " AND {$table}.n_givn=?";
						$bind[]=$value;
						break;
					case 'BEGINS':
						$sql .= " AND {$table}.n_givn LIKE CONCAT(?, '%')";
						$bind[]=$value;
						break;
					case 'CONTAINS':
						$sql .= " AND {$table}.n_givn LIKE CONCAT('%', ?, '%')";
						$bind[]=$value;
						break;
					case 'SDX_STD':
						$sdx = explode(':', WT_Soundex::soundex_std($value));
						foreach ($sdx as $k=>$v) {
							$sdx[$k]="{$table}.n_soundex_givn_std LIKE CONCAT('%', ?, '%')";
							$bind[]=$v;
						}
						$sql.=' AND ('.implode(' OR ', $sdx).')';
						break;
					case 'SDX': // SDX uses DM by default.
					case 'SDX_DM':
						$sdx = explode(':', WT_Soundex::soundex_dm($value));
						foreach ($sdx as $k=>$v) {
							$sdx[$k]="{$table}.n_soundex_givn_dm LIKE CONCAT('%', ?, '%')";
							$bind[]=$v;
						}
						$sql.=' AND ('.implode(' OR ', $sdx).')';
						break;
					}
					break;
				case 'SURN':
					switch ($parts[4]) {
					case 'EXACT':
						$sql .= " AND {$table}.n_surname=?";
						$bind[]=$value;
						break;
					case 'BEGINS':
						$sql .= " AND {$table}.n_surname LIKE CONCAT(?, '%')";
						$bind[]=$value;
						break;
					case 'CONTAINS':
						$sql .= " AND {$table}.n_surname LIKE CONCAT('%', ?, '%')";
						$bind[]=$value;
						break;
					case 'SDX_STD':
						$sdx = explode(':', WT_Soundex::soundex_std($value));
						foreach ($sdx as $k=>$v) {
							$sdx[$k]="{$table}.n_soundex_surn_std LIKE CONCAT('%', ?, '%')";
							$bind[]=$v;
						}
						$sql.=' AND ('.implode(' OR ', $sdx).')';
						break;
					case 'SDX': // SDX uses DM by default.
					case 'SDX_DM':
						$sdx = explode(':', WT_Soundex::soundex_dm($value));
						foreach ($sdx as $k=>$v) {
							$sdx[$k]="{$table}.n_soundex_surn_dm LIKE CONCAT('%', ?, '%')";
							$bind[]=$v;
						}
						$sql.=' AND ('.implode(' OR ', $sdx).')';
						break;
					}
					break;
				}
			} elseif ($parts[0] === 'FAMS') {
				// e.g. searches for occupation, religion, note, etc.
				$sql .= " AND fam.f_gedcom REGEXP CONCAT('\n[0-9] ', ?, '(.*\n[0-9] CONT)* [^\n]*', ?)";
				$bind[]=$parts[1];
				$bind[]=$value;
			} elseif ($parts[1] === 'TYPE') {
 				// e.g. FACT:TYPE or EVEN:TYPE
 				$sql .= " AND ind.i_gedcom REGEXP CONCAT('\n1 ', ?, '.*(\n[2-9] .*)*\n2 TYPE .*', ?)";
 				$bind[] = $parts[0];
 				$bind[] = $value;
  			} else {
				// e.g. searches for occupation, religion, note, etc.
				$sql .= " AND ind.i_gedcom REGEXP CONCAT('\n[0-9] ', ?, '(.*\n[0-9] CONT)* [^\n]*', ?)";
				$bind[]=$parts[0];
				$bind[]=$value;
			}

			if ($dfct > 1 || $pfct > 1) {
				$sql .= " )";
			}
		}

		$rows = WT_DB::prepare($sql)->execute($bind)->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows as $row) {
			$person = WT_Person::getInstance($row);
			// Check for XXXX:PLAC fields, which were only partially matched by SQL
			foreach ($this->fields as $n=>$field) {
				if ($this->values[$n] && preg_match('/^('.WT_REGEX_TAG.'):PLAC$/', $field, $match)) {
					if (!preg_match('/\n1 '.$match[1].'(\n[2-9].*)*\n2 PLAC .*'.preg_quote($this->values[$n], '/').'/i', $person->getGedcomRecord())) {
						continue 2;
				 }
				}
			}
			$this->myindilist[]= $person;
		}
		return $this->myindilist;

	}

	function printResults() {
		require_once WT_ROOT.'includes/functions/functions_print_lists.php';
		global $GEDCOM;
		$somethingPrinted = false; // whether anything printed
		// ---- section to search and display results on a general keyword search
		if ($this->action == "general" || $this->action == "soundex" || $this->action == "replace" || $this->action == "advanced") {
			if ($this->myindilist || $this->myfamlist || $this->mysourcelist || $this->mynotelist || $this->mystorieslist) {
				$this->addInlineJavascript('jQuery("#search-result-tabs").tabs();');
				$this->addInlineJavascript('jQuery("#search-result-tabs").css("visibility", "visible");');
				$this->addInlineJavascript('jQuery(".loading-image").css("display", "none");');
				echo '<br>';
				echo '<div class="loading-image">&nbsp;</div>';
				echo '<div id="search-result-tabs"><ul>';
				if ($this->myindilist) {echo '<li><a href="#searchAccordion-indi"><span id="indisource">', WT_I18N::translate('Individuals'), '</span></a></li>';}
				if ($this->myfamlist) {echo '<li><a href="#searchAccordion-fam"><span id="famsource">', WT_I18N::translate('Families'), '</span></a></li>';}
				if ($this->mysourcelist) {echo '<li><a href="#searchAccordion-source"><span id="mediasource">', WT_I18N::translate('Sources'), '</span></a></li>';}
				if ($this->mynotelist) {echo '<li><a href="#searchAccordion-note"><span id="notesource">', WT_I18N::translate('Notes'), '</span></a></li>';}
				if ($this->mystorieslist) {echo '<li><a href="#searchAccordion-story"><span id="storysource">', WT_I18N::translate('Stories'), '</span></a></li>';}
				echo '</ul>';

				// individual results
				echo '<div id="searchAccordion-indi">';
					if ($this->action == "advanced") {
						uasort($this->myindilist, array('WT_GedcomRecord', 'Compare'));
						echo format_indi_table($this->myindilist);
					} else {
						// Split individuals by tree
						$trees = WT_Tree::getAll();
						foreach ($this->sgeds as $ged_id=>$gedcom) {
							$datalist = array();
							foreach ($this->myindilist as $individual) {
								if ($individual->getGedId() == $ged_id) {
									$datalist[] = $individual;
								}
							}
							if ($datalist) {
								$somethingPrinted = true;
								usort($datalist, array('WT_GedcomRecord', 'Compare'));
								$GEDCOM = $gedcom;
								load_gedcom_settings($ged_id);
								echo '<h3 class="indi-acc-header"><a href="#"><span class="search_item" dir="auto">', $this->myquery, '</span> @ <span>', $trees[$ged_id]->tree_title_html, '</span></a></h3>
									<div class="indi-acc_content">',
									format_indi_table($datalist);
								echo '</div>';//indi-acc_content
							}
						}
					}
				echo '</div>';//#searchAccordion-indi
				$this->addInlineJavascript('jQuery("#searchAccordion-indi").accordion({heightStyle: "content", collapsible: true});');

				// family results
				echo '<div id="searchAccordion-fam">';
					// Split families by gedcom
					foreach ($this->sgeds as $ged_id=>$gedcom) {
						$datalist = array();
						foreach ($this->myfamlist as $family) {
							if ($family->getGedId() == $ged_id) {
								$datalist[]=$family;
							}
						}
						if ($datalist) {
							$somethingPrinted = true;
							usort($datalist, array('WT_GedcomRecord', 'Compare'));
							$GEDCOM = $gedcom;
							load_gedcom_settings($ged_id);
							echo '<h3 class="fam-acc-header"><a href="#"><span class="search_item" dir="auto">', $this->myquery, '</span> @ <span>', $trees[$ged_id]->tree_title_html, '</span></a></h3>
								<div class="fam-acc_content">',
								format_fam_table($datalist);
							echo '</div>';//fam-acc_content
						}
					}
				echo '</div>';//#searchAccordion-fam
				$this->addInlineJavascript('jQuery("#searchAccordion-fam").accordion({heightStyle: "content", collapsible: true});');

				// source results
				echo '<div id="searchAccordion-source">';
					// Split sources by gedcom
					foreach ($this->sgeds as $ged_id=>$gedcom) {
						$datalist = array();
						foreach ($this->mysourcelist as $source) {
							if ($source->getGedId() == $ged_id) {
								$datalist[]=$source;
							}
						}
						if ($datalist) {
							$somethingPrinted = true;
							usort($datalist, array('WT_GedcomRecord', 'Compare'));
							$GEDCOM = $gedcom;
							load_gedcom_settings($ged_id);
							echo '<h3 class="source-acc-header"><a href="#"><span class="search_item" dir="auto">', $this->myquery, '</span> @ <span>', $trees[$ged_id]->tree_title_html, '</span></a></h3>
								<div class="source-acc_content">',
								format_sour_table($datalist);
							echo '</div>';//fam-acc_content
						}
					}
				echo '</div>';//#searchAccordion-source
				$this->addInlineJavascript('jQuery("#searchAccordion-source").accordion({heightStyle: "content", collapsible: true});');

				// note results
				echo '<div id="searchAccordion-note">';
					// Split notes by gedcom
					foreach ($this->sgeds as $ged_id=>$gedcom) {
						$datalist = array();
						foreach ($this->mynotelist as $note) {
							if ($note->getGedId() == $ged_id) {
								$datalist[]=$note;
							}
						}
						if ($datalist) {
							$somethingPrinted = true;
							usort($datalist, array('WT_GedcomRecord', 'Compare'));
							$GEDCOM = $gedcom;
							load_gedcom_settings($ged_id);
							echo '<h3 class="note-acc-header"><a href="#"><span class="search_item" dir="auto">', $this->myquery, '</span> @ <span>', $trees[$ged_id]->tree_title_html, '</span></a></h3>
								<div class="note-acc_content">',
								format_note_table($datalist);
							echo '</div>';//note-acc_content
						}
					}
				echo '</div>';//#searchAccordion-note
				$this->addInlineJavascript('jQuery("#searchAccordion-note").accordion({heightStyle: "content", collapsible: true});');

				// stories results
				echo '<div id="searchAccordion-story">';
				// Split stories by gedcom
				foreach ($this->sgeds as $ged_id=>$gedcom) {
					$datalist = array();
					foreach ($this->mystorieslist as $story) {
						if ($story['ged_id'] == $ged_id) {
							$datalist[] = $story['block_id'];
						}
					}
					if ($datalist) {
						$somethingPrinted = true;
						echo '<h3 class="story-acc-header"><a href="#"><span class="search_item" dir="auto">', $this->myquery, '</span> @ <span>', $trees[$ged_id]->tree_title_html, '</span></a></h3>
							<div class="story-acc_content">',
							format_story_table($datalist);
						echo '</div>';//story-acc_content
					}
				}
				echo '</div>';//#searchAccordion-story
				$this->addInlineJavascript('jQuery("#searchAccordion-story").accordion({heightStyle: "content", collapsible: true});');

				$GEDCOM = WT_GEDCOM;
				load_gedcom_settings(WT_GED_ID);
				echo '</div>'; //#search-result-tabs
			} elseif (isset ($this->query)) {
				echo '<br><div class="warning center"><em>'.WT_I18N::translate('No results found.').'</em><br>';
				if (!isset ($this->srindi) && !isset ($this->srfams) && !isset ($this->srsour) && !isset ($this->srnote)) {
					echo '<em>'.WT_I18N::translate('Be sure to select an option to search for.').'</em><br>';
				}
				echo '</div>';
			}
		}
		return $somethingPrinted; // whether anything printed
	}

}
