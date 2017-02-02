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

class fancy_treeview_pedigree_class_WT_Module extends fancy_treeview_WT_Module {

	/** @var Individual[] Ancestors of the root person - for SOSA numbers */
	private $ancestors = array();

	/** @var integer count number of individuals in generation */
	private $gencount;

	// Other functions
	public function get_person($pid) {
		$person = WT_Person::getInstance($pid);
		return $person;
	}

	/** {@inheritdoc} */
	public function printPage($numblocks) {
		require_once WT_MODULES_DIR . 'fancy_treeview/fancy_treeview_class.php';
		$ftv = new fancy_treeview_class_WT_Module();

		$root	= WT_Filter::get('rootid', WT_REGEX_XREF);
		$gen	= WT_Filter::get('gen', WT_REGEX_INTEGER);
		$pids	= WT_Filter::get('pids');

		if ($numblocks == 0) {
			$numblocks = 99;
		}

		$html = '';
		if (!isset($gen) && !isset($pids)) {
			$gen			  = 1;
			$numblocks		  = $numblocks - 1;
			$this->generation = array($root);
			$this->gencount	  = 1;
			$this->loadAncestors($this->get_person($root), 1);
			$html .= $this->print_generation($this->generation, $gen);
		} else {
			$this->generation = explode('|', $pids);
		}

		$lastblock = $gen + $numblocks + 1; // + 1 to get one hidden block.
		while (count($this->generation) > 0 && $gen < $lastblock) {
			$pids = $this->generation;
			unset($this->generation);

			foreach ($pids as $pid) {
				$parents = $this->getParents($pid);
				if (count($parents) > 0) {
					foreach ($parents as $parent) {
						$this->generation[] = $parent;
					}
				}
			}

			if (!empty($this->generation)) {
				$gen++;
				$this->gencount = count($this->generation);
				$html .= $this->print_generation($this->generation, $gen);
				unset($parents, $pids);
			} else {
				return $html;
			}
		}
		return $html;
	}

	/** {@inheritdoc} */
	protected function printBlockHeader($i) {
		$gentotal	= pow(2, $i - 1);
		$genperc	= number_format($this->gencount / $gentotal * 100, 2) . '%';
		$html = '
			<div class="blockheader ui-state-default">
			<span class="header-title">' . WT_I18N::translate('Generation') . ' ' . $i . ' (' . $this->gencount . ' ' . WT_I18N::translate('of') . ' ' . pow(2, $i - 1) . ' - ' . $genperc . ' ' . WT_I18N::translate('complete') . ')</span>';
			if($i > 1) {
				$html .= '<a href="#body" class="header-link scroll noprint">' . WT_I18N::translate('back to top') . '</a>';
			}
			$html .= '</div>';

		return $html;
	}

	/** {@inheritdoc} */
	protected function printIndividual() {

		if ($this->individual->canDisplayDetails()) {
			$sosa	 = array_search($this->individual, $this->ancestors, true);
			$html	 = '<div class="parents">' . $this->printThumbnail($this->individual) . '<p class="desc">' . $this->printNameUrl($this->individual, $this->individual->getXref()) . '<sup>' . $sosa . '</sup>';
			if ($this->options('show_occu')) {
				$html .= $this->printOccupations($this->individual);
			}

			$html .= $this->printParents($this->individual) . $this->printLifespan($this->individual) . '.';
			$html .= '</p></div>';

			return $html;
		} else {
			if ($this->individual->getTree()->getPreference('SHOW_PRIVATE_RELATIONSHIPS')) {
				return WT_I18N::translate('The details of this person are private.');
			}
		}
	}

	public function print_generation($generation, $i) {
		require_once WT_MODULES_DIR . 'fancy_treeview/fancy_treeview_class.php';
		$ftv = new fancy_treeview_class_WT_Module();

		// added data attributes to retrieve values easily with jquery (for scroll reference to next generations).
		$html = $this->printBlockHeader($i);

		if ($ftv->check_privacy($generation, true)) {
			$html .= '<div class="blockcontent generation private">' . WT_I18N::translate('The details of this generation are private.') . '</div>';
		}

		else {
			$html .= '<ol class="blockcontent generation">';
			$generation = array_unique($generation); // needed to prevent the same family added twice to the generation block (this is the case when parents have the same ancestors and are both members of the previous generation).

			foreach ($generation as $pid) {
				$person = $this->get_person($pid);

				// only list persons without parents in the same generation - if they have they will be listed in the next generation anyway.
				// This prevents double listings
				if(!$ftv->has_parents_in_same_generation($person, $generation)) {
					$family = $ftv->get_family($person);
					if(!empty($family)) {
						$id = $family->getXref();
					}
					else {
						if ($this->options('show_singles') == true || !$person->getSpouseFamilies()) {
							$id = 'S' . $pid;
						} // Added prefix (S = Single) to prevent double id's.
					}
					$class = $person->canDisplayDetails() ? 'family' : 'family private';
					$html .= '<li id="' . $id . '" class="' . $class . '">' . $ftv->print_person($person) . '</li>';
				}
			}
			$html .= '</ol></li>';
		}
		return $html;
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

}
