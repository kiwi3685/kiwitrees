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
class WT_Census_RegisterOfEngland1939 extends WT_Census_CensusOfEngland implements WT_Census_CensusInterface {
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
			new WT_Census_CensusColumnNull($this, 'Schedule', 'Schedule Number'),
			new WT_Census_CensusColumnNull($this, 'SubNum', 'Schedule Sub Number'),
			new WT_Census_CensusColumnSurnameGivenNames($this, 'Name', 'Surname & other names', 'width: 250px;'),
			new WT_Census_CensusColumnNull($this, 'Role', 'For institutions only – for example, Officer, Visitor, Servant, Patient, Inmate'),
			new WT_Census_CensusColumnSexMF($this, 'Sex', 'Male or Female'),
			new WT_Census_CensusColumnBirthDayMonthSlashYear($this, 'DOB', 'Date of birth', 'width: 90px;'),
			new WT_Census_CensusColumnConditionEnglish($this, 'MC', 'Marital Condition - Married, Single, Unmarried, Widowed or Divorced'),
			new WT_Census_CensusColumnOccupation($this, 'Occupation', 'Occupation', 'width: 200px;'),
			new WT_Census_CensusColumnOccupation($this, 'Other', 'Instructions', 'width: 200px;'),
		);
	}
}