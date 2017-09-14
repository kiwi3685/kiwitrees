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

/**
 * Definitions for a census column
 */
class KT_Census_AbstractCensusColumn {
	/** @var CensusInterface */
	private $census;

	/** @var string */
	private $abbr;

	/** @var string */
	private $title;

	/** @var string */
	private $style;

	/**
	 * Create a column for a census
	 *
	 * @param KT_Census_CensusInterface $census - The census to which this column forms part.
	 * @param string          $abbr   - The abbrievated on-screen name "BiC"
	 * @param string          $title  - The full column heading "Born in the county"
	 * @param string          $style  - Optional additional styling for column headers "width: 250px;"
	 */
	public function __construct(KT_Census_CensusInterface $census, $abbr, $title, $style='') {
		$this->census = $census;
		$this->abbr   = $abbr;
		$this->title  = $title;
		$this->style  = $style;
	}

	/**
	 * A short version of the column's name.
	 *
	 * @return string
	 */
	public function abbreviation() {
		return $this->abbr;
	}

	/**
	 * Extract the country (last part) of a place name.
	 *
	 * @param string $place - e.g. "London, England"
	 *
	 * @return string - e.g. "England"
	 */
	protected function lastPartOfPlace($place) {
		$place = explode(', ', $place);

		return end($place);
	}

	/**
	 * When did this census occur
	 *
	 * @return Date
	 */
	public function date() {
		return new KT_Date($this->census->censusDate());
	}

	/**
	 * Find the father of an individual
	 *
	 * @param Individual $individual
	 *
	 * @return Individual|null
	 */
	public function father(KT_Person $individual) {
		$family = $individual->getPrimaryChildFamily();

		if ($family) {
			return $family->getHusband();
		} else {
			return null;
		}
	}

	/**
	 * Find the mother of an individual
	 *
	 * @param Individual $individual
	 *
	 * @return Individual|null
	 */
	public function mother(KT_Person $individual) {
		$family = $individual->getPrimaryChildFamily();

		if ($family) {
			return $family->getWife();
		} else {
			return null;
		}
	}

	/**
	 * Remove the country of a place name, where it is the same as the census place
	 *
	 * @param string $place - e.g. "London, England"
	 *
	 * @return string - e.g. "London" (for census of England) and "London, England" elsewhere
	 */
	protected function notCountry($place) {
		$parts = explode(', ', $place);

		if (end($parts) === $this->place()) {
			return implode(', ', array_slice($parts, 0, -1));
		} else {
			return $place;
		}
	}

	/**
	 * Where did this census occur
	 *
	 * @return string
	 */
	public function place() {
		return $this->census->censusPlace();
	}

	/**
	 * Find the current spouse family of an individual
	 *
	 * @param Individual $individual
	 *
	 * @return Family|null
	 */
	public function spouseFamily(KT_Person $individual) {
		// Exclude families that were created after this census date
		$families = array();
		foreach ($individual->getSpouseFamilies() as $family) {
			if (KT_Date::Compare($family->getMarriageDate(), $this->date()) <= 0) {
				$families[] = $family;
			}
		}

		if (empty($families)) {
			return null;
		} else {
			usort($families, function (KT_Family $x, KT_Family $y) { return KT_Date::Compare($x->getMarriageDate(), $y->getMarriageDate()); });

			return end($families);
		}
	}

	/**
	 * The full version of the column's name.
	 *
	 * @return string
	 */
	public function title() {
		return $this->title;
	}

	/**
	 * Optional header style
	 *
	 * @return string
	 */
	public function style() {
		return $this->style;
	}
}
