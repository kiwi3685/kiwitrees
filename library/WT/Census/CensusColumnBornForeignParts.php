<?php
/**
 * webtrees: online genealogy
 * Copyright (C) 2017 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Was the individual born in "foreign parts".
 */
class WT_Census_CensusColumnBornForeignParts extends WT_Census_AbstractCensusColumn implements WT_Census_CensusColumnInterface {
	/**
	 * Generate the likely value of this census column, based on available information.
	 *
	 * @param WT_Person     $individual
	 * @param Individual|null $head
	 *
	 * @return string
	 */
	public function generate(WT_Person $individual, WT_Person $head = null) {
		$birth_place  = explode(', ', $individual->getBirthPlace());
		$birth_place  = end($birth_place);
		$census_place = $this->place();

		if ($birth_place === 'Wales') {
			$birth_place = 'England';
		}

		if ($census_place === 'Wales') {
			$census_place = 'England';
		}

		if ($birth_place === $census_place || $birth_place === '') {
			return '';
		} elseif ($birth_place === 'England' || $birth_place === 'Scotland' || $birth_place === 'Ireland') {
			return substr($birth_place, 0, 1);
		} else {
			return 'F';
		}
	}
}
