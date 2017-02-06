<?php
// Fancy Tree View Module
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from JustCarmen
// Copyright (C) 2015 JustCarmen
//
// Derived from webtrees
// Copyright (C) 2014 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2010 John Finlay
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
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class WT_Controller_FancyTreeView extends fancy_treeview_WT_Module {

	// Get module options
	public function options($value = '') {
		$FTV_OPTIONS = unserialize(get_module_setting($this->getName(), 'FTV_OPTIONS'));

		$key = WT_TREE::getIdFromName(WT_Filter::get('ged'));
		if (empty($key)) {
			$key = WT_GED_ID;
		}

		if (empty($FTV_OPTIONS) || (is_array($FTV_OPTIONS) && !array_key_exists($key, $FTV_OPTIONS))) {
			$FTV_OPTIONS[0] = array(
				'USE_FULLNAME' 			=> '0',
				'NUMBLOCKS'				=> '0',
				'CHECK_RELATIONSHIP' 	=> '0',
				'SHOW_SINGLES'			=> '0',
				'SHOW_PLACES' 			=> '1',
				'USE_GEDCOM_PLACES'		=> '0',
				'COUNTRY' 				=> '',
				'SHOW_OCCU' 			=> '1',
				'RESIZE_THUMBS'			=> '1',
				'THUMB_SIZE'			=> '60',
				'THUMB_RESIZE_FORMAT'	=> '2',
				'USE_SQUARE_THUMBS'		=> '1'
			);
			$key = 0;
		}

		// country could be disabled and thus not set
		if ($value == 'country' && !array_key_exists(strtoupper($value), $FTV_OPTIONS[$key])) {
			return '';
		} elseif ($value) {
			return($FTV_OPTIONS[$key][strtoupper($value)]);
		} else {
			return $FTV_OPTIONS[$key];
		}
	}

	// Get Indis from surname input
	public function indis_array($surname, $soundex_std, $soundex_dm) {
		$sql =
			"SELECT DISTINCT i_id AS xref, i_file AS gedcom_id, i_gedcom AS gedcom".
			" FROM `##individuals`".
			" JOIN `##name` ON (i_id=n_id AND i_file=n_file)".
			" WHERE n_file=?".
			" AND n_type!=?".
			" AND (n_surn=? OR n_surname=?";
		$args = array(WT_GED_ID, '_MARNM', $surname, $surname);
		if ($soundex_std) { // works only with latin letters. For other letters it outputs the code '0000'.
			foreach (explode(':', WT_Soundex::soundex_std($surname)) as $value) {
				if ($value != '0000') {
					$sql .= " OR n_soundex_surn_std LIKE CONCAT('%', ?, '%')";
					$args[] = $value;
				}
			}
		}
		if ($soundex_dm) { // works only with predefined letters and lettercombinations. Fot other letters it outputs the code '000000'.
			foreach (explode(':', WT_Soundex::soundex_dm($surname)) as $value) {
				if ($value != '000000') {
					$sql .= " OR n_soundex_surn_dm LIKE CONCAT('%', ?, '%')";
					$args[] = $value;
				}
			}
		}
		$sql .= ')';
		$rows =
			WT_DB::prepare($sql)
			->execute($args)
			->fetchAll();
		$data = array();
		foreach ($rows as $row) {
			$data[] = WT_Person::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
		}
		return $data;
	}

	// Get surname from pid
	public function getSurname($pid) {
		$sql= "SELECT n_surname AS surname FROM `##name` WHERE n_file=? AND n_id=? AND n_type=?";
		$args = array(WT_GED_ID, $pid, 'NAME');
		$data= WT_DB::prepare($sql)->execute($args)->fetchOne();
		return $data;
	}

	// Add error or success message
	public function addMessage($controller, $type, $msg) {
		if ($type == "success") {
			$class = "ui-state-highlight";
		}
		if ($type == "error") {
			$class = "ui-state-error";
		}
		$controller->addInlineJavaScript('
			jQuery("#error").text("' . $msg . '").addClass("' . $class . '").show("normal");
			setTimeout(function() {
				jQuery("#error").hide("normal");
			}, 10000);
		');
	}

	// Search within a multiple dimensional array
	public function searchArray($array, $key, $value) {
		$results = array();
		if (is_array($array)) {
			if (isset($array[$key]) && $array[$key] == $value) {
				$results[] = $array;
			}
			foreach ($array as $subarray) {
				$results = array_merge($results, $this->searchArray($subarray, $key, $value));
			}
		}
		return $results;
	}

	// Sort the array according to the $key['SORT'] input.
	public function sortArray($array, $sort_by){

		$array_keys = array('tree', 'surname', 'display_name', 'pid', 'access_level', 'sort');

		foreach ($array as $pos =>  $val) {
			$tmp_array[$pos] = $val[$sort_by];
		}
		asort($tmp_array);

		$return_array = array();
		foreach ($tmp_array as $pos => $val){
			foreach ($array_keys as $key) {
				$key = strtoupper($key);
				$return_array[$pos][$key] = $array[$pos][$key];
			}
		}
		return array_values($return_array);
    }

	public function getCountryList() {
		$list = '';
		$countries =
			WT_DB::prepare("SELECT SQL_CACHE p_place as country FROM `##places` WHERE p_parent_id=? AND p_file=?")
			->execute(array('0', WT_GED_ID))->fetchAll(PDO::FETCH_ASSOC);

		foreach ($countries as $country) {
			$list[$country['country']] = $country['country']; // set the country as key to display as option value.
		}
		return $list;
	}

	// Reset all settings to default
	public function ftv_reset() {
		WT_DB::prepare("DELETE FROM `##module_setting` WHERE setting_name LIKE 'FTV%'")->execute();
		AddToLog($this->getTitle() . ' reset to default values', 'auth');
	}

	// Delete item
	public function delete() {
		$FTV_SETTINGS = unserialize(get_module_setting($this->getName(), 'FTV_SETTINGS'));
		unset($FTV_SETTINGS[WT_Filter::getInteger('key')]);
		$NEW_FTV_SETTINGS = array_merge($FTV_SETTINGS);
		set_module_setting($this->getName(), 'FTV_SETTINGS',  serialize($NEW_FTV_SETTINGS));
		AddToLog($this->getTitle() . ' item deleted', 'auth');
	}

	// Print functions
	public function printPage($direction) {
		$root 		= WT_Filter::get('rootid', WT_REGEX_XREF);
		$gen  		= WT_Filter::get('gen', WT_REGEX_INTEGER);
		$pids 		= WT_Filter::get('pids');
		$numblocks  = $this->options('numblocks');

		if ($numblocks == 0) {
			$numblocks = 99;
		}

		$html = '';
		if (!isset($gen) && !isset($pids)) {
			$gen			  = 1;
			$numblocks		  = $numblocks - 1;
			$this->generation = array($root);
			$this->gencount	  = 1;
			$direction == 'ancestors' ? $this->loadAncestors($this->get_person($root), 1) : '';
			$html .= $this->printGeneration($gen, $direction);
		} else {
			$this->generation = explode('|', $pids);
		}

		$lastblock = $gen + $numblocks + 1;// + 1 to get one hidden block.
		while (count($this->generation) > 0 && $gen < $lastblock) {
			$pids = $this->generation;
			unset($this->generation);

			switch ($direction) {
				case  'descendants':
					foreach ($pids as $pid) {
						$next_gen[] = $this->getNextGen($pid);
					}
					foreach ($next_gen as $descendants) {
						if (count($descendants) > 0) {
							foreach ($descendants as $descendant) {
								if ($this->options('show_singles') == true || $descendant['desc'] == 1) {
									$this->generation[] = $descendant['pid'];
								}
							}
						}
					}
					break;
				case 'ancestors':
					foreach ($pids as $pid) {
						$parents = $this->getParents($pid);
						if (count($parents) > 0) {
							foreach ($parents as $parent) {
								$this->generation[] = $parent;
							}
						}
					}
					break;
			}

			if (!empty($this->generation)) {
				$gen++;
				$direction == 'ancestors' ? $this->gencount = count($this->generation) : '';
				$html .= $this->printGeneration($gen, $direction);
				unset($next_gen, $descendants, $pids);
			} else {
				return $html;
			}

		}
		return $html;
	}

	public function printGeneration($i, $direction) {
		// added data attributes to retrieve values easily with jquery (for scroll reference to next generations).
		$html = '<li class="block generation-block" data-gen="' . $i . '" data-pids="' . implode('|', $this->generation) . '">' . $this->printBlockHeader($i, $direction);

		if ($this->check_privacy($this->generation, true)) {
			$html .= '<div class="blockcontent generation private">' . WT_I18N::translate('The details of this generation are private.') . '</div>';
		} else {
			$html .= $this->printBlockContent();
		}

		$html .= '</li>';

		return $html;

	}

	/** {@inheritdoc} */
	protected function printBlockHeader($i, $direction) {
		switch ($direction) {
			case 'descendants':
				$title = '<span class="header-title">' . WT_I18N::translate('Generation') . ' ' . $i . '</span>';
				break;
			case 'ancestors':
				$gentotal	= pow(2, $i - 1);
				$genperc	= number_format($this->gencount / $gentotal * 100, 2) . '%';
				$title = '<span class="header-title">' . WT_I18N::translate('Generation') . ' ' . $i . ' (' . $this->gencount . ' ' . WT_I18N::translate('of') . ' ' . pow(2, $i - 1) . ' - ' . $genperc . ' ' . WT_I18N::translate('complete') . ')</span>';
				break;
		}

		return
			'<div class="blockheader ui-state-default">' .
				$title .
				$this->printBackToTopLink($i) .
			'</div>';
	}


	/**
	 *
	 * @return string
	 */
	protected function printBlockContent() {
		$html = '<ol class="blockcontent generation">';
			foreach (array_unique($this->generation) as $pid) {
				$person = $this->get_person($pid);
				// only list persons without parents in the same generation - if they have they will be listed in the next generation anyway.
				// This prevents double listings
				if(!$this->has_parents_in_same_generation($person, $this->generation)) {
					$family = $this->get_family($person);
					if(!empty($family)) {
						$id = $family->getXref();
					}
					else {
						if ($this->options('show_singles') == true || !$person->getSpouseFamilies()) {
							$id = 'S' . $pid;
						} // Added prefix (S = Single) to prevent double id's.
					}
					$class = $person->canDisplayDetails() ? 'family' : 'family private';
					$html .= '<li id="' . $id . '" class="' . $class . '">' . $this->print_person($person) . '</li>';
				}
			}
		$html .= '</ol>';

		return $html;
	}

	public function print_person($person) {
		global $SHOW_PRIVATE_RELATIONSHIPS;

		if($person->canDisplayDetails()) {
			$resize = $this->options('resize_thumbs') === 1 ? true : false;
			$html = '<div class="parents">' . $this->print_thumbnail($person, $this->options('thumb_size'), $this->options('thumb_resize_format'), $this->options('use_square_thumbs'), $resize) . '<a id="' . $person->getXref() . '" href="' . $person->getHtmlUrl() . '"><p class="desc">' . $person->getFullName() . '</a>';
			if ($this->options('show_occu') == true) {
				$html .= $this->printFact($person, 'OCCU');
			}

			$html .= $this->printParents($person).$this->printLifespan($person);

			// get a list of all the spouses
			// First, determine the true number of spouses by checking the family gedcom
			$spousecount = 0;
			foreach ($person->getSpouseFamilies(WT_PRIV_HIDE) as $i => $family) {
				$spouse = $family->getSpouse($person);
				if ($spouse && $spouse->canDisplayDetails() && ($family->getMarriage() || $family->isNotMarried())) {
					$spousecount++;
				}
			}
      /*
       * Now iterate thru spouses
       * $spouseindex is used for ordinal rather than array index
       * as not all families have a spouse
       * $spousecount is passed rather than doing each time inside function get_spouse
      */
			if($spousecount > 0) {
				$spouseindex = 0;
				foreach ($person->getSpouseFamilies(WT_PRIV_HIDE) as $i => $family) {
					$spouse = $family->getSpouse($person);
					if ($spouse && $spouse->canDisplayDetails()) {
						$marriage = $family->getMarriage();
						if ($marriage && $marriage->canShow()) {
							$html .= $this->printSpouse($family, $person, $spouse, $spouseindex, $spousecount);
							$spouseindex++;
						} else {
							$html .= $this->printPartner($family, $person, $spouse);
						}

					}
				}
			}

			$html .= '</p></div>';

			// get children for each couple (could be none or just one, $spouse could be empty, includes children of non-married couples)
			foreach ($person->getSpouseFamilies(WT_PRIV_HIDE) as $family) {
				$spouse = $family->getSpouse($person);
				$html .= $this->printChildren($family, $person, $spouse);
			}

			return $html;
		}
		else {
			if ($SHOW_PRIVATE_RELATIONSHIPS) {
				return WT_I18N::translate('The details of this family are private.');
			}
		}
	}

	public function printSpouse($family, $person, $spouse, $i, $count) {

		$html = ' ';

		// we assume no one married more then 15 times.
		// these need to be male/female for some languages
		$wordcountM = array(
			WT_I18N::translate_c('MALE', 'first'),
			WT_I18N::translate_c('MALE', 'second'),
			WT_I18N::translate_c('MALE', 'third'),
			WT_I18N::translate_c('MALE', 'fourth'),
			WT_I18N::translate_c('MALE', 'fifth'),
			WT_I18N::translate_c('MALE', 'sixth'),
			WT_I18N::translate_c('MALE', 'seventh'),
			WT_I18N::translate_c('MALE', 'eighth'),
			WT_I18N::translate_c('MALE', 'ninth'),
			WT_I18N::translate_c('MALE', 'tenth'),
			WT_I18N::translate_c('MALE', '11th'),
			WT_I18N::translate_c('MALE', '12th'),
			WT_I18N::translate_c('MALE', '13th'),
			WT_I18N::translate_c('MALE', '14th'),
			WT_I18N::translate_c('MALE', '15th')
		);

		$wordcountF = array(
			WT_I18N::translate_c('FEMALE', 'first'),
			WT_I18N::translate_c('FEMALE', 'second'),
			WT_I18N::translate_c('FEMALE', 'third'),
			WT_I18N::translate_c('FEMALE', 'fourth'),
			WT_I18N::translate_c('FEMALE', 'fifth'),
			WT_I18N::translate_c('FEMALE', 'sixth'),
			WT_I18N::translate_c('FEMALE', 'seventh'),
			WT_I18N::translate_c('FEMALE', 'eighth'),
			WT_I18N::translate_c('FEMALE', 'ninth'),
			WT_I18N::translate_c('FEMALE', 'tenth'),
			WT_I18N::translate_c('FEMALE', '11th'),
			WT_I18N::translate_c('FEMALE', '12th'),
			WT_I18N::translate_c('FEMALE', '13th'),
			WT_I18N::translate_c('FEMALE', '14th'),
			WT_I18N::translate_c('FEMALE', '15th')
		);

		$wordcount2M = array(
			WT_I18N::translate_c('MALE', 'once'),
			WT_I18N::translate_c('MALE', 'twice'),
			WT_I18N::translate_c('MALE', 'three times'),
			WT_I18N::translate_c('MALE', 'four times'),
			WT_I18N::translate_c('MALE', 'five times'),
			WT_I18N::translate_c('MALE', 'six times'),
			WT_I18N::translate_c('MALE', 'seven times'),
			WT_I18N::translate_c('MALE', 'eight times'),
			WT_I18N::translate_c('MALE', 'nine times'),
			WT_I18N::translate_c('MALE', 'ten times'),
			WT_I18N::translate_c('MALE', '11 times'),
			WT_I18N::translate_c('MALE', '12 times'),
			WT_I18N::translate_c('MALE', '13 times'),
			WT_I18N::translate_c('MALE', '14 times'),
			WT_I18N::translate_c('MALE', '15 times')
		);

		$wordcount2F = array(
			WT_I18N::translate_c('FEMALE', 'once'),
			WT_I18N::translate_c('FEMALE', 'twice'),
			WT_I18N::translate_c('FEMALE', 'three times'),
			WT_I18N::translate_c('FEMALE', 'four times'),
			WT_I18N::translate_c('FEMALE', 'five times'),
			WT_I18N::translate_c('FEMALE', 'six times'),
			WT_I18N::translate_c('FEMALE', 'seven times'),
			WT_I18N::translate_c('FEMALE', 'eight times'),
			WT_I18N::translate_c('FEMALE', 'nine times'),
			WT_I18N::translate_c('FEMALE', 'ten times'),
			WT_I18N::translate_c('FEMALE', '11 times'),
			WT_I18N::translate_c('FEMALE', '12 times'),
			WT_I18N::translate_c('FEMALE', '13 times'),
			WT_I18N::translate_c('FEMALE', '14 times'),
			WT_I18N::translate_c('FEMALE', '15 times')
		);

		if($count > 1) {
			if($i == 0) {
				$person->getSex() == 'M' ? $html .= '<br>' . /* I18N: %s is a number  */ WT_I18N::translate('He married %s', $wordcount2M[$count-1]) : $html .= '<br>' . WT_I18N::translate('She married %s', $wordcount2F[$count-1]);
				$html .= '. ';
			}
			$person->getSex() == 'M' ? $html .= '<br>' . /* I18N: %s is an ordinal */ WT_I18N::translate('The %s time he married', $wordcountM[$i]) : $html .= '<br>' . WT_I18N::translate('The %s time she married', $wordcountF[$i]);
		}
		else {
			$person->getSex() == 'M' ? $html .= '<br>' . WT_I18N::translate('He married') : $html .= '<br>' . WT_I18N::translate('She married');
		}

		$html .= ' <a href="' . $spouse->getHtmlUrl() . '">' . $spouse->getFullName() . '</a>';

		// Add relationship note
		if($this->options('check_relationship')) {
			$relationship = $this->check_relationship($person, $spouse, $family);
			if ($relationship) {
				$html .= ' (' . $relationship . ')';
			}
		}

		$html .= $this->printParents($spouse);

		if(!$family->getMarriage()) { // use the default privatized function to determine if marriage details can be shown.
			$html .= '.';
		}
		else {
			// use the facts below only on none private records.
			$marrdate = $family->getMarriageDate();
			$marrplace = $family->getMarriagePlace();
			if ($marrdate && $marrdate->isOK()) {
				$html .= $this->print_date($marrdate);
			}
			if (!is_null($family->getMarriagePlace())) {
				$html .= $this->printPlace($family->getMarriagePlace());
			}
			$html .= $this->printLifespan($spouse, true);

			if($family->isDivorced()) {
				$html .= $person->getFullName() . ' ' . WT_I18N::translate('and') . ' ' . $spouse->getFullName() .  ' ' . WT_I18N::translate('divorced') . $this->print_divorce_date($family) . '.';
			}
		}
		return $html;
	}

	public function printPartner($family, $person, $spouse) {
		$html = ' ';
		switch ($person->getSex()) {
			case 'M':
				$html .= '<br>' . WT_I18N::translate('He had a relationship with');
				break;
			case 'F':
				$html .= '<br>' . WT_I18N::translate('She had a relationship with');
				break;
			default:
				$html .= '<br>' . WT_I18N::translate('This individual had a relationship with');
				break;
		}
		$html .= ' <a href="' . $spouse->getHtmlUrl() . '">' . $spouse->getFullName() . '</a>';
		$html .= $this->printRelationship($person, $spouse, $family);
		$html .= $this->printParents($spouse);
		if ($family->getFacts('_NMR') && $this->printLifespan($spouse, true)) {
			$html .= $this->printLifespan($spouse, true);
		}

		return $html;
	}

	public function printChildren($family, $person, $spouse) {
		$html = '';

		$match = null;
		if (preg_match('/\n1 NCHI (\d+)/', $family->getGedcomRecord(), $match) && $match[1]==0) {
			$html .= '<div class="children"><p>' . $person->getFullName() . ' ';
					if($spouse && $spouse->canDisplayDetails()) {
						$html .= /* I18N: Note the space at the end of the string */ WT_I18N::translate('and ').$spouse->getFullName() . ' ';
						$html .= WT_I18N::translate_c('Two parents/one child', 'had');
					}
					else {
						$html .= WT_I18N::translate_c('One parent/one child', 'had');
					}
					$html .= ' ' . /* I18N: 'no' is the number zero  */ WT_I18N::translate('no') . ' ' . WT_I18N::translate('children') . '.</p></div>';
		}
		else {
			$children = $family->getChildren();
			if($children) {
				if ($this->check_privacy($children)) {
					$html .= '<div class="children"><p>' . $person->getFullName() . ' ';
					// needs multiple translations for the word 'had' to serve different languages.
					if($spouse && $spouse->canDisplayDetails()) {
						$html .= /* I18N: Note the space at the end of the string */ WT_I18N::translate('and ').$spouse->getFullName() . ' ';
						if (count($children) > 1) {
							$html .= WT_I18N::translate_c('Two parents/multiple children', 'had');
						} else {
							$html .= WT_I18N::translate_c('Two parents/one child', 'had');
						}
					}
					else {
						if (count($children) > 1) {
							$html .= WT_I18N::translate_c('One parent/multiple children', 'had');
						} else {
							$html .= WT_I18N::translate_c('One parent/one child', 'had');
						}
					}
					$html .= ' './* I18N: %s is a number */ WT_I18N::plural('%s child', '%s children', count($children), count($children)) . '.</p></div>';
				}
				else {
					$html .= '<div class="children"><p>'. /* I18N: Note the space at the end of the string */ WT_I18N::translate('Children of ').$person->getFullName();
					if($spouse && $spouse->canDisplayDetails()) {
						$html .= ' '. /* I18N: Note the space at the end of the string */ WT_I18N::translate('and ');
						if (!$family->getMarriage()) {
							// check relationship first (If a relationship is found the information of this parent is printed elsewhere on the page.)
							if ($this->options('check_relationship')) {
								$relationship = $this->check_relationship($person, $spouse, $family);
							}
							if(isset($relationship) && $relationship) {
								$html .= $spouse->getFullName() . ' (' . $relationship.')';
							}
							else {
								// the non-married spouse is not mentioned in the parents div text or elsewhere on the page. So put a link behind the name.
								$html .= '<a class="tooltip" title="" href="' . $spouse->getHtmlUrl() . '">' . $spouse->getFullName() . '</a>';
								// Print info of the non-married spouse in a tooltip
								$html .= '<span class="tooltip-text">' . $this->print_tooltip($spouse) . '</span>';
							}
						}
						else {
							$html .= $spouse->getFullName();
						}
					}
					$html .= ':<ol>';

					foreach ($children as $child) {
						if ($child->canDisplayDetails()) {
							$html .= '<li class="child"><a href="' . $child->getHtmlUrl() . '">' . $child->getFullName() . '</a>';
							$pedi = $child->getChildFamilyPedigree($family->getXref());

							if($pedi === 'foster') {
								if ($child->getSex() == 'F') {
									$html .= ' <span class="pedi"> - ' . WT_I18N::translate_c('FEMALE', 'foster child') . '</span>';
								} else {
									$html .= ' <span class="pedi"> - ' . WT_I18N::translate_c('MALE', 'foster child') . '</span>';
								}
							}
							if($pedi === 'adopted') {
								if ($child->getSex() == 'F') {
									$html .= ' <span class="pedi"> - ' . WT_I18N::translate_c('FEMALE', 'adopted') . '</span>';
								} else {
									$html .= ' <span class="pedi"> - ' . WT_I18N::translate_c('MALE', 'adopted') . '</span>';
								}
							}
							if ($child->getBirthDate()->isOK() || $child->getDeathdate()->isOK()) {
								$html .= '<span class="lifespan"> (' . $child->getLifeSpan() . ')</span>';
							}

							$child_family = $this->get_family($child);
							if ($child->canDisplayDetails() && $child_family) {
									$html .= ' - <a class="scroll" href="#' . $child_family->getXref() . '"></a>';
							}
							else { // just go to the person details in the next generation (added prefix 'S'for Single Individual, to prevent double ID's.)
								if ($this->options('show_singles') == true) {
									$html .= ' - <a class="scroll" href="#S' . $child->getXref() . '"></a>';
								}
							}
							$html .= '</li>';
						}
					}
					$html .= '</ol></div>';
				}
			}
		}
		return $html;
	}

	public function printParents($person) {
		$parents = $person->getPrimaryChildFamily();
		if ($parents) {
			$pedi = $person->getChildFamilyPedigree($parents->getXref());

			$html = '';
			switch($person->getSex()) {
				case 'M':
					if ($pedi === 'foster') {
						$html .= ' ' . WT_I18N::translate('foster son of') . ' ';
					} elseif ($pedi === 'adopted') {
						$html .= ' ' . WT_I18N::translate('adopted son of') . ' ';
					} else {
						$html .= ' ' . WT_I18N::translate('son of') . ' ';
					}
					break;
				case 'F':
					if ($pedi === 'foster') {
						$html .= ' ' . WT_I18N::translate('foster daughter of') . ' ';
					} elseif ($pedi === 'adopted') {
						$html .= ' ' . WT_I18N::translate('adopted daughter of') . ' ';
					} else {
						$html .= ' ' . WT_I18N::translate('daughter of') . ' ';
					}
					break;
				default:
					if ($pedi === 'foster') {
						$html .= ' ' . WT_I18N::translate_c('MALE', 'foster child of') . ' ';
					} elseif ($pedi === 'adopted') {
						$html .= ' ' . WT_I18N::translate('adopted child of') . ' ';
					} else {
						$html .= ' ' . WT_I18N::translate('child of') . ' ';
					}
			}

			$father = $parents->getHusband();
			$mother = $parents->getWife();

			if ($father) {
				$html .= $father->getFullName();
			}
			if ($father && $mother) {
				$html .= ' ' . /* I18N: Note the space at the end of the string */ WT_I18N::translate('and ');
			}
			if ($mother) {
				$html .= $mother->getFullName();
			}

			return $html;
		}
	}

	public function printLifespan($person, $is_spouse = false){
		$html = '';
		$birthdate = $person->getBirthDate();
		$deathdate = $person->getDeathdate();
		$ageOfdeath = get_age_at_event(WT_Date::GetAgeGedcom($birthdate, $deathdate), false);

		$birthdata = false;
		if($birthdate->isOK() || $person->getBirthPlace() != ''){
			$birthdata = true;
			if ($is_spouse == true) {
				$html .= '. ';
				if($person->isDead()) {
					$person->getSex() == 'F' ? $html .= WT_I18N::translate_c('PAST', 'She was born') : $html .= WT_I18N::translate_c('PAST', 'He was born');
				}
				else {
					$person->getSex() == 'F' ? $html .= WT_I18N::translate_c('PRESENT', 'She was born') : $html .= WT_I18N::translate_c('PRESENT', 'He was born');
				}
			} else {
				$this->printParents($person) || $this->printFact($person, 'OCCU') ? $html .= ', ' : $html .= ' ';
				if ($person->isDead()) {
					$person->getSex() == 'F' ? $html .= WT_I18N::translate_c('PAST (FEMALE)', 'was born') : $html .= WT_I18N::translate_c('PAST (MALE)', 'was born');
				}
				else {
				 	$person->getSex() == 'F' ? $html .= WT_I18N::translate_c('PRESENT (FEMALE)', 'was born') : $html .= WT_I18N::translate_c('PRESENT (MALE)', 'was born');
				}
			}
			if ($birthdate->isOK()) {
				$html .= $this->print_date($birthdate);
			}
			if ($person->getBirthPlace() != '') {
				$html .= $this->printPlace($person->getBirthPlace());
			}
		}

		$deathdata = false;
		if($deathdate->isOK() || $person->getDeathPlace() != ''){
			$deathdata = true;
			if($birthdata) {
				$html .= ' '. /* I18N: Note the space at the end of the string */ WT_I18N::translate('and ');
				$person->getSex() == 'F' ? $html .= WT_I18N::translate_c('FEMALE', 'died') : $html .= WT_I18N::translate_c('MALE', 'died');
			}
			else {
				$person->getSex() == 'F' ? $html .= '. ' . WT_I18N::translate('She died') : $html .= '. ' . WT_I18N::translate('He died');
			}

			if ($deathdate->isOK()) {
				$html .= $this->print_date($deathdate);
			}
			if ($person->getDeathPlace() != '') {
				$html .= $this->printPlace($person->getDeathPlace());
			}

			if ($birthdate->isOK() && $deathdate->isOK()) {
				if (WT_Date::getAge($birthdate, $deathdate, 0) < 2) {
					$html .= ' './* I18N: %s is the age of death in days/months; %s is a string, e.g. at the age of 2 months */  WT_I18N::translate_c('age in days/months', 'at the age of %s', $ageOfdeath);
				}
				else {
					$html .= ' './* I18N: %s is the age of death in years; %s is a number, e.g. at the age of 40 */  WT_I18N::translate_c('age in years', 'at the age of %s', $ageOfdeath);
				}
			}
		}

		if ($birthdata || $deathdata) {
			$html .= '. ';
		}

		return $html;
	}

	// some couples are known as not married but have children together. Print the info of the "spouse" parent in a tooltip.
	public function print_tooltip($person) {
		$birthdate = $person->getBirthDate();
		$deathdate = $person->getDeathdate();
		$html = '';
		if ($birthdate->isOK()) {
			$html .= '<strong>' . WT_I18N::translate('Birth') . ':</strong> ' . strip_tags($birthdate->Display());
		}
		if ($deathdate->isOK()) {
			$html .= '<br><strong>' . WT_I18N::translate('Death') . ':</strong> ' . strip_tags($deathdate->Display());
		}

		$parents = $person->getPrimaryChildFamily();
		if ($parents) {
			$father = $parents->getHusband();
			$mother = $parents->getWife();
			if ($father) {
				$html .= '<br><strong>' . WT_I18N::translate('Father') . ':</strong> ' . strip_tags($father->getFullName());
			}
			if ($mother) {
				$html .= '<br><strong>' . WT_I18N::translate('Mother') . ':</strong> ' . strip_tags($mother->getFullName());
			}
		}
		return $html;
	}

	public function printRelationship($person, $spouse, $family) {
		$html = '';
		if ($this->options('check_relationship')) {
			$relationship = $this->check_relationship($person, $spouse, $family);
			if ($relationship) {
				$html .= ' (' . $relationship . ')';
			}
		}
		return $html;
	}

	public function print_thumbnail($person, $thumbsize, $resize_format, $square, $resize) {
		$mediaobject = $person->findHighlightedMedia();
		if ($mediaobject) {
			$html = '';
			if($resize == true) {
				$mediasrc = $resize_format == 1 ? $mediaobject->getServerFilename('thumb') : $mediaobject->getServerFilename('main');
				$thumbwidth = $thumbsize; $thumbheight = $thumbsize;
				$mediatitle = strip_tags($person->getFullName());

				$type = $mediaobject->mimeType();
				if($type == 'image/jpeg' || $type == 'image/png' || $mimetype === 'image/gif') {

					if (!list($width_orig, $height_orig) = @getimagesize($mediasrc)) {
						return null;
					}

					switch ($type) {
						case 'image/jpeg':
							$image = @imagecreatefromjpeg($mediasrc);
							break;
						case 'image/png':
							$image = @imagecreatefrompng($mediasrc);
							break;
						case 'image/gif':
							$image	 = imagecreatefromgif($mediasrc);
							break;
						}

					// fallback if image is in the database but not on the server
					if(isset($width_orig) && isset($height_orig)) {
						$ratio_orig = $width_orig/$height_orig;
					}
					else {
						$ratio_orig = 1;
					}

					if($resize_format == 1) {
						$thumbwidth = $thumbwidth/100 * $width_orig;
						$thumbheight = $thumbheight/100 * $height_orig;
					}

					if($square == true) {
						$thumbheight = $thumbwidth;
						if ($ratio_orig < 1) {
						   $new_height = $thumbwidth/$ratio_orig;
						   $new_width = $thumbwidth;
						} else {
						   $new_width = $thumbheight*$ratio_orig;
						   $new_height = $thumbheight;
						}
					}
					else {
						if($resize_format == 1) {
							$new_width = $thumbwidth;
							$new_height = $thumbheight;
						} elseif ($width_orig > $height_orig) {
							$new_height = $thumbheight/$ratio_orig;
							$new_width 	= $thumbwidth;
						} elseif ($height_orig > $width_orig) {
						   $new_width 	= $thumbheight*$ratio_orig;
						   $new_height 	= $thumbheight;
						} else {
							$new_width 	= $thumbwidth;
							$new_height = $thumbheight;
						}
					}
					$process = @imagecreatetruecolor(round($new_width), round($new_height));
					if($type == 'image/png') { // keep transparancy for png files.
						imagealphablending($process, false);
						imagesavealpha($process, true);
					}
					@imagecopyresampled($process, $image, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);

					$thumb = $square == true ? imagecreatetruecolor($thumbwidth, $thumbheight) : imagecreatetruecolor($new_width, $new_height);
					if($type == 'image/png') {
						imagealphablending($thumb, false);
						imagesavealpha($thumb, true);
					}
					@imagecopyresampled($thumb, $process, 0, 0, 0, 0, $thumbwidth, $thumbheight, $thumbwidth, $thumbheight);

					@imagedestroy($process);
					@imagedestroy($image);

					$width = $square == true ? round($thumbwidth) : round($new_width);
					$height = $square == true ? round($thumbheight) : round($new_height);
					ob_start(); $type = 'image/png' ? imagepng($thumb,null,9) : imagejpeg($thumb,null,100); $newThumb = ob_get_clean();
					$html = '<a' .
							' class="'          	. 'gallery'                         			 	. '"' .
							' href="'           	. $mediaobject->getHtmlUrlDirect('main')    		. '"' .
							' type="'           	. $mediaobject->mimeType()                  		. '"' .
							' data-obje-url="'  	. $mediaobject->getHtmlUrl()                		. '"' .
							' data-obje-note="' 	. htmlspecialchars($mediaobject->getNote())			. '"' .
							' data-obje-xref="'		. $mediaobject->getXref()							. '"' .
							' data-title="'     	. WT_Filter::escapeHtml($mediaobject->getFullName()). '"' .
							'><img class="ftv-thumb" src="data:' . $mediaobject->mimeType() . ';base64,'.base64_encode($newThumb) . '" dir="auto" title="' . $mediatitle . '" alt="' . $mediatitle . '" width="' . $width . '" height="' . $height . '"/></a>';
				}
			}
			else {
				$html = $mediaobject->displayImage();
			}
			return $html;
		}
	}

	public function print_date($date) {
		if($date->qual1 || $date->qual2) {
			return ' ' . $date->Display();
		}
		if($date->MinDate()->d > 0) {
			return ' '. /* I18N: Note the space at the end of the string */ WT_I18N::translate_c('before dateformat dd-mm-yyyy', 'on ').$date->Display();
		}
		if($date->MinDate()->m > 0) {
			return ' '. /* I18N: Note the space at the end of the string */ WT_I18N::translate_c('before dateformat mmm yyyy', 'in ').$date->Display();
		}
		if($date->MinDate()->y > 0) {
			return ' '. /* I18N: Note the space at the end of the string */ WT_I18N::translate_c('before dateformat yyyy', 'in ').$date->Display();
		}
	}

	public function print_divorce_date($family) {
		foreach ($family->getAllFactsByType(explode('|', WT_EVENTS_DIV)) as $event) {
			// Only display if it has a date
			if ($event->getDate()->isOK() && $event->canShow()) {
				return $this->print_date($event->getDate());
			}
		}
	}

	public function printFact($person, $tag) {
		$facts = $person->getFacts();
		foreach ($facts as $fact) {
			if ($fact->getTag()== $tag) {
				$str = $fact->getDetail();
				$str = rtrim($str, ".");
				$html = ', ' . $str;
				return $html;
			}
		}
	}

	public function printPlace($place) {
		if($this->options('show_places') == true) {
			$place = new WT_Place($place, WT_GED_ID);
			$html = ' '. /* I18N: Note the space at the end of the string */ WT_I18N::translate_c('before placesnames', 'in ');
			if	($this->options('use_gedcom_places') == true) {
				$html .= $place->getShortName();
			} else {
				$country = $this->options('country');
				$new_place = array_reverse(explode(", ", $place->getGedcomName()));
				if (!empty($country) && $new_place[0] == $country) {
					unset($new_place[0]);
					$html .= '<span dir="auto">' . WT_Filter::escapeHtml(implode(', ', array_reverse($new_place))) . '</span>';
				} else {
					$html .= $place->getFullName();
				}
			}
			return $html;
		}
	}

	// Other functions

	/** @var Individual[] Ancestors of the root person - for SOSA numbers */
	private $ancestors = array();

	/** @var integer count number of individuals in generation */
	private $gencount;

	public function get_person($pid) {
		$person = WT_Person::getInstance($pid);
		return $person;
	}

	public function get_family($person) {
		foreach ($person->getSpouseFamilies(WT_PRIV_HIDE) as $family) {
			return $family;
		}
	}

	public function getNextGen($pid) {
		$person = $this->get_person($pid);
		foreach($person->getSpouseFamilies() as $family) {
			$children = $family->getChildren();
			if($children) {
				foreach ($children as $key => $child) {
					$key = $family->getXref() . '-' . $key; // be sure the key is unique.
					$ng[$key]['pid'] = $child->getXref();
					$child->getSpouseFamilies(WT_PRIV_HIDE) ? $ng[$key]['desc'] = 1 : $ng[$key]['desc'] = 0;
				}
			}
		}
		if (isset($ng)) {
			return $ng;
		}
	}

	// check if a person has parents in the same generation
	public function has_parents_in_same_generation($person, $generation) {
		$parents = $person->getPrimaryChildFamily();
		if ($parents) {
			$father = $parents->getHusband();
			$mother = $parents->getWife();
			if ($father) {
				$father = $father->getXref();
			}
			if ($mother) {
				$mother = $mother->getXref();
			}
			if(in_array($father, $generation) || in_array($mother, $generation)) {
				return true;
			}
		}
	}

	// check (blood) relationship between partners
	public function check_relationship($person, $spouse, $family) {
		$count = count($family->getChildren());
		for($i = 0; $i <= $count; $i++) { // the number of paths is equal to the number of children, because every relationship is checked through each child.
										  // and we need the relationship from the next path.
			$nodes = get_relationship($person, $spouse, false, 0, $i);

			if (!is_array($nodes)) {
				return '';
			}

			$path = array_slice($nodes['relations'], 1);

			$combined_path = '';
			$display = false;
			foreach ($path as $key => $rel) {
				$rel_to_exclude = array('son', 'daughter', 'child'); // don't return the relationship path through the children
				if($key == 0 && in_array($rel, $rel_to_exclude)) {
					$display = false;
					break;
				}
				$rel_to_find = array('sister', 'brother', 'sibling'); // one of these relationships must be in the path
				if(in_array($rel, $rel_to_find)) {
					$display = true;
					break;
				}
			}

			if($display == true) {
				foreach ($path as $rel) {
					$combined_path .= substr($rel, 0, 3);
				}
				return get_relationship_name_from_path($combined_path, $person, $spouse);
			}
		}
	}

	public function check_privacy($record, $xrefs = false) {
		$count = 0;
		foreach ($record as $person) {
			if ($xrefs) {
				$person = $this->get_person($person);
			}
			if($person->canDisplayDetails()) {
				$count++;
			}
		}
		if ($count < 1) {
			return true;
		}
	}

	public function getImageData() {
		Zend_Session::writeClose();
		header('Content-type: text/html; charset=UTF-8');
		$xref = WT_Filter::get('mid');
		$mediaobject = WT_Media::getInstance($xref);
		if ($mediaobject) {
			echo $mediaobject->getServerFilename();
		}
	}

	/**
	 * Get the parents of this person
	 *
	 * @param type $pid
	 * @return array of xrefs
	 */
	private function getParents($pid) {
		$this->individual	 = $this->get_person($pid);
		$family	 = $this->individual->getPrimaryChildFamily();

		if ($family) {
			foreach ($family->getSpouses() as $parent) {
				$parents[] = $parent->getXref();
			}
			return $parents;
		}
	}

	/**
	 * Load the ancestors of an individual to retrieve the sosa numbers
	 *
	 * @param Individual $ancestor
	 * @param int        $sosa
	 */
	private function loadAncestors(WT_Person $ancestor, $sosa) {
		if ($ancestor) {
			$this->ancestors[$sosa]	 = $ancestor;
			$family					 = $ancestor->getPrimaryChildFamily();
			if ($family) {
				foreach ($family->getSpouses() as $parent) {
					$this->loadAncestors($parent, $sosa * 2 + ($parent->getSex() == 'F' ? 1 : 0));
				}
			}
		}
	}

	/**
	 * Print back-to-top link
	 *
	 * @param type $i
	 * @return string
	 */
	protected function printBackToTopLink($i) {
		if ($i > 1) {
			return '<a href="#body" class="header-link scroll noprint">' . WT_I18N::translate('back to top') . '</a>';
		}
	}

}
