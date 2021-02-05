<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2021 kiwitrees.net
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

/**
 * Definitions for a census
 */
class KT_Census_CensusOfUnitedStates1930 extends KT_Census_CensusOfUnitedStates implements KT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return 'APR 1930';
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
            new KT_Census_CensusColumnSexMF($this, 'Sex', 'Sex'),
			new KT_Census_CensusColumnNull($this, 'Race', 'Color or race'),
			new KT_Census_CensusColumnAge($this, 'Age', 'Age at last birthday'),
			new KT_Census_CensusColumnConditionUs($this, 'Cond', 'Whether single, married, widowed, or divorced'),
			new KT_Census_CensusColumnAgeMarried($this, 'AM', 'Age at first marriage'),
			new KT_Census_CensusColumnNull($this, 'School', 'Attended school since Sept. 1, 1929'),
			new KT_Census_CensusColumnNull($this, 'R/W', 'Whether able to read and write'),
			new KT_Census_CensusColumnBirthPlaceSimple($this, 'BP', 'Place of birth'),
			new KT_Census_CensusColumnFatherBirthPlaceSimple($this, 'FBP', 'Place of birth of father'),
			new KT_Census_CensusColumnMotherBirthPlaceSimple($this, 'MBP', 'Place of birth of mother'),
			new KT_Census_CensusColumnOccupation($this, 'Occupation', 'Trade, profession, or particular kind of work done'),
			new KT_Census_CensusColumnNull($this, 'Industry', 'Industry, business of establishment in which at work'),
			new KT_Census_CensusColumnNull($this, 'Vet', 'Whether a veteran of U.S. military'),
		);
	}
}
