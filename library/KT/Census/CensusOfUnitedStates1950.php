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
class KT_Census_CensusOfUnitedStates1950 extends KT_Census_CensusOfUnitedStates implements KT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return 'APR 1950';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new KT_Census_CensusColumnSurnameGivenNameInitial($this, 'Name', 'Name', 'width: 200px;'),
			new KT_Census_CensusColumnRelationToHead($this, 'Rel', 'Relationship of each person to the head of the family'),
			new KT_Census_CensusColumnNull($this, 'Race', 'Color or race'),
			new KT_Census_CensusColumnSexMF($this, 'Sex', 'Sex'),
			new KT_Census_CensusColumnAge($this, 'Age', 'Age at last birthday'),
			new KT_Census_CensusColumnConditionUs($this, 'Cond', 'Whether married, widowed, divorced, separated, or never'),
			new KT_Census_CensusColumnBirthPlaceSimple($this, 'BP', 'State or Country of birth'),
			new KT_Census_CensusColumnNull($this, 'Nat', 'Is naturalized?'),
			new KT_Census_CensusColumnNull($this, 'Occ.', 'Occupation category in last week - W: Worker, H: Housework, Ot: Other, U: Unable to work'),
			new KT_Census_CensusColumnOccupation($this, 'Occupation', 'Trade, profession, or particular kind of work done'),
			new KT_Census_CensusColumnNull($this, 'Work', 'Class of worker - P: Private, G: Government, O: Own business, NP: Without pay'),
		);
	}
}
