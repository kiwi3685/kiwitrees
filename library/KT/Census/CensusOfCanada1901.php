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
class KT_Census_CensusOfCanada1901 extends KT_Census_CensusOfCanada implements KT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '31 MAR 1901';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new KT_Census_CensusColumnFullName($this, 'Names', 'Name of each person in family or household', 'width: 200px;'),
			new KT_Census_CensusColumnRelationToHead($this, 'Relation', 'Relation to head of household'),
			new KT_Census_CensusColumnSexMF($this, 'Sex', 'Sex (M = Male; F = Female)'),
			new KT_Census_CensusColumnConditionCan($this, 'Marital Status', 'Single, Married, Widowed, or Divorced'),
			new KT_Census_CensusColumnNull($this, 'Date of Birth', 'Date of Birth'),
            new KT_Census_CensusColumnAge($this, 'Age', 'Age at last birthday'),
			new KT_Census_CensusColumnBirthPlaceSimple($this, 'Place of Birth', 'Country of Origin'),
            new KT_Census_CensusColumnNull($this, 'Immigration', 'Year of immigration to Canada'),
            new KT_Census_CensusColumnNull($this, 'Race', 'Racial or tribal origin'),
            new KT_Census_CensusColumnReligion($this, 'Religion', 'Religion'),
    		new KT_Census_CensusColumnOccupation($this, 'Occupation', 'Profession, occupation, or trade'),
			new KT_Census_CensusColumnNull($this, 'Employment Status', 'Employer, Worker, or Own Account'),
			new KT_Census_CensusColumnNull($this, 'Infirm', 'Infirmities – (1) deaf and dumb, (2) blind, (3) unsound mind'),
		);
	}
}
