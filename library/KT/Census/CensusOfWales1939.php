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
class KT_Census_CensusOfWales1939 extends KT_Census_CensusOfWales implements KT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '29 SEP 1939';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new KT_Census_CensusColumnNull($this, 'Schedule', 'Schedule Number'),
			new KT_Census_CensusColumnNull($this, 'SubNum', 'Schedule Sub Number'),
			new KT_Census_CensusColumnSurnameGivenNames($this, 'Name', 'Surname & other names'),
			new KT_Census_CensusColumnNull($this, 'Role', 'For institutions only – for example, Officer, Visitor, Servant, Patient, Inmate'),
			new KT_Census_CensusColumnSexMF($this, 'Sex', 'Male or Female'),
			new KT_Census_CensusColumnBirthDayMonthSlashYear($this, 'DOB', 'Date of birth'),
			new KT_Census_CensusColumnConditionEnglish($this, 'MC', 'Marital Condition - Married, Single, Unmarried, Widowed or Divorced'),
			new KT_Census_CensusColumnOccupation($this, 'Occupation', 'Occupation'),
			new KT_Census_CensusColumnOccupation($this, 'Other', 'Instructions'),
		);
	}
}
