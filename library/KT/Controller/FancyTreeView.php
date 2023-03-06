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
class KT_Controller_FancyTreeView {

	/** @var Individual[] Ancestors of the root person - for SOSA numbers */
	private $ancestors = array();

	/** @var integer count number of individuals in generation */
	private $gencount;

	protected function tree() {
		global $KT_TREE;

		$tree = KT_Tree::getIdFromName(KT_Filter::get('ged'));
		if ($tree) {
			return $tree;
		} else {
			return $KT_TREE;
		}
	}

	/**
	 * Set the default module options
	 *
	 * @param type $key
	 * @return string
	 */
	private function setDefault($key) {
		$FTV_DEFAULT = array(
			'USE_FULLNAME'			 => '0',
			'NUMBLOCKS'				 => '0',
			'CHECK_RELATIONSHIP'	 => '0',
			'SHOW_SINGLES'			 => '0',
			'SHOW_PLACES'			 => '1',
			'USE_GEDCOM_PLACES'		 => '0',
			'COUNTRY'				 => '',
			'SHOW_OCCU'				 => '1',
			'SHOW_SOSA'				 => '1',
			'SHOW_CHIL'				 => '1',
			'SHOW_IMGS'				 => '1',
			'RESIZE_THUMBS'			 => '1',
			'THUMB_SIZE'			 => '60',
			'THUMB_RESIZE_FORMAT'	 => '2',
			'USE_SQUARE_THUMBS'		 => '1',
		);
		return $FTV_DEFAULT[$key];
	}

	/**
	 * Get module options
	 * @param type $k
	 * @return type
	 */
	public function options($module, $k) {
		$FTV_OPTIONS = unserialize((string) get_module_setting($module, 'FTV_OPTIONS'));
		$key		 = strtoupper($k);

		if (empty($FTV_OPTIONS[$this->tree()->getTreeId()]) || (is_array($FTV_OPTIONS[$this->tree()->getTreeId()]) && !array_key_exists($key, $FTV_OPTIONS[$this->tree()->getTreeId()]))) {
			return $this->setDefault($key);
		} else {
			return($FTV_OPTIONS[$this->tree()->getTreeId()][$key]);
		}
	}

	// Get Indis from surname input
	public function indisArray($surname, $soundex_std, $soundex_dm) {
		$sql = "
			SELECT DISTINCT i_id AS xref, i_file AS gedcom_id, i_gedcom AS gedcom
			 FROM `##individuals`
			 JOIN `##name` ON (i_id=n_id AND i_file=n_file)
			 WHERE n_file=?
			 AND n_type!=?
			 AND (n_surn=? OR n_surname=?
		";
		$args = array(KT_GED_ID, '_MARNM', $surname, $surname);
		if ($soundex_std) { // works only with latin letters. For other letters it outputs the code '0000'.
			foreach (explode(':', KT_Soundex::soundex_std($surname)) as $value) {
				if ($value != '0000') {
					$sql .= " OR n_soundex_surn_std LIKE CONCAT('%', ?, '%')";
					$args[] = $value;
				}
			}
		}
		if ($soundex_dm) { // works only with predefined letters and lettercombinations. Fot other letters it outputs the code '000000'.
			foreach (explode(':', KT_Soundex::soundex_dm($surname)) as $value) {
				if ($value != '000000') {
					$sql .= " OR n_soundex_surn_dm LIKE CONCAT('%', ?, '%')";
					$args[] = $value;
				}
			}
		}
		$sql .= ')';
		$rows =
			KT_DB::prepare($sql)
			->execute($args)
			->fetchAll();
		$data = array();
		foreach ($rows as $row) {
			$data[] = KT_Person::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
		}
		return $data;
	}

	// Get surname from pid
	public function getSurname($pid) {
		$sql= "SELECT n_surname AS surname FROM `##name` WHERE n_file=? AND n_id=? AND n_type=?";
		$args = array(KT_GED_ID, $pid, 'NAME');
		$data= KT_DB::prepare($sql)->execute($args)->fetchOne();
		return $data;
	}

	// Add error or success message
//	public function addMessage($controller, $type, $msg) {
//		if ($type == "success") {
//			$class = "ui-state-highlight";
//		}
//		if ($type == "error") {
//			$class = "ui-state-error";
//		}
/*		$controller->addInlineJavaScript('
			jQuery("#error").text("' . $msg . '").addClass("' . $class . '").show("normal");
			setTimeout(function() {
				jQuery("#error").hide("normal");
			}, 10000);
		');

	}
*/
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
	public function sortArray($array, $sort_by) {

		$array_keys = array('tree', 'surname', 'display_name', 'pid', 'access_level', 'sort');

		foreach ($array as $pos =>  $val) {
			$tmp_array[$pos] = $val[$sort_by];
		}
		asort($tmp_array);

		$return_array = array();
		foreach ($tmp_array as $pos => $val) {
			foreach ($array_keys as $key) {
				$key = strtoupper($key);
				$return_array[$pos][$key] = $array[$pos][$key];
			}
		}
		return array_values($return_array);
    }

	public function getCountryList() {
		$list = array();
		$countries =
			KT_DB::prepare("SELECT p_place as country FROM `##places` WHERE p_parent_id=? AND p_file=?")
			->execute(array('0', KT_GED_ID))->fetchAll(PDO::FETCH_ASSOC);

		if ($countries) {
			foreach ($countries as $country) {
				$list[$country['country']] = $country['country']; // set the country as key to display as option value.
			}
		}
		return $list;
	}

	// Reset all settings to default
	public function ftv_reset($module) {
		KT_DB::prepare("DELETE FROM `##module_setting` WHERE module_name LIKE '" . $module . "' AND setting_name LIKE 'FTV%'")->execute();
		AddToLog($module . ' reset to default values', 'auth');
	}

	// Delete item
	public function delete($module) {
		$FTV_SETTINGS = unserialize((string) get_module_setting($module, 'FTV_SETTINGS'));
		unset($FTV_SETTINGS[KT_Filter::getInteger('key')]);
		$NEW_FTV_SETTINGS = array_merge($FTV_SETTINGS);
		set_module_setting($module, 'FTV_SETTINGS',  serialize($NEW_FTV_SETTINGS));
		AddToLog($module . ' item deleted', 'auth');
	}

	// Print functions
	public function printPage($module, $numblocks) {
		$root 		= KT_Filter::get('rootid', KT_REGEX_XREF);
		$gen  		= KT_Filter::get('gen', KT_REGEX_INTEGER);
		$pids 		= KT_Filter::get('pids');

		if ($numblocks || $numblocks == 0) {
			$numblocks = 99;
		}

		$html = '';
		if (!isset($gen) && !isset($pids)) {
			$gen				= 1;
			$numblocks			= $numblocks - 1;
			$this->generation	= array($root);
			$html .= $this->printGeneration($gen, $module);
			$module == 'fancy_treeview_ancestors' ? $this->loadAncestors($this->getPerson($root), 1) : '';
		} else {
			$this->generation = explode('|', $pids);
		}

		$lastblock = $gen + $numblocks + 1;// + 1 to get one hidden block.
		while (count($this->generation) > 0 && $gen < $lastblock) {
			$pids = $this->generation;
			unset($this->generation);

			switch ($module) {
			case 'fancy_treeview_descendants':
				foreach ($pids as $pid) {
					$next_gen[] = $this->getNextGen($pid);
				}
				foreach ($next_gen as $descendants) {
					if (is_array($descendants) && count($descendants) > 0) {
						foreach ($descendants as $descendant) {
							if ($this->options($module, 'show_singles') == true || $descendant['desc'] == 1) {
								$this->generation[] = $descendant['pid'];
							}
						}
					}
				}
				break;
			case 'fancy_treeview_ancestors':
				foreach ($pids as $pid) {
					$parents = $this->getParents($pid);
					if (is_array($parents) && count($parents) > 0) {
						foreach ($parents as $parent) {
							$this->generation[] = $parent;
						}
					}
				}
				break;
			}
			if (!empty($this->generation)) {
				$gen++;
				$module == 'fancy_treeview_ancestors' ? $this->gencount = count($this->generation) : '';
				$html .= $this->printGeneration($gen, $module);
				unset($next_gen, $descendants, $pids);
			} else {
				return $html;
			}
		}
		return $html;
	}

	/**
	 * Print a generation
	 *
	 * @param type $i
	 * @param type $module
	 * @return string
	 */
	protected function printGeneration($i, $module) {
		// added data attributes to retrieve values easily with jquery (for scroll reference en next generations).
		$html = '<li class="block generation-block" data-gen="' . $i . '" data-pids="' . implode('|', $this->generation) . '">' .
			$this->printBlockHeader($i, $module);

		if ($this->checkPrivacy($this->generation, true)) {
			$html .= $this->printPrivateBlock();
		} else {
			$html .= $this->printBlockContent($module, $i);
		}

		$html .= '</li>';

		return $html;
	}

	/**
	 * Print the header for each generation
	 *
	 * @param type $i
	 * @param type $module
	 * @return string
	 */
	protected function printBlockHeader($i, $module) {

		$title = '<span class="header-title">' . KT_I18N::translate('Generation') . ' ' . $i . '</span>';
		if ($module == 'fancy_treeview_ancestors' && $i > 1 ) {
			$gentotal	= pow(2, $i - 1);
			$genperc	= number_format($this->gencount / $gentotal * 100, 2) . '%';
			$title		.= '<span class="header-subtitle">' . /* I18N: display calculation of inviduals recorded per generation on ancestors report */ KT_I18N::translate('(%1s of %2s - %3s complete)', $this->gencount, $gentotal, $genperc) . '</span>
			';
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
	protected function printBlockContent($module, $i) {
		$html = '<ol class="blockcontent generation">';
		foreach (array_unique($this->generation) as $pid) {
			$person = $this->getPerson($pid);
			if (!$this->hasParentsInSameGeneration($person)) {
				$family = $this->getFamily($person);
				if (!empty($family)) {
					$id = $family->getXref();
				} else {
					if ($this->options($module, 'show_singles') == true || !$person->getSpouseFamilies()) {
						$id = 'S' . $pid;
					} // Added prefix (S = Single) to prevent double id's.
				}
				$class = $person->canDisplayDetails() ? 'family' : 'family private';
				$html .= '<li id="' . $id . '" class="' . $class . '">' . $this->printPerson($person, $module, $i) . '</li>';
			}
		}
		$html .= '</ol>';
		return $html;
	}

	/**
	 * Print back-to-top link
	 *
	 * @param type $i
	 * @return string
	 */
	protected function printBackToTopLink($i) {
		if ($i > 1) {
			return '<a href="#fancy_treeview_descendants-page" class="header-link scroll">' . KT_I18N::translate('back to top') . '</a>';
		}
	}

	/**
	 * Print private block content
	 *
	 * @return string
	 */
	protected function printPrivateBlock() {
		return
			'<div class="blockcontent generation private">' .
				KT_I18N::translate('The details of this generation are private.') .
			'</div>';
	}

	/**
	 * Print the content for one person
	 *
	 * @param type $person
	 * @param type $module
	 * @return string (html)
	 */
	public function printPerson($person, $module, $i) {
		global $SHOW_PRIVATE_RELATIONSHIPS;

		if ($person->canDisplayDetails()) {
			$html = '<div class="parents">';
				$this->options($module, 'show_imgs') ? $html .= $this->printThumbnail($person, $module) : $html .= '';
				$html .=  $this->printNameUrl($person, $person->getXref());
				if ($module == 'fancy_treeview_ancestors' && $this->options($module, 'show_sosa')) {
					$sosa = array_search($person, $this->ancestors, true);
					$sosa ? $html .= '<sup class="sosa" title="' . KT_I18N::translate('Sosa number') . '">' . $sosa . '</sup>' : $html;
				}
				if ($this->options($module, 'show_occu')) {
					$html .= $this->printOccupations($person);
					$this->printOccupations($person) ? $html .= ', ' : $html .= '';
				}

				$html .= $this->printParents($person) . $this->printLifespan($module, $person);

				// get a list of all the spouses
				// First, determine the true number of spouses by checking the family gedcom
				$spousecount = 0;
				foreach ($person->getSpouseFamilies(KT_PRIV_HIDE) as $i => $family) {
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
				if ($spousecount > 0) {
					$spouseindex = 0;
					foreach ($person->getSpouseFamilies(KT_PRIV_HIDE) as $i => $family) {
						$spouse = $family->getSpouse($person);
						if ($spouse && $spouse->canDisplayDetails()) {
							$marriage = $family->getMarriage();
							if ($marriage && $marriage->canShow()) {
								$html .= $this->printSpouse($family, $person, $spouse, $spouseindex, $spousecount, $module);
								$spouseindex ++;
							} else {
								$html .= $this->printPartner($family, $person, $spouse, $module);
							}

						}
					}
				}

				// get children for each couple (could be none or just one, $spouse could be empty, includes children of non-married couples)
				// print children only once per couple on the ancestors version, if the "show children" option is selected.
				// do not print children of gen 1 ($i) couple if this is ancestors report
				if ($module == 'fancy_treeview_descendants' || ($module == 'fancy_treeview_ancestors' && $i > 1 && $person->getSex() === 'F' && $this->options($module, 'show_chil'))) {
					foreach ($person->getSpouseFamilies(KT_PRIV_HIDE) as $family) {
						$spouse = $family->getSpouse($person);
						$html .= $this->printChildren($family, $person, $spouse, $module);
					}
				}

			$html .= '</div>';

			return $html;
		} else {
			if ($SHOW_PRIVATE_RELATIONSHIPS) {
				return KT_I18N::translate('The details of this family are private.');
			}
		}
	}

	public function printSpouse($family, $person, $spouse, $i, $count, $module) {

		$html = ' ';

		if ($count > 1) {
			// we assume no one married more then 15 times.
			// these need to be male/female for some languages
			$wordcountM = array(
				KT_I18N::translate_c('MALE', 'first'),
				KT_I18N::translate_c('MALE', 'second'),
				KT_I18N::translate_c('MALE', 'third'),
				KT_I18N::translate_c('MALE', 'fourth'),
				KT_I18N::translate_c('MALE', 'fifth'),
				KT_I18N::translate_c('MALE', 'sixth'),
				KT_I18N::translate_c('MALE', 'seventh'),
				KT_I18N::translate_c('MALE', 'eighth'),
				KT_I18N::translate_c('MALE', 'ninth'),
				KT_I18N::translate_c('MALE', 'tenth'),
				KT_I18N::translate_c('MALE', '11th'),
				KT_I18N::translate_c('MALE', '12th'),
				KT_I18N::translate_c('MALE', '13th'),
				KT_I18N::translate_c('MALE', '14th'),
				KT_I18N::translate_c('MALE', '15th')
			);

			$wordcountF = array(
				KT_I18N::translate_c('FEMALE', 'first'),
				KT_I18N::translate_c('FEMALE', 'second'),
				KT_I18N::translate_c('FEMALE', 'third'),
				KT_I18N::translate_c('FEMALE', 'fourth'),
				KT_I18N::translate_c('FEMALE', 'fifth'),
				KT_I18N::translate_c('FEMALE', 'sixth'),
				KT_I18N::translate_c('FEMALE', 'seventh'),
				KT_I18N::translate_c('FEMALE', 'eighth'),
				KT_I18N::translate_c('FEMALE', 'ninth'),
				KT_I18N::translate_c('FEMALE', 'tenth'),
				KT_I18N::translate_c('FEMALE', '11th'),
				KT_I18N::translate_c('FEMALE', '12th'),
				KT_I18N::translate_c('FEMALE', '13th'),
				KT_I18N::translate_c('FEMALE', '14th'),
				KT_I18N::translate_c('FEMALE', '15th')
			);

			$wordcount2M = array(
				KT_I18N::translate_c('MALE', 'once'),
				KT_I18N::translate_c('MALE', 'twice'),
				KT_I18N::translate_c('MALE', 'three times'),
				KT_I18N::translate_c('MALE', 'four times'),
				KT_I18N::translate_c('MALE', 'five times'),
				KT_I18N::translate_c('MALE', 'six times'),
				KT_I18N::translate_c('MALE', 'seven times'),
				KT_I18N::translate_c('MALE', 'eight times'),
				KT_I18N::translate_c('MALE', 'nine times'),
				KT_I18N::translate_c('MALE', 'ten times'),
				KT_I18N::translate_c('MALE', '11 times'),
				KT_I18N::translate_c('MALE', '12 times'),
				KT_I18N::translate_c('MALE', '13 times'),
				KT_I18N::translate_c('MALE', '14 times'),
				KT_I18N::translate_c('MALE', '15 times')
			);

			$wordcount2F = array(
				KT_I18N::translate_c('FEMALE', 'once'),
				KT_I18N::translate_c('FEMALE', 'twice'),
				KT_I18N::translate_c('FEMALE', 'three times'),
				KT_I18N::translate_c('FEMALE', 'four times'),
				KT_I18N::translate_c('FEMALE', 'five times'),
				KT_I18N::translate_c('FEMALE', 'six times'),
				KT_I18N::translate_c('FEMALE', 'seven times'),
				KT_I18N::translate_c('FEMALE', 'eight times'),
				KT_I18N::translate_c('FEMALE', 'nine times'),
				KT_I18N::translate_c('FEMALE', 'ten times'),
				KT_I18N::translate_c('FEMALE', '11 times'),
				KT_I18N::translate_c('FEMALE', '12 times'),
				KT_I18N::translate_c('FEMALE', '13 times'),
				KT_I18N::translate_c('FEMALE', '14 times'),
				KT_I18N::translate_c('FEMALE', '15 times')
			);
			switch ($person->getSex()) {
				case 'M':
					if ($i == 0) {
						$html .= '<br>' . /* I18N: %s is a number  */ KT_I18N::translate('He married %s', $wordcount2M[$count-1]) . '. ';
					}
					$html .= /* I18N: %s is an ordinal */ KT_I18N::translate('The %s time he married', $wordcountM[$i]);
					break;
				case 'F':
					if ($i == 0) {
						$html .= /* I18N: %s is a number  */ KT_I18N::translate('She married %s', $wordcount2F[$count-1]) . '. ';
					}
					$html .= /* I18N: %s is an ordinal */ KT_I18N::translate('The %s time she married', $wordcountF[$i]);
					break;
				default:
					if ($i == 0) {
						$html .= /* I18N: %s is a number  */ KT_I18N::translate('This individual married %s', $wordcount2M[$count-1]) . '. ';
					}
					$html .= /* I18N: %s is an ordinal */ KT_I18N::translate('The %s time this individual married', $wordcountM[$i]);
					break;
			}
		} else {
			switch ($person->getSex()) {
				case 'M':
					$html .= KT_I18N::translate('He married');
					break;
				case 'F':
					$html .= KT_I18N::translate('She married');
					break;
				default:
					$html .= KT_I18N::translate('This individual married');
					break;
			}
		}

		$html .= ' <a href="' . $spouse->getHtmlUrl() . '">' . $spouse->getFullName() . '</a>';
		$html .= $this->printRelationship($person, $spouse, $module);
		$html .= $this->printParents($spouse);

		if (!$family->getMarriage()) { // use the default privatized function to determine if marriage details can be shown.
			$html .= '.';
		} else {
			// use the facts below only on none private records.
			$marrdate = $family->getMarriageDate();
			$marrplace = $family->getMarriagePlace();
			if ($marrdate && $marrdate->isOK()) {
				$html .= $this->printDate($marrdate);
			}
			if (!is_null($family->getMarriagePlace())) {
				$html .= $this->printPlace($family->getMarriagePlace(), $module);
			}
			$html .= $this->printLifespan($module, $spouse, true);

			if ($family->isDivorced()) {
				$html .= /* I18N: details of a couple divorce */KT_I18N::translate('They divorced %s.', $this->printDivorceDate($family));
			}
		}
		return $html;
	}

	public function printPartner($family, $person, $spouse, $module) {
		$html = ' ';
		switch ($person->getSex()) {
			case 'M':
				$html .= '<br>' . KT_I18N::translate('He had a relationship with');
				break;
			case 'F':
				$html .= '<br>' . KT_I18N::translate('She had a relationship with');
				break;
			default:
				$html .= '<br>' . KT_I18N::translate('This individual had a relationship with');
				break;
		}
		$html .= ' <a href="' . $spouse->getHtmlUrl() . '">' . $spouse->getFullName() . '</a>';
		$html .= $this->printRelationship($person, $spouse, $family, $module);
		$html .= $this->printParents($spouse);
		$life = $this->printLifespan($module, $spouse, true);
		if ($family->getFacts('_NMR') && $life) {
			$html .= $life;
		}
		return $html;
	}

	public function printChildren($family, $person, $spouse, $module) {
		$html = '';
		$match = null;
		if (preg_match('/\n1 NCHI (\d+)/', $family->getGedcomRecord(), $match) && $match[1]==0) {
			$html .= '<div class="children"><p>' . $person->getFullName() . ' ';
					if ($spouse && $spouse->canDisplayDetails()) {
						$html .= /* I18N: Note the space at the end of the string */ KT_I18N::translate('and ').$spouse->getFullName() . ' ';
						$html .= KT_I18N::translate_c('Two parents/one child', 'had');
					} else {
						$html .= KT_I18N::translate_c('One parent/one child', 'had');
					}
					$html .= ' ' . /* I18N: 'no' is the number zero  */ KT_I18N::translate('no') . ' ' . KT_I18N::translate('children') . '.</p></div>';
		} else {
			$children = $family->getChildren();
			if ($children) {
				if ($this->checkPrivacy($children)) {
					$html .= '<div class="children">
						<p>' . $person->getFullName() . ' ';
							// needs multiple translations for the word 'had' to serve different languages.
							if ($spouse && $spouse->canDisplayDetails()) {
								$html .= /* I18N: Note the space at the end of the string */ KT_I18N::translate('and ').$spouse->getFullName() . ' ';
								if (count($children) > 1) {
									$html .= KT_I18N::translate_c('Two parents/multiple children', 'had');
								} else {
									$html .= KT_I18N::translate_c('Two parents/one child', 'had');
								}
							} else {
								if (count($children) > 1) {
									$html .= KT_I18N::translate_c('One parent/multiple children', 'had');
								} else {
									$html .= KT_I18N::translate_c('One parent/one child', 'had');
								}
							}
							$html .= ' './* I18N: %s is a number */ KT_I18N::plural('%s child', '%s children', count($children), count($children)) . '.
						</p>
					</div>';
				} else {
					$html .= '<div class="children">
						<p>'. /* I18N: Note the space at the end of the string */ KT_I18N::translate('Children of ') . $person->getFullName();
							if ($spouse && $spouse->canDisplayDetails()) {
								$html .= ' '. /* I18N: Note the space at the end of the string */ KT_I18N::translate('and ');
								if (!$family->getMarriage()) {
									// check relationship first (If a relationship is found the information of this parent is printed elsewhere on the page.)
									if ($this->options($module, 'check_relationship')) {
										$relationship = $this->checkRelationship($person, $spouse);
									}
									if (isset($relationship) && $relationship) {
										$html .= $spouse->getFullName() . ' (' . $relationship.')';
									} else {
										// the non-married spouse is not mentioned in the parents div text or elsewhere on the page. So put a link behind the name.
										$html .= '<a class="tooltip" title="" href="' . $spouse->getHtmlUrl() . '">' . $spouse->getFullName() . '</a>';
										// Print info of the non-married spouse in a tooltip
										$html .= '<span class="tooltip-text">' . $this->printTooltip($spouse) . '</span>';
									}
								} else {
									$html .= $spouse->getFullName();
								}
							}
							$html .= '<ol>';
								foreach ($children as $child) {
									if ($child->canDisplayDetails()) {
										$html .= '<li class="child"><a href="' . $child->getHtmlUrl() . '">' . $child->getFullName() . '</a>';
										$pedi = $child->getChildFamilyPedigree($family->getXref());

										if ($pedi) {
											$html .= ' <span class="pedi">';
											switch ($pedi) {
												case 'foster':
													switch ($child->getSex()) {
														case 'F':
															$html .= KT_I18N::translate_c('FEMALE', 'foster child');
															break;
														default:
															$html .= KT_I18N::translate_c('MALE', 'foster child');
															break;
													}
													break;
												case 'adopted':
													switch ($child->getSex()) {
														case 'F':
															$html .= KT_I18N::translate_c('FEMALE', 'adopted child');
															break;
														default:
															$html .= KT_I18N::translate_c('MALE', 'adopted child');
															break;
													}
													break;
											}
											$html .= '</span>';
										}

										if ($child->getBirthDate()->isOK() || $child->getDeathdate()->isOK()) {
											$html .= '<span class="lifespan"> (' . $child->getLifeSpan() . ')</span>';
										}

										$child_family = $this->getFamily($child);
										$module == 'fancy_treeview_descendants' ? $class = 'scroll' : $class = '';
										if ($child_family) {
											$html .= ' <a class="' . $class . '" href="#' . $child_family->getXref() . '"></a>';
										} else { // just go to the person details in the next generation (added prefix 'S'for Single Individual, to prevent double ID's.)
											if ($this->options($module, 'show_singles') == true) {
												$html .= ' <a class="' . $class . '" href="#S' . $child->getXref() . '"></a>';
											}
										}
										$html .= '</li>';
									} else {
										$html .= '<li class="child private">' . KT_I18N::translate('Private') . '</li>';
									}
								}
							$html .= '</ol>
						</p>
					</div>';
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
						$html .= ' ' . KT_I18N::translate('foster son of') . ' ';
					} elseif ($pedi === 'adopted') {
						$html .= ' ' . KT_I18N::translate('adopted son of') . ' ';
					} else {
						$html .= ' ' . KT_I18N::translate('son of') . ' ';
					}
					break;
				case 'F':
					if ($pedi === 'foster') {
						$html .= ' ' . KT_I18N::translate('foster daughter of') . ' ';
					} elseif ($pedi === 'adopted') {
						$html .= ' ' . KT_I18N::translate('adopted daughter of') . ' ';
					} else {
						$html .= ' ' . KT_I18N::translate('daughter of') . ' ';
					}
					break;
				default:
					if ($pedi === 'foster') {
						$html .= ' ' . KT_I18N::translate_c('MALE', 'foster child of') . ' ';
					} elseif ($pedi === 'adopted') {
						$html .= ' ' . KT_I18N::translate('adopted child of') . ' ';
					} else {
						$html .= ' ' . KT_I18N::translate('child of') . ' ';
					}
			}

			$father = $parents->getHusband();
			$mother = $parents->getWife();

			if ($father) {
				$html .= $father->getFullName();
			}
			if ($father && $mother) {
				$html .= ' ' . /* I18N: Note the space at the end of the string */ KT_I18N::translate('and ');
			}
			if ($mother) {
				$html .= $mother->getFullName();
			}

			return $html;
		}
	}

	/**
	 * Print the name of a person with the link to the individual page
	 *
	 * @param type $person
	 * @param type $xref
	 * @return string
	 */
	protected function printNameUrl($person, $xref = '') {
		if ($xref) {
			$name = ' name="' . $xref . '"';
		} else {
			$name = '';
		}

		$url = '<a' . $name . ' href="' . $person->getHtmlUrl() . '">' . $person->getFullName() . '</a>';

		return $url;
	}

	/**
	 * Print occupations
	 *
	 * @param type $person
	 * @param type $tag
	 * @return string
	 */
	protected function printOccupations(KT_Person $person) {
		$html		 = '';
		$occupations = $person->getAllFactsByType('OCCU', true);
		$count		 = count($occupations);
		foreach ($occupations as $num => $fact) {
			if ($num > 0 && $num === $count - 1) {
				$html .= ' ' . /* I18N: Note the space at the end of the string */ KT_I18N::translate('and ');
			} else {
				$html .= ', ';
			}

			// In the Gedcom file most occupations are probably written with a capital (as a single word)
			// but use lcase/ucase to be sure the occupation is spelled the right way since we are using
			// it in the middle of a sentence.
			// In German all occupations are written with a capital.
			// Are there any other languages where this is the case?
			if (in_array(KT_LOCALE, array('de'))) {
				$html .= rtrim(ucfirst($fact->getDetail()), ".");
			} else {
				$html .= rtrim(lcfirst($fact->getDetail()), ".");
			}

			$date = $this->printDate($fact->getDate('OCCU'));
			if ($date) {
				$html .= ' (' . trim($date) . ')';
			}
		}
		return $html;
	}



	public function printLifespan($module, $person, $is_spouse = false) {
		$html = '';
		$birthdate = $person->getBirthDate();
		$deathdate = $person->getDeathdate();
		$ageOfdeath = get_age_at_event(KT_Date::GetAgeGedcom($birthdate, $deathdate), false);

		$birthdata = false;
		if ($birthdate->isOK() || $person->getBirthPlace() != '') {
			$birthdata = true;
			if ($is_spouse == true) {
				$html .= '. ';
				if ($person->isDead()) {
					$person->getSex() == 'F' ? $html .= KT_I18N::translate_c('PAST', 'She was born') : $html .= KT_I18N::translate_c('PAST', 'He was born');
				}
				else {
					$person->getSex() == 'F' ? $html .= KT_I18N::translate_c('PRESENT', 'She was born') : $html .= KT_I18N::translate_c('PRESENT', 'He was born');
				}
			} else {
				$this->printParents($person) || $this->printFact($person, 'OCCU') ? $html .= ', ' : $html .= ' ';
				if ($person->isDead()) {
					$person->getSex() == 'F' ? $html .= KT_I18N::translate_c('PAST (FEMALE)', 'was born') : $html .= KT_I18N::translate_c('PAST (MALE)', 'was born');
				}
				else {
				 	$person->getSex() == 'F' ? $html .= KT_I18N::translate_c('PRESENT (FEMALE)', 'was born') : $html .= KT_I18N::translate_c('PRESENT (MALE)', 'was born');
				}
			}
			if ($birthdate->isOK()) {
				$html .= $this->printDate($birthdate);
			}
			if ($person->getBirthPlace() != '') {
				$html .= $this->printPlace($person->getBirthPlace(), $module);
			}
		}

		$deathdata = false;
		if ($deathdate->isOK() || $person->getDeathPlace() != '') {
			$deathdata = true;
			if ($birthdata) {
				$html .= ' '. /* I18N: Note the space at the end of the string */ KT_I18N::translate('and ');
				$person->getSex() == 'F' ? $html .= KT_I18N::translate_c('FEMALE', 'died') : $html .= KT_I18N::translate_c('MALE', 'died');
			}
			else {
				$person->getSex() == 'F' ? $html .= '. ' . KT_I18N::translate('She died') : $html .= '. ' . KT_I18N::translate('He died');
			}

			if ($deathdate->isOK()) {
				$html .= $this->printDate($deathdate);
			}
			if ($person->getDeathPlace() != '') {
				$html .= $this->printPlace($person->getDeathPlace(), $module);
			}

			if ($birthdate->isOK() && $deathdate->isOK()) {
				if (KT_Date::getAge($birthdate, $deathdate, 0) < 2) {
					$html .= ' './* I18N: %s is the age of death in days/months; %s is a string, e.g. at the age of 2 months */  KT_I18N::translate_c('age in days/months', 'at the age of %s', $ageOfdeath);
				}
				else {
					$html .= ' './* I18N: %s is the age of death in years; %s is a number, e.g. at the age of 40 */  KT_I18N::translate_c('age in years', 'at the age of %s', $ageOfdeath);
				}
			}
		}

		if ($birthdata || $deathdata) {
			$html .= '. ';
		}

		return $html;
	}

	// some couples are known as not married but have children together. Print the info of the "spouse" parent in a tooltip.
	public function printTooltip($person) {
		$birthdate = $person->getBirthDate();
		$deathdate = $person->getDeathdate();
		$html = '';
		if ($birthdate->isOK()) {
			$html .= '<strong>' . KT_I18N::translate('Birth') . ':</strong> ' . strip_tags($birthdate->Display());
		}
		if ($deathdate->isOK()) {
			$html .= '<br><strong>' . KT_I18N::translate('Death') . ':</strong> ' . strip_tags($deathdate->Display());
		}

		$parents = $person->getPrimaryChildFamily();
		if ($parents) {
			$father = $parents->getHusband();
			$mother = $parents->getWife();
			if ($father) {
				$html .= '<br><strong>' . KT_I18N::translate('Father') . ':</strong> ' . strip_tags($father->getFullName());
			}
			if ($mother) {
				$html .= '<br><strong>' . KT_I18N::translate('Mother') . ':</strong> ' . strip_tags($mother->getFullName());
			}
		}
		return $html;
	}

	/**
	 * Print the relationship between spouses (optional)
	 *
	 * @param type $person
	 * @param type $spouse
	 * @return string
	 */
	protected function printRelationship($person, $spouse, $module) {
		$html = '';
		if ($this->options($module, 'check_relationship')) {
			$relationship = $this->checkRelationship($person, $spouse);
			if ($relationship) {
				$html .= ' (' . $relationship . ')';
			}
		}
		return $html;
	}

	/**
	 * Print the Fancy thumbnail for this individual
	 *
	 * @param type $person
	 * @return thumbnail
	 */
	protected function printThumbnail(KT_Person $person, $module) {
		$mediaobject = $person->findHighlightedMedia();
		if ($mediaobject) {
			$cache_filename = $this->getThumbnail($mediaobject, $module);
			if (is_file($cache_filename)) {
				$imgsize = getimagesize($cache_filename);
				$image	 = '<img' .
					' dir="' . 'auto' . '"' . // For the tool-tip
					' src="module.php?mod=' . $module . '&amp;mod_action=thumbnail&amp;mid=' . $mediaobject->getXref() . '&amp;thumb=2&amp;cb=' . $mediaobject->getEtag() . '"' .
					' alt="' . strip_tags($person->getFullName()) . '"' .
					' title="' . strip_tags($person->getFullName()) . '"' .
					' data-cachefilename="' . basename($cache_filename) . '"' .
					' ' . $imgsize[3] . // height="yyy" width="xxx"
					'>';
				return
					'<a' .
					' class="' . 'gallery' . '"' .
					' href="' . $mediaobject->getHtmlUrlDirect() . '"' .
					' type="' . $mediaobject->mimeType() . '"' .
					' data-obje-url="' . $mediaobject->getHtmlUrl() . '"' .
					' data-obje-note="' . KT_Filter::escapeHtml($mediaobject->getNote()) . '"' .
					' data-title="' . strip_tags($person->getFullName()) . '"' .
					'>' . $image . '</a>';
			} else {
				return $mediaobject->displayImage();
			}
		}
	}

	/**
	 * Function to print dates with the right syntax
	 *
	 * @param type $fact
	 * @return type
	 */
	protected function printDate($date) {
		if ($date && $date->isOK()) {
			if ($date->qual1 || $date->qual2) {
				$dq = $date->qual1 . $date->qual2;
				if (in_array($dq, array('ABT', 'BEF', 'AFT', 'FROM', 'TO', 'BETAND', 'FROMTO'))) {
					return ' ' . $date->Display();
				}
				// other qualifiers ('CAL', 'EST', 'INT') do not need special narrative forms
			}

			if ($date->MinDate()->d > 0) {
				return ' ' . /* I18N: Note the space at the end of the string */ KT_I18N::translate_c('before dateformat dd-mm-yyyy', 'on ') . $date->Display();
			}
			if ($date->MinDate()->m > 0) {
				return ' ' . /* I18N: Note the space at the end of the string */ KT_I18N::translate_c('before dateformat mmm yyyy', 'in ') . $date->Display();
			}
			if ($date->MinDate()->y > 0) {
				return ' ' . /* I18N: Note the space at the end of the string */ KT_I18N::translate_c('before dateformat yyyy', 'in ') . $date->Display();
			}
		}
	}

	public function printDivorceDate($family) {
		foreach ($family->getAllFactsByType(explode('|', KT_EVENTS_DIV)) as $event) {
			// Only display if it has a date
			if ($event->getDate()->isOK() && $event->canShow()) {
				return $this->printDate($event->getDate());
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

	public function printPlace($place, $module) {
		if ($this->options($module, 'show_places') == true) {
			$place = new KT_Place($place, KT_GED_ID);
			$html = ' '. /* I18N: Note the space at the end of the string */ KT_I18N::translate_c('before placesnames', 'in ');
			if	($this->options($module, 'use_gedcom_places') == true) {
				$html .= $place->getShortName();
			} else {
				$country = $this->options($module, 'country');
				$new_place = array_reverse(explode(", ", $place->getGedcomName()));
				if (!empty($country) && $new_place[0] == $country) {
					unset($new_place[0]);
					$html .= '<span dir="auto">' . KT_Filter::escapeHtml(implode(', ', array_reverse($new_place))) . '</span>';
				} else {
					$html .= $place->getFullName();
				}
			}
			return $html;
		}
	}

	// Other functions
	public function getPerson($pid) {
		$person = KT_Person::getInstance($pid);
		return $person;
	}

	public function getFamily($person) {
		foreach ($person->getSpouseFamilies(KT_PRIV_HIDE) as $family) {
			return $family;
		}
	}

	public function getNextGen($pid) {
		$person = $this->getPerson($pid);
		foreach($person->getSpouseFamilies() as $family) {
			$children = $family->getChildren();
			if ($children) {
				foreach ($children as $key => $child) {
					$key = $family->getXref() . '-' . $key; // be sure the key is unique.
					$ng[$key]['pid'] = $child->getXref();
					$child->getSpouseFamilies(KT_PRIV_HIDE) ? $ng[$key]['desc'] = 1 : $ng[$key]['desc'] = 0;
				}
			}
		}
		if (isset($ng)) {
		return $ng;
	}
	}

	// check if a person has parents in the same generation
	public function hasParentsInSameGeneration($person) {
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
			if (in_array($father, $this->generation) || in_array($mother, $this->generation)) {
				return true;
			}
		}
	}

	// check (blood) relationship between partners
	public function checkRelationship($person, $spouse) {
		$controller	 = new KT_Controller_Relationship();
		$paths		 = $controller->calculateRelationships_123456($person, $spouse, 1, 0);
		foreach ($paths as $path) {
			$relationships = $controller->oldStyleRelationshipPath($path);
			if (empty($relationships)) {
				// Cannot see one of the families/individuals, due to privacy;
				continue;
			}
			foreach (array_keys($path) as $n) {
				if ($n % 2 === 1) {
					switch ($relationships[$n]) {
						case 'sis':
						case 'bro':
						case 'sib':
							return get_relationship_name_from_path(implode('', $relationships), $person, $spouse);

					}
				}
			}
		}
	}

	public function checkPrivacy($record, $xrefs = false) {
		$count = 0;
		foreach ($record as $person) {
			if ($xrefs) {
				$person = $this->getPerson($person);
			}
			if ($person->canDisplayDetails()) {
				$count++;
			}
		}
		if ($count < 1) {
			return true;
		}
	}

	/**
	 * Get the parents of this person
	 *
	 * @param type $pid
	 * @return array of xrefs
	 * @return filename
	 */
	 private function getParents($pid) {
 		$parents = array();
 		$this->individual	= $this->getPerson($pid);
 		$family				= $this->individual->getPrimaryChildFamily();
 		if ($family) {
 			foreach ($family->getSpouses() as $parent) {
 				$parents[] = $parent->getXref();
 			}
 		}
 		return $parents;
 	}

	/**
	 * Load the ancestors of an individual to retrieve the sosa numbers
	 *
	 * @param Individual $ancestor
	 * @param int        $sosa
	 */
	private function loadAncestors(KT_Person $ancestor, $sosa) {
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
	 * Get the ftv_cache directory
	 *
	 * @return directory name
	 */
	public function cacheDir($module) {
		return KT_DATA_DIR . $module . '_cache/';
	}

	/**
	 * Get the filename of the cached image
	 *
	 * @param Media $mediaobject
	 * @return filename
	 */
	public function cacheFileName(KT_Media $mediaobject, $module) {
		return $this->cacheDir($module) . $this->tree()->getTreeId() . '-' . $mediaobject->getXref() . '-' . filemtime($mediaobject->getServerFilename()) . '.' . $mediaobject->extension();
	}

	/**
	 * remove all old cached files
	 */
	public function emptyCache($module) {
		foreach (glob($this->cacheDir($module) . '*') as $cache_file) {
			if (is_file($cache_file)) {
				unlink($cache_file);
			}
		}
	}

	/**
	 * Check if thumbnails from cache should be recreated
	 *
	 * @param type $mediaobject
	 * @return string filename
	 */
	private function getThumbnail(KT_Media $mediaobject, $module) {
		$cache_dir = $this->cacheDir($module);

		if (!file_exists($cache_dir)) {
			KT_File::mkdir($cache_dir);
		}

		if (file_exists($mediaobject->getServerFilename())) {
			$cache_filename = $this->cacheFileName($mediaobject, $module);

			if (!is_file($cache_filename)) {
				if ($this->options($module, 'resize_thumbs')) {
					$thumbnail	 = $this->fancyThumb($mediaobject, $module);
					$mimetype	 = $mediaobject->mimeType();
					if ($mimetype === 'image/jpeg') {
						imagejpeg($thumbnail, $cache_filename);
					} elseif ($mimetype === 'image/png') {
						imagepng($thumbnail, $cache_filename);
					} elseif ($mimetype === 'image/gif') {
						imagegif ($thumbnail, $cache_filename);
					} else {
						return;
					}
				} else {
					// if we are using the original thumbnails, copy them to the ftv_cache folder
					// so we can cache them either and output them in the same way we would output the fancy thumbnail.
					try {
						copy($mediaobject->getServerFilename('thumb'), $cache_filename);
					} catch (Exception $ex) {
						// something went wrong while copying the default webtrees image to the ftv cache folder
						// there is a fallback in the function printThumbnail(): output $mediaobject->displayImage();
					}
				}
			}

			return $cache_filename;
		}
	}

	/**
	 * Get the Fancy thumbnail (highlighted image)
	 *
	 * @param type $mediaobject
	 * @return image
	 */
	private function fancyThumb($mediaobject, $module) {
		// option 1 = percentage of original thumbnail
		// option 2 = size in pixels
		$resize_format = $this->options($module, 'thumb_resize_format');
		if ($resize_format === '1') {
			$mediasrc = $mediaobject->getServerFilename('thumb');
		} else {
			$mediasrc = $mediaobject->getServerFilename('main');
		}

		if (is_file($mediasrc)) {
			$thumbsize	 = $this->options($module, 'thumb_size');
			$thumbwidth	 = $thumbheight = $thumbsize;

			$mimetype = $mediaobject->mimeType();
			if ($mimetype === 'image/jpeg' || $mimetype === 'image/png' || $mimetype === 'image/gif') {

				if (![$imagewidth, $imageheight] = getimagesize($mediasrc)) {
					return null;
				}

				switch ($mimetype) {
					case 'image/jpeg':
						$image	 = imagecreatefromjpeg($mediasrc);
						break;
					case 'image/png':
						$image	 = imagecreatefrompng($mediasrc);
						break;
					case 'image/gif':
						$image	 = imagecreatefromgif ($mediasrc);
						break;
				}

				// fallback if image is in the database but not on the server
				if (isset($imagewidth) && isset($imageheight)) {
					$ratio = $imagewidth / $imageheight;
				} else {
					$ratio = 1;
				}

				if ($resize_format === '1') {
					$thumbwidth	 = $thumbwidth / 100 * $imagewidth;
					$thumbheight = $thumbheight / 100 * $imageheight;
				}

				$square = $this->options($module, 'use_square_thumbs');
				if ($square == true) {
					$thumbheight = $thumbwidth;
					if ($ratio < 1) {
						$new_height	 = $thumbwidth / $ratio;
						$new_width	 = $thumbwidth;
					} else {
						$new_width	 = $thumbheight * $ratio;
						$new_height	 = $thumbheight;
					}
				} else {
					if ($resize_format === '1') {
						$new_width	 = $thumbwidth;
						$new_height	 = $thumbheight;
					} elseif ($imagewidth > $imageheight) {
						$new_height	 = $thumbheight / $ratio;
						$new_width	 = $thumbwidth;
					} elseif ($imageheight > $imagewidth) {
						$new_width	 = $thumbheight * $ratio;
						$new_height	 = $thumbheight;
					} else {
						$new_width	 = $thumbwidth;
						$new_height	 = $thumbheight;
					}
				}
				$process = imagecreatetruecolor(round($new_width), round($new_height));
				if ($mimetype == 'image/png') { // keep transparancy for png files.
					imagealphablending($process, false);
					imagesavealpha($process, true);
				}
				imagecopyresampled($process, $image, 0, 0, 0, 0, $new_width, $new_height, $imagewidth, $imageheight);

				if ($square) {
					$thumb = imagecreatetruecolor($thumbwidth, $thumbheight);
				} else {
					$thumb = imagecreatetruecolor($new_width, $new_height);
				}

				if ($mimetype == 'image/png') {
					imagealphablending($thumb, false);
					imagesavealpha($thumb, true);
				}
				imagecopyresampled($thumb, $process, 0, 0, 0, 0, $thumbwidth, $thumbheight, $thumbwidth, $thumbheight);

				imagedestroy($process);
				imagedestroy($image);

				return $thumb;
			}
		}
	}

	// Implement KT_Module_Menu
	public function getFTVMenu() {

		global $SEARCH_SPIDER;

		$FTV_SETTINGS		= array();
		$FTV_SETTINGS_D		= unserialize((string) get_module_setting('fancy_treeview_descendants', 'FTV_SETTINGS'));
		$FTV_SETTINGS_A		= unserialize((string) get_module_setting('fancy_treeview_ancestors', 'FTV_SETTINGS'));
		$FTV_GED_SETTINGS_D = array();
		$FTV_GED_SETTINGS_A = array();

		if ($FTV_SETTINGS_D && $FTV_SETTINGS_A) {
			$FTV_SETTINGS	= array_replace($FTV_SETTINGS_D, $FTV_SETTINGS_A);
		}
		if ($FTV_SETTINGS_D && !$FTV_SETTINGS_A) {
			$FTV_SETTINGS	= $FTV_SETTINGS_D;
		}
		if ($FTV_SETTINGS_A && !$FTV_SETTINGS_D) {
			$FTV_SETTINGS	= $FTV_SETTINGS_A;
		}

		if (!empty($FTV_SETTINGS)) {
			if ($SEARCH_SPIDER) {
				return null;
			}

			if ($FTV_SETTINGS_D) {
				foreach ($FTV_SETTINGS_D as $FTV_ITEM) {
					if ($FTV_ITEM['TREE'] == KT_GED_ID && $FTV_ITEM['ACCESS_LEVEL'] >= KT_USER_ACCESS_LEVEL) {
						$FTV_GED_SETTINGS_D[] = $FTV_ITEM;
					}
				}
			}
			if ($FTV_SETTINGS_A) {
				foreach ($FTV_SETTINGS_A as $FTV_ITEM) {
					if ($FTV_ITEM['TREE'] == KT_GED_ID && $FTV_ITEM['ACCESS_LEVEL'] >= KT_USER_ACCESS_LEVEL) {
						$FTV_GED_SETTINGS_A[] = $FTV_ITEM;
					}
				}
			}

			if ($FTV_SETTINGS) {
				$menu = new KT_Menu(KT_I18N::translate('Overview'), '#', 'menu-fancy_treeview_descendants');
				if ($FTV_GED_SETTINGS_A) {
					foreach($FTV_GED_SETTINGS_A as $FTV_ITEM) {
						if (KT_Person::getInstance($FTV_ITEM['PID']) && KT_Person::getInstance($FTV_ITEM['PID'])->canDisplayDetails() ) {
							$submenu = new KT_Menu(KT_I18N::translate('Ancestors of %s', KT_Person::getInstance($FTV_ITEM['PID'])->getFullName()), 'module.php?mod=fancy_treeview_ancestors&amp;mod_action=show&amp;type=overview&amp;rootid=' . $FTV_ITEM['PID'], 'menu-fancy_treeview_descendants-' . $FTV_ITEM['PID']);
							$menu->addSubmenu($submenu);
						}
					}
				}
				if ($FTV_GED_SETTINGS_D) {
					foreach ($FTV_GED_SETTINGS_D as $FTV_ITEM) {
						if (KT_Person::getInstance($FTV_ITEM['PID'])) {
							if ($this->options('fancy_treeview_descendants', 'use_fullname') == true) {
								$submenu = new KT_Menu(KT_I18N::translate('Descendants of %s', KT_Person::getInstance($FTV_ITEM['PID'])->getFullName()), 'module.php?mod=fancy_treeview_descendants&amp;mod_action=show&amp;type=overview&amp;rootid=' . $FTV_ITEM['PID'], 'menu-fancy_treeview_descendants-' . $FTV_ITEM['PID']);
							} else {
								$submenu = new KT_Menu(KT_I18N::translate('%s family descendants', $FTV_ITEM['DISPLAY_NAME']), 'module.php?mod=fancy_treeview_descendants&amp;mod_action=show&amp;type=overview&amp;rootid=' . $FTV_ITEM['PID'], 'menu-fancy_treeview_descendants-' . $FTV_ITEM['PID']);
							}
							$menu->addSubmenu($submenu);
						}
					}
				}
				return $menu;
			}
		}
	}


}
