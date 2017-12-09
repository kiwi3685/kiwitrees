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
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Definitions for a census
 */
class KT_Census_CensusOfCanada1916 extends KT_Census_CensusOfCanada implements KT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '01 JUN 1916';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new KT_Census_CensusColumnFullName($this, 'Names', 'Name of each person in family', 'width: 200px;'),
			new KT_Census_CensusColumnNull($this, 'Military', 'Military service'),
			new KT_Census_CensusColumnNull($this, 'Habitation', 'Place of habitation (township, range, meridian, and municipality)'),
			new KT_Census_CensusColumnRelationToHead($this, 'Relation', 'Relation to head of household'),
			new KT_Census_CensusColumnSexMF($this, 'Sex', 'Sex (M = Male; F = Female)'),
			new KT_Census_CensusColumnConditionCan($this, 'Marital Status', 'Single, Married, Widowed, or Divorced'),
			new KT_Census_CensusColumnAge($this, 'Age', 'Age at last birthday'),
			new KT_Census_CensusColumnReligion($this, 'Religion', 'Religion'),
			new KT_Census_CensusColumnNull($this, 'Immigration', 'Year of immigration to Canada'),
			new KT_Census_CensusColumnNull($this, 'Naturalization', 'Year of naturalization'),
			new KT_Census_CensusColumnNationality($this, 'Nationality', ''),
			new KT_Census_CensusColumnNull($this, 'Race', ''),
			new KT_Census_CensusColumnNull($this, 'English', 'Can speak English'),
			new KT_Census_CensusColumnNull($this, 'French', 'Can speak French'),
			new KT_Census_CensusColumnNull($this, 'Other Language', 'Other language spoken as mother tongue'),
			new KT_Census_CensusColumnNull($this, 'Literate', 'Can read and write'),
			new KT_Census_CensusColumnOccupation($this, 'Occupation', 'Chief occupation, or trade'),
			new KT_Census_CensusColumnNull($this, 'Employment', 'Employer, Worker, or Own Account'),
		);
	}
}
