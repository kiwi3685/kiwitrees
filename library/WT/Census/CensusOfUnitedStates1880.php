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
class WT_Census_CensusOfUnitedStates1880 extends WT_Census_CensusOfUnitedStates implements WT_Census_CensusInterface {
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
			new WT_Census_CensusColumnFullName($this, 'Name', 'Name'),
			new WT_Census_CensusColumnAge($this, 'Age', 'Age'),
			new WT_Census_CensusColumnSexMF($this, 'Sex', 'Sex'),
			new WT_Census_CensusColumnMonthIfBornWithinYear($this, 'Mon', 'If born within the year, state month'),
			new WT_Census_CensusColumnRelationToHead($this, 'Relation', 'Relation to head of household'),
			new WT_Census_CensusColumnNull($this, 'S', 'Single'),
			new WT_Census_CensusColumnNull($this, 'M', 'Married'),
			new WT_Census_CensusColumnNull($this, 'W/D', 'Widowed, Divorced'),
			new WT_Census_CensusColumnMarriedWithinYear($this, 'MY', 'Married during census year'),
			new WT_Census_CensusColumnOccupation($this, 'Occupation', 'Profession, occupation, or trade'),
			new WT_Census_CensusColumnNull($this, 'UnEm', 'Number of months the person has been unemployed during the census year'),
			new WT_Census_CensusColumnNull($this, 'Sick', 'Sickness or disability'),
			new WT_Census_CensusColumnNull($this, 'Blind', 'Blind'),
			new WT_Census_CensusColumnNull($this, 'DD', 'Deaf and dumb'),
			new WT_Census_CensusColumnNull($this, 'Idiotic', 'Idiotic'),
			new WT_Census_CensusColumnNull($this, 'Insane', 'Insane'),
			new WT_Census_CensusColumnNull($this, 'Disabled', 'Maimed, crippled, bedridden or otherwise disabled'),
			new WT_Census_CensusColumnNull($this, 'School', 'Attended school within the census year'),
			new WT_Census_CensusColumnNull($this, 'Read', 'Cannot read'),
			new WT_Census_CensusColumnNull($this, 'Write', 'Cannot write'),
			new WT_Census_CensusColumnBirthPlaceSimple($this, 'BP', 'Place of birth, naming the state, territory, or country'),
			new WT_Census_CensusColumnFatherBirthPlaceSimple($this, 'FBP', 'Place of birth of father, naming the state, territory, or country'),
			new WT_Census_CensusColumnMotherBirthPlaceSimple($this, 'MBP', 'Place of birth of mother, naming the state, territory, or country'),
		);
	}
}
