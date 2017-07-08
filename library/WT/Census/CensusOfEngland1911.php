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
class WT_Census_CensusOfEngland1911 extends WT_Census_CensusOfEngland implements WT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '02 APR 1911';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new WT_Census_CensusColumnFullName($this, 'Name', 'Name and surname', 'width: 250px;'),
			new WT_Census_CensusColumnRelationToHead($this, 'Relation', 'Relation to head of household'),
			new WT_Census_CensusColumnAgeMale($this, 'AgeM', 'Age (males)'),
			new WT_Census_CensusColumnAgeFemale($this, 'AgeF', 'Age (females)'),
			new WT_Census_CensusColumnConditionEnglish($this, 'Condition', 'Marital status'),
			new WT_Census_CensusColumnYearsMarried($this, 'YrM', 'Years married'),
			new WT_Census_CensusColumnChildrenBornAlive($this, 'ChA', 'Children born alive'),
			new WT_Census_CensusColumnChildrenLiving($this, 'ChL', 'Children who are still alive'),
			new WT_Census_CensusColumnChildrenDied($this, 'ChD', 'Children who have died'),
			new WT_Census_CensusColumnOccupation($this, 'Occupation', 'Rank, profession or occupation', 'width: 100px;'),
			new WT_Census_CensusColumnNull($this, 'Ind', 'Industry'),
			new WT_Census_CensusColumnNull($this, 'Emp', 'Employer, worker or own account'),
			new WT_Census_CensusColumnNull($this, 'Home', 'Working at home'),
			new WT_Census_CensusColumnBirthPlace($this, 'Birthplace', 'Where born', 'width: 100px;'),
			new WT_Census_CensusColumnNationality($this, 'Nat', 'Nationality'),
			new WT_Census_CensusColumnNull($this, 'Infirm', 'Infirmity'),
		);
	}
}
