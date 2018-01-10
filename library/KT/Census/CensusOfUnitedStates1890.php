<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2018 kiwitrees.net
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
class KT_Census_CensusOfUnitedStates1890 extends KT_Census_CensusOfUnitedStates implements KT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '02 JUN 1890';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new KT_Census_CensusColumnGivenNameInitial($this, 'Name', 'Christian name in full, and initial of middle name'),
			new KT_Census_CensusColumnSurname($this, 'Surname', 'Surname'),
			new KT_Census_CensusColumnNull($this, 'CW', 'Whether a soldier, sailor or marine during the civil war (U.S. or Conf.), or widow of such person'),
			new KT_Census_CensusColumnRelationToHead($this, 'Relation', 'Relation to head of family'),
			new KT_Census_CensusColumnNull($this, 'Race', 'Whether white, black, mulatto, quadroon, octoroon, Chinese, Japanese, or Indian'),
			new KT_Census_CensusColumnSexMF($this, 'Sex', 'Sex'),
			new KT_Census_CensusColumnAge($this, 'Age', 'Age at nearest birthday. If under one year, give age in months'),
			new KT_Census_CensusColumnConditionUs($this, 'Cond', 'Whether single, married, widowed, or divorced'),
			new KT_Census_CensusColumnMonthIfMarriedWithinYear($this, 'Mar', 'Whether married duirng the census year (June 1, 1889, to May 31, 1890)'),
			new KT_Census_CensusColumnNull($this, 'Chil', 'Mother of how many children, and number of these children living'),
			new KT_Census_CensusColumnBirthPlaceSimple($this, 'BP', 'Place of birth'),
			new KT_Census_CensusColumnFatherBirthPlaceSimple($this, 'FBP', 'Place of birth of father'),
			new KT_Census_CensusColumnFatherBirthPlaceSimple($this, 'MBP', 'Place of birth of mother'),
			new KT_Census_CensusColumnNull($this, 'US', 'Number of years in the United States'),
			new KT_Census_CensusColumnNull($this, 'Nat', 'Whether naturalized'),
			new KT_Census_CensusColumnNull($this, 'Papers', 'Whether naturalization papers have been taken out'),
			new KT_Census_CensusColumnOccupation($this, 'Occupation', 'Profession, trade, occupation'),
			new KT_Census_CensusColumnNull($this, 'Unemp', 'Months unemployed during the census year (June 1, 1889, to May 31, 1890)'),
			new KT_Census_CensusColumnNull($this, 'Read', 'Able to read'),
			new KT_Census_CensusColumnNull($this, 'Write', 'Able to write'),
			new KT_Census_CensusColumnNull($this, 'Eng', 'Able to speak English. If not the language or dialect spoken'),
			new KT_Census_CensusColumnNull($this, 'Disease', 'Whether suffering from acute or chronic disease, with name of disease and length of time afflicted'),
			new KT_Census_CensusColumnNull($this, 'Infirm', 'Whether defective in mind, sight, hearing, or speech, or whether crippled, maimed, or deformed, with name of defect'),
			new KT_Census_CensusColumnNull($this, 'Prisoner', 'Whether a prisoner, convict, homeless child, or pauper'),
		);
	}
}
