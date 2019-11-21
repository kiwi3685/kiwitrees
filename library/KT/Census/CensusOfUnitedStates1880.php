<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2018 kiwitrees.net
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
class KT_Census_CensusOfUnitedStates1880 extends KT_Census_CensusOfUnitedStates implements KT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return 'JUN 1880';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new KT_Census_CensusColumnSurnameGivenNameInitial($this, 'Name', 'Name', 'width: 250px;'),
			new KT_Census_CensusColumnNull($this, 'Color', 'White, Black, Mulatto, Chinese, Indian'),
			new KT_Census_CensusColumnSexMF($this, 'Sex', 'Sex'),
			new KT_Census_CensusColumnAge($this, 'Age', 'Age'),
			new KT_Census_CensusColumnRelationToHead($this, 'Relation', 'Relation to head of household'),
			new KT_Census_CensusColumnConditionUs($this, 'Cond', 'Whether single, married, widowed, or divorced'),
			new KT_Census_CensusColumnOccupation($this, 'Occupation', 'Profession, occupation, or trade'),
			new KT_Census_CensusColumnNull($this, 'Infirm', 'Whether deaf and dumb, blind, insane, or idiotic'),
			new KT_Census_CensusColumnNull($this, 'R/W', 'Cannot read / write'),
			new KT_Census_CensusColumnBirthPlaceSimple($this, 'BP', 'Place of birth, naming the state, territory, or country'),
			new KT_Census_CensusColumnFatherBirthPlaceSimple($this, 'FBP', 'Place of birth of father, naming the state, territory, or country'),
			new KT_Census_CensusColumnMotherBirthPlaceSimple($this, 'MBP', 'Place of birth of mother, naming the state, territory, or country'),
		);
	}
}
