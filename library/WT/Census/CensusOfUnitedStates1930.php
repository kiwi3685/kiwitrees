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
class WT_Census_CensusOfUnitedStates1930 extends WT_Census_CensusOfUnitedStates implements WT_Census_CensusInterface {
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
			new WT_Census_CensusColumnFullName($this, 'Name', 'Name'),
			new WT_Census_CensusColumnRelationToHead($this, 'Relation', 'Relationship of each person to the head of the family'),
			new WT_Census_CensusColumnNull($this, 'Home', 'Home owned or rented'),
			new WT_Census_CensusColumnNull($this, 'V/R', 'Value of house, if owned, or monthly rental if rented'),
			new WT_Census_CensusColumnNull($this, 'Radio', 'Radio set'),
			new WT_Census_CensusColumnNull($this, 'Farm', 'Does this family live on a farm'),
			new WT_Census_CensusColumnSexMF($this, 'Sex', 'Sex'),
			new WT_Census_CensusColumnNull($this, 'Race', 'Color or race'),
			new WT_Census_CensusColumnAge($this, 'Age', 'Age at last birthday'),
			new WT_Census_CensusColumnConditionUs($this, 'Cond', 'Whether single, married, widowed, or divorced'),
			new WT_Census_CensusColumnMarried($this, 'AM', 'Age at first marriage'),
			new WT_Census_CensusColumnNull($this, 'School', 'Attended school since Sept. 1, 1929'),
			new WT_Census_CensusColumnNull($this, 'R/W', 'Whether able to read and write'),
			new WT_Census_CensusColumnBirthPlaceSimple($this, 'BP', 'Place of birth'),
			new WT_Census_CensusColumnFatherBirthPlaceSimple($this, 'FBP', 'Place of birth of father'),
			new WT_Census_CensusColumnMotherBirthPlaceSimple($this, 'MBP', 'Place of birth of mother'),
			new WT_Census_CensusColumnNull($this, 'Lang', 'Language spoken in home before coming to the United States'),
			new WT_Census_CensusColumnNull($this, 'Imm', 'Year of immigration to the United States'),
			new WT_Census_CensusColumnNull($this, 'Nat', 'Naturalization'),
			new WT_Census_CensusColumnNull($this, 'Eng', 'Whether able to speak English'),
			new WT_Census_CensusColumnOccupation($this, 'Occupation', 'Trade, profession, or particular kind of work done'),
			new WT_Census_CensusColumnNull($this, 'Industry', 'Industry, business of establishment in which at work'),
			new WT_Census_CensusColumnNull($this, 'Code', 'Industry code'),
			new WT_Census_CensusColumnNull($this, 'Emp', 'Class of worker'),
			new WT_Census_CensusColumnNull($this, 'Work', 'Whether normally at work yesterday or the last regular working day'),
			new WT_Census_CensusColumnNull($this, 'Unemp', 'If not, …'),
			new WT_Census_CensusColumnNull($this, 'Vet', 'Whether a veteran of U.S. military or …'),
			new WT_Census_CensusColumnNull($this, 'War', 'What war or …'),
		);
	}
}
