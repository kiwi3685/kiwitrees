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
class KT_Census_CensusOfCanada1861 extends KT_Census_CensusOfCanada implements KT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '13 JAN 1861';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new KT_Census_CensusColumnFullName($this, 'Name', 'Name of each person in family or household', 'width: 200px;'),
			new KT_Census_CensusColumnOccupation($this, 'Occupation', 'Profession, trade, or occupation'),
			new KT_Census_CensusColumnBirthPlaceSimple($this, 'Place of Birth', 'Country or province of birth'),
			new KT_Census_CensusColumnReligion($this, 'Religion', 'Religion'),
			new KT_Census_CensusColumnNull($this, 'Residence', 'Address if not at usual place of abode'),
			new KT_Census_CensusColumnAgeNext($this, 'Age', 'Age at next birthday'),
			new KT_Census_CensusColumnSexMF($this, 'Sex', 'Sex (M = Male; F = Female)'),
			new KT_Census_CensusColumnConditionCan($this, 'Married', 'Whether married, or widowed'),
		);
	}
}
