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
class KT_Census_CensusOfCanada1931 extends KT_Census_CensusOfCanada implements KT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '01 JUN 1931';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new KT_Census_CensusColumnFullName($this, 'Name', 'Name of each person in family, household or institution', 'width: 200px;'),
			new KT_Census_CensusColumnNull($this, 'Place of Abode', 'In rural localities give parish or township. In cities, towns and villages, give street and number of dwelling'),
			new KT_Census_CensusColumnNull($this, 'Own/Rent', 'Home owned or rented'),
			new KT_Census_CensusColumnNull($this, 'Value', 'If Owned give value. If rented, give rent paid per month'),
			new KT_Census_CensusColumnNull($this, 'Class', 'Class of House: Apartment, Row or Terrace, Single house, Semi-Detached, Flat'),
			new KT_Census_CensusColumnNull($this, 'Materials', 'Material of Construction: Stone, Brick, Wood, Brick Veneered, Stucco, Cement bricks'),
			new KT_Census_CensusColumnNull($this, 'Rooms', 'Rooms occupied by this family'),
			new KT_Census_CensusColumnNull($this, 'Radio', 'Has this family a radio?'),
			new KT_Census_CensusColumnRelationToHead($this, 'Relation', 'Relationship to Head of Family or household'),
			new KT_Census_CensusColumnSexMF($this, 'Sex', 'Sex'),
			new KT_Census_CensusColumnConditionCan($this, 'Marital Status', 'single, married, widowed, divorced, or legally separated'),
			new KT_Census_CensusColumnAge($this, 'Age', 'Age at last birthday'),
			new KT_Census_CensusColumnBirthPlaceSimple($this, 'BP', 'Country or place of birth (if Canada, specify province or territory)'),
			new KT_Census_CensusColumnFatherBirthPlaceSimple($this, 'BPF', 'Birth place of father'),
			new KT_Census_CensusColumnMotherBirthPlaceSimple($this, 'BPM', 'Birth place of mother'),
			new KT_Census_CensusColumnNull($this, 'Immigration', 'Year of immigration to Canada, if an immigrant'),
			new KT_Census_CensusColumnNull($this, 'Naturalization', 'Year of naturalization, if formerly an alien'),
			new KT_Census_CensusColumnNationality($this, 'Nationality', 'Nationality (country to which person owes allegiance)'),
			new KT_Census_CensusColumnNull($this, 'Origin', 'Racial origin'),
			new KT_Census_CensusColumnNull($this, 'English', 'Can speak English'),
			new KT_Census_CensusColumnNull($this, 'French', 'Can speak French'),
			new KT_Census_CensusColumnNull($this, 'Language', 'Language other than English or French spoken as mother tongue'),
			new KT_Census_CensusColumnReligion($this, 'Religion', 'Religious body, Denomination or Community to which this person adheres or belongs'),
			new KT_Census_CensusColumnNull($this, 'Read/Write', 'Can read and write'),
			new KT_Census_CensusColumnNull($this, 'School', 'Months at school since September 1, 1920'),
			new KT_Census_CensusColumnOccupation($this, 'Occupation', 'Trade, profession or particular kind of work, as carpenter, weaver, sawyer, merchant, farmer,salesman, teacher, etc. (Give as definite and precise information as possible)'),
			new KT_Census_CensusColumnNull($this, 'Industry', 'Industry or business in which engaged or employed as cotton mill, brass foundry, grocery, coal mine, dairy farm, public school, business college, etc'),
			new KT_Census_CensusColumnNull($this, 'Class', 'Class of worker'),
			new KT_Census_CensusColumnNull($this, 'Earnings', 'Total earnings in the past twelve months (Since June 1st, 1930)'),
			new KT_Census_CensusColumnNull($this, 'Employed', 'If an employee, where you at work Monday June 1st, 1930'),
			new KT_Census_CensusColumnNull($this, 'WHY', 'If answer to previous question is NO. Why were you not at work on Monday, June 1st, 1931. (For Example, no job, sick, accident, on holidays, strike or lock-out, plant closed, no materials, etc)'),
			new KT_Census_CensusColumnNull($this, 'Weeks unemployed', 'Total number of weeks unemployed from any cause in the last 12 months'),
			new KT_Census_CensusColumnNull($this, 'No Job', 'Of the total numer of weeks reported out of work, how many were due to-'),
			new KT_Census_CensusColumnNull($this, 'Illness', 'Of the total numer of weeks reported out of work, how many were due to-'),
			new KT_Census_CensusColumnNull($this, 'Accident', 'Of the total numer of weeks reported out of work, how many were due to-'),
			new KT_Census_CensusColumnNull($this, 'Strike or Lock-out', 'Of the total numer of weeks reported out of work, how many were due to-'),
			new KT_Census_CensusColumnNull($this, 'Temporary Lay-off', 'Of the total numer of weeks reported out of work, how many were due to-'),
			new KT_Census_CensusColumnNull($this, 'Other Causes', 'Of the total numer of weeks reported out of work, how many were due to-'),
		);
	}
}
