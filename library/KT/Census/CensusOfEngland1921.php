<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net
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
class KT_Census_CensusOfEngland1921 extends KT_Census_CensusOfEngland implements KT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '19 JUN 1921';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new KT_Census_CensusColumnFullName($this, 'Name', 'Name and surname', 'width: 250px;'),
			new KT_Census_CensusColumnRelationToHead($this, 'Relation', 'Relationship to head of household'),
			new KT_Census_CensusColumnAge($this, 'Age', 'Age (Y/M)'),
			new KT_Census_CensusColumnSexMF($this, 'Sex', 'Male or Female'),
			new KT_Census_CensusColumnConditionEnglish($this, 'Condition', 'Marriage or Orphanhood'),
			new KT_Census_CensusColumnBirthPlace($this, 'Birthplace', 'Where born in UK', 'width: 100px;'),
			new KT_Census_CensusColumnNationality($this, 'Nat', 'Where born if not in UK'),
			new KT_Census_CensusColumnOccupation($this, 'Occupation', 'Personal Occupation or Schooling', 'width: 100px;'),
			new KT_Census_CensusColumnNull($this, 'Emp', 'Employment'),
			new KT_Census_CensusColumnNull($this, 'Work place', 'Place of Work'),
			new KT_Census_CensusColumnNull($this, 'ChL', 'No. of living children, total and by age'),
		);
	}
}
