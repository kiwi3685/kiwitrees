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

#[AllowDynamicProperties]
class KT_Event {
// These objects need further refinement in their implementations and parsing
// var $address = null;
// var $notes = array(); //[0..*]: string
// var $sourceCitations = array(); //[0..*]: SourceCitation
// var $multimediaLinks = array(); //[0..*]: MultimediaLink

	var $lineNumber = null;
	var $canShow = null;
	var $state = "";
	var $type = NULL;
	var $tag = NULL;
	var $date = NULL;
	var $place = null;
	var $gedcomRecord = null;
	var $resn = null;
	var $dest = false;
	var $label = null;
	var $parentObject = null;
	var $detail = NULL;
	var $values = NULL;
	var $sortOrder = 0;
	var $sortDate = NULL;
	//-- temporary state variable that can be used by other scripts
	var $temp = NULL;

	// Is this an old/new pending record?
	private $is_old = false;
	private $is_new = false;

	// For family facts on individual pages - who is the significant spouse.
	private $spouse;

	/**
	 * Get the value for the first given GEDCOM tag
	 *
	 * @param string $code
	 * @return string
	 */
	function getValue($code) {
		if (is_null($this->values)) {
			$this->values = array();
			preg_match_all('/\n2 (' . KT_REGEX_TAG . ') (.+)/', $this->gedcomRecord, $matches, PREG_SET_ORDER);
			foreach ($matches as $match) {
				// If this is a link, remove the "@"
				if (preg_match('/^@' . KT_REGEX_XREF . '@$/', $match[2])) {
					$this->values[$match[1]] = trim($match[2], "@");
				} else {
					$this->values[$match[1]] = $match[2];
				}
			}
		}
		if (array_key_exists($code, $this->values)) {
			return $this->values[$code];
		}
		return null;
	}

	// Create an event objects from a gedcom fragment.
	// We also need to know the parent (to check privacy, etc.) and
	// the line number (from the original, privacy-filtered) gedcom
	// record, to allow editing
	function __construct($subrecord, $parent, $lineNumber) {
		if (preg_match('/^1 ('.KT_REGEX_TAG.') ?(.*)((\n2 CONT.*)*)/', $subrecord, $match)) {
			$this->tag   =$match[1];
			$this->detail=$match[2];
			// Some detail records contain multiple lines
			if ($match[3]) {
				$this->detail.=str_replace(array("\n2 CONT ", "\n2 CONT"), "\n", $match[3]);
			}
		} else {
			// We are not ready for this yet.
			// throw new Exception('Invalid GEDCOM data passed to KT_Event::_construct('.$subrecord.')');
		}
		$this->gedcomRecord = $subrecord;
		$this->parentObject = $parent;
		$this->lineNumber   = $lineNumber;
	}

	function setState($s) {
		$this->state = $s;
	}

	function getState() {
		return $this->state;
	}

	/**
	 * Get the value of level 2 data in the fact
	 *
	 * @param string $tag
	 *
	 * @return string|null
	 */
	public function getAttribute($tag) {
		if (preg_match('/\n2 (?:' . $tag . ') ?(.*(?:(?:\n3 CONT ?.*)*)*)/', $this->gedcomRecord, $match)) {
			return preg_replace("/\n3 CONT ?/", "\n", $match[1]);
		} else {
			return null;
		}
	}

	/**
	 * Check whether or not this event can be shown
	 *
	 * @return boolean
	 */
	function canShow() {
		if (is_null($this->canShow)) {
			if (empty($this->gedcomRecord)) {
				$this->canShow = false;
			} elseif (!is_null($this->parentObject)) {
				$this->canShow = canDisplayFact($this->parentObject->getXref(), $this->parentObject->getGedId(), $this->gedcomRecord);
			} else {
				$this->canShow = true;
			}
		}
		return $this->canShow;
	}

	// Check whether this fact is protected against edit
	public function canEdit() {
		// Managers can edit anything
		// Members cannot edit RESN, CHAN and locked records
		return
			$this->parentObject && $this->parentObject->canEdit() && (
				KT_USER_GEDCOM_ADMIN ||
				KT_USER_CAN_EDIT && strpos($this->gedcomRecord, "\n2 RESN locked")===false && $this->getTag()!='RESN' && $this->getTag()!='CHAN'
			);
	}

	/**
	 * The 4 character event type specified by GEDCom.
	 *
	 * @return string
	 */
	function getType() {
		if (is_null($this->type))
			$this->type=$this->getValue('TYPE');
		return $this->type;
	}

	/**
	 * The place where the event occured.
	 *
	 * @return string
	 */
	function getPlace() {
		if (is_null($this->place)) {
			$this->place=$this->getValue('PLAC');
		}
		return $this->place;
	}

	// For family facts on individual pages, we need to know the spouse
	public function setSpouse(KT_Person $spouse=null) {
		$this->spouse=$spouse;
	}
	public function getSpouse() {
		return $this->spouse;
	}

	// We can call this function many times, especially when sorting,
	// so keep a copy of the date.
	function getDate() {
		if ($this->date===null) {
			$this->date=new KT_Date($this->getValue('DATE'));
		}

		return $this->date;
	}

	/**
	 * The remaining unparsed GEDCom record
	 *
	 * @return string
	 */
	function getGedcomRecord() {
		return $this->gedcomRecord;
	}

	/**
	 * The line number, or line of occurrence in the GEDCom record.
	 *
	 * @return unknown
	 */
	function getLineNumber() {
		return $this->lineNumber;
	}

	/**
	 *
	 */
	function getTag() {
		return $this->tag;
	}

	/**
	 * The Person/Family record where this KT_Event came from
	 *
	 * @return GedcomRecord
	 */
	function getParentObject() {
		return $this->parentObject;
	}

	/**
	 *
	 */
	function getDetail() {
		return $this->detail;
	}

	function getLabel($abbreviate=false) {
		if ($abbreviate) {
			return KT_Gedcom_Tag::getAbbreviation($this->tag);
		} else {
			switch($this->tag) {
			case 'EVEN':
			case 'FACT':
				if ($this->getType()) {
					// Custom FACT/EVEN - with a TYPE
					return KT_I18N::translate(htmlspecialchars((string) $this->type));
				}
				// no break - drop into next case
			default:
				return KT_Gedcom_Tag::getLabel($this->tag, $this->parentObject);
			}
		}
	}

	public function setIsOld() {
		$this->is_old=true;
		$this->is_new=false;
	}
	public function getIsOld() {
		return $this->is_old;
	}
	public function setIsNew() {
		$this->is_new=true;
		$this->is_old=false;
	}
	public function getIsNew() {
		return $this->is_new;
	}

	/**
	 * Print a simple fact version of this event
	 *
	 * @param boolean $return whether to print or return
	 * @param boolean $anchor whether to add anchor to date and place
	 */
	function print_simple_fact($return=false, $anchor=false) {
		global $SHOW_PEDIGREE_PLACES, $ABBREVIATE_CHART_LABELS;

		if (!$this->canShow()) return "";
		$data = '<span class="details_label">'.$this->getLabel($ABBREVIATE_CHART_LABELS).'</span>';
		// Don't display "yes", because format_fact_date() does this for us.  (Should it?)
		if ($this->detail && $this->detail!='Y') {
			$data .= ' <span dir="auto">'.htmlspecialchars((string) $this->detail).'</span>';
		}
		$data .= ' '.format_fact_date($this, $this->getParentObject(), $anchor, false);
		$data .= ' '.format_fact_place($this, $anchor, false, false);
		$data .= '<br>';
		if ($return) {
			return $data;
		} else {
			echo $data;
		}
	}

	// Display an icon for this fact.
	// Icons are held in a theme subfolder.  Not all themes provide icons.
	function Icon() {
		$dir=KT_THEME_DIR.'images/facts/';
		$tag=$this->getTag();
		$file=$tag.'.png';
		if (file_exists($dir.$file)) {
			return '<img src="'.KT_STATIC_URL.$dir.$file.'" title="'.KT_Gedcom_Tag::getLabel($tag).'">';
		} elseif (file_exists($dir.'NULL.png')) {
			// Spacer image - for alignment - until we move to a sprite.
			return '<img src="'.KT_STATIC_URL.$dir.'NULL.png">';
		} else {
			return '';
		}
	}

	/**
	 * Static Helper functions to sort events
	 *
	 * @param KT_Event $a
	 * @param KT_Event $b
	 * @return int
	 */
	static function CompareDate($a, $b) {
		if ($a->getDate()->isOK() && $b->getDate()->isOK()) {
			// If both events have dates, compare by date
			$ret=KT_Date::Compare($a->getDate(), $b->getDate());
			if ($ret==0) {
				// If dates are the same, compare by fact type
				$ret=self::CompareType($a, $b);
				// If the fact type is also the same, retain the initial order
				if ($ret==0) {
					$ret=$a->sortOrder - $b->sortOrder;
				}
			}
			return $ret;
		} else {
			// One or both events have no date - retain the initial orde
			return $a->sortOrder - $b->sortOrder;
		}
	}

	/**
	 * Static method to Compare two events by their type
	 *
	 * @param KT_Event $a
	 * @param KT_Event $b
	 * @return int
	 */
	static function CompareType($a, $b) {
		global $factsort;

		if (empty($factsort))
			$factsort=array_flip(array(
				'BIRT',
				'_HNM',
				'ALIA', '_AKA', '_AKAN',
				'ADOP', '_ADPF', '_ADPF',
				'_BRTM',
				'CHR', 'BAPM', 'BAPM_CHR',
				'FCOM',
				'CONF',
				'BARM', 'BASM',
				'EDUC',
				'GRAD',
				'_DEG',
				'EMIG', 'IMMI',
				'NATU',
				'_MILI', '_MILT',
				'ENGA',
				'MARB', 'MARC', 'MARL', '_MARI', '_MBON',
				'MARR', 'MARR_CIVIL', 'MARR_RELIGIOUS', 'MARR_PARTNERS', 'MARR_UNKNOWN', '_COML',
				'_STAT',
				'_SEPR',
				'DIVF',
				'MARS',
				'_BIRT_CHIL',
				'DIV', 'ANUL',
				'_BIRT_', '_MARR_', '_DEAT_','_BURI_', // other events of close relatives
				'CENS',
				'OCCU',
				'RESI',
				'PROP',
				'CHRA',
				'RETI',
				'FACT', 'EVEN',
				'_NMR', '_NMAR', 'NMR',
				'NCHI',
				'WILL',
				'_HOL',
				'_????_',
				'DEAT',
				'_FNRL', 'BURI', 'CREM', '_INTE', 'BURI_CREM',
				'_YART',
				'_NLIV',
				'PROB',
				'TITL',
				'COMM',
				'NATI',
				'CITN',
				'CAST',
				'RELI',
				'SSN', 'IDNO',
				'TEMP',
				'SLGC', 'BAPL', 'CONL', 'ENDL', 'SLGS',
				'ADDR', 'PHON', 'EMAIL', '_EMAIL', 'EMAL', 'FAX', 'WWW', 'URL', '_URL',
				'AFN', 'REFN', '_PRMN', 'REF', 'RIN', '_UID',
				'CHAN', '_TODO',
				'NOTE', 'SOUR', 'OBJE'
			));

		// Facts from same families stay grouped together
		// Keep MARR and DIV from the same families from mixing with events from other FAMs
		// Use the original order in which the facts were added
		if ($a->parentObject instanceof KT_Family && $b->parentObject instanceof KT_Family && !$a->parentObject->equals($b->parentObject)) {
			return $a->sortOrder - $b->sortOrder;
		}

		$atag = $a->getTag();
		$btag = $b->getTag();

		// Events not in the above list get mapped onto one that is.
		if (!array_key_exists($atag, $factsort)) {
			if (preg_match('/^(_(BIRT|MARR|DEAT|BURI)_)/', $atag, $match)) {
				$atag=$match[1];
			} else {
				$atag="_????_";
			}
		}
		if (!array_key_exists($btag, $factsort)) {
			if (preg_match('/^(_(BIRT|MARR|DEAT|BURI)_)/', $btag, $match)) {
				$btag=$match[1];
			} else {
				$btag="_????_";
			}
		}

		//-- don't let dated after DEAT/BURI facts sort non-dated facts before DEAT/BURI
		//-- treat dated after BURI facts as BURI instead
		if ($a->getValue('DATE')!=NULL && $factsort[$atag]>$factsort['BURI'] && $factsort[$atag]<$factsort['CHAN']) $atag='BURI';
		if ($b->getValue('DATE')!=NULL && $factsort[$btag]>$factsort['BURI'] && $factsort[$btag]<$factsort['CHAN']) $btag='BURI';
		$ret = $factsort[$atag]-$factsort[$btag];
		//-- if facts are the same then put dated facts before non-dated facts
		if ($ret==0) {
			if ($a->getValue('DATE')!=NULL && $b->getValue('DATE')==NULL) return -1;
			if ($b->getValue('DATE')!=NULL && $a->getValue('DATE')==NULL) return 1;
			//-- if no sorting preference, then keep original ordering
			$ret = $a->sortOrder - $b->sortOrder;
		}
		return $ret;
	}

	/**
	 * Get the value of level 1 data in the fact
	 * Allow for multi-line values
	 *
	 * @return string|null
	 */
	public function getValueTarget() {
		if (preg_match('/^1 (?:' . $this->tag . ') ?(.*(?:(?:\n2 CONT ?.*)*))/', $this->gedcomRecord, $match)) {
			return preg_replace("/\n2 CONT ?/", "\n", $match[1]);
		} else {
			return null;
		}
	}

	/**
	 * Get the record to which this fact links
	 *
	 * @return Individual|Family|Source|Repository|Media|Note|null
	 */
	public function getTarget() {
		$xref = trim($this->getValue($this->tag));
		switch ($this->tag) {
		case 'FAMC':
		case 'FAMS':
			return KT_Family::getInstance($xref, $this->parentObject->getGedId());
		case 'HUSB':
		case 'WIFE':
		case 'CHIL':
			return KT_Person::getInstance($xref, $this->parentObject->getGedId());
		case 'SOUR':
			return KT_Source::getInstance($xref, $this->parentObject->getGedId());
		case 'OBJE':
			return KT_Media::getInstance($xref, $this->parentObject->getGedId());
		case 'REPO':
			return KT_Repository::getInstance($xref, $this->parentObject->getGedId());
		case 'NOTE':
			return KT_Note::getInstance($xref, $this->parentObject->getGedId());
		default:
			return KT_GedcomRecord::getInstance($xref, $this->parentObject->getGedId());
		}
	}



}
