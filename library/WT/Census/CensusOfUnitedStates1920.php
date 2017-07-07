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
 * Definitions for a census
 */
class WT_Census_CensusOfUnitedStates1920 extends WT_Census_CensusOfUnitedStates implements WT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return 'JAN 1920';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new WT_Census_CensusColumnSurnameGivenNameInitial($this, 'Name', 'Name'),
			new WT_Census_CensusColumnRelationToHead($this, 'Relation', 'Relationship of each person to the head of the family'),
			new WT_Census_CensusColumnNull($this, 'Home', 'Owned or rented'),
			new WT_Census_CensusColumnNull($this, 'Mort', 'If owned, free or mortgaged'),
			new WT_Census_CensusColumnSexMF($this, 'Sex', 'Sex'),
			new WT_Census_CensusColumnNull($this, 'Race', 'Color or race'),
			new WT_Census_CensusColumn($this, 'Age', 'Age at last birthday'),
			new WT_Census_CensusColumnConditionUs($this, 'Condition', 'Whether single, married, widowed, or divorced'),
			new WT_Census_CensusColumnNull($this, 'Imm', 'Year of immigration to the United States'),
			new WT_Census_CensusColumnNull($this, 'Nat', 'Naturalized or alien'),
			new WT_Census_CensusColumnNull($this, 'NatY', 'If naturalized, year of naturalization'),
			new WT_Census_CensusColumnNull($this, 'School', 'Attended school since Sept. 1, 1919'),
			new WT_Census_CensusColumnNull($this, 'R', 'Whether able to read'),
			new WT_Census_CensusColumnNull($this, 'W', 'Whether able to write'),
			new WT_Census_CensusColumnBirthPlaceSimple($this, 'BP', 'Place of birth'),
			new WT_Census_CensusColumnNull($this, 'Lang', 'Mother tongue'),
			new WT_Census_CensusColumnFatherBirthPlaceSimple($this, 'FBP', 'Place of birth of father'),
			new WT_Census_CensusColumnNull($this, 'Father lang', 'Mother tongue of father'),
			new WT_Census_CensusColumnFatherBirthPlaceSimple($this, 'MBP', 'Place of birth of mother'),
			new WT_Census_CensusColumnNull($this, 'Mother lang', 'Mother tongue of mother'),
			new WT_Census_CensusColumnNull($this, 'Eng', 'Whether able to speak English'),
			new WT_Census_CensusColumnOccupation($this, 'Occupation', 'Trade, profession, or particular kind of work done'),
			new WT_Census_CensusColumnNull($this, 'Ind', 'Industry, business of establishment in which at work'),
			new WT_Census_CensusColumnNull($this, 'Emp', 'Employer, salary or wage worker, or work on own account'),
		);
	}
}
