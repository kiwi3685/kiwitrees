<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net
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
class KT_Census_CensusOfCanada1906 extends KT_Census_CensusOfCanada implements KT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '24 JUN 1906';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new KT_Census_CensusColumnFullName($this, 'Names', 'Name of each person in family or household', 'width: 200px;'),
			new KT_Census_CensusColumnRelationToHead($this, 'Relation', 'Relation to head of family'),
			new KT_Census_CensusColumnSexMF($this, 'Sex', 'Sex (M = Male; F = Female)'),
			new KT_Census_CensusColumnConditionCan($this, 'Marital Status', 'Single, Married, Widowed, or Divorced'),
			new KT_Census_CensusColumnAge($this, 'Age', ''),
			new KT_Census_CensusColumnBirthPlaceSimple($this, 'BP', 'Country or place of birth'),
			new KT_Census_CensusColumnNull($this, 'Immigration', 'Year of immigration to Canada'),
			new KT_Census_CensusColumnNull($this, 'PO Address', 'Post Office Address (entered for the head of family only)'),
		);
	}
}
