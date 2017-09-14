<?php
/**
 * webtrees: online genealogy
 * Copyright (C) 2015 webtrees development team
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
 * Definitions for a census
 */
class KT_Census_CensusOfUnitedStates1790 extends KT_Census_CensusOfUnitedStates implements KT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '02 AUG 1790';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new KT_Census_CensusColumnFullName($this, 'Name', 'Name of head of family'),
			new KT_Census_CensusColumnOccupation($this, 'Occupation', 'Professions and occupation'),
			new KT_Census_CensusColumnNull($this, 'White male 16+', 'White male of 16 yrs upward'),
			new KT_Census_CensusColumnNull($this, 'White male 0-16', 'White males of under 16 yrs'),
			new KT_Census_CensusColumnNull($this, 'White female', 'All White Females'),
			new KT_Census_CensusColumnNull($this, 'Free', 'All other free persons'),
			new KT_Census_CensusColumnNull($this, 'Slaves', 'Number of slaves'),
			new KT_Census_CensusColumnNull($this, 'Total', 'Total'),
		);
	}
}
