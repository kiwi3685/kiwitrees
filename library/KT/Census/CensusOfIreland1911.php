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
class KT_Census_CensusOfIreland1911 extends KT_Census_CensusOfIreland implements KT_Census_CensusInterface {
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
			new KT_Census_CensusColumnGivenNameInitial($this, 'Christian Name', 'Given name of each person at this place on the night of the census', 'width: 120px;'),
			new KT_Census_CensusColumnSurname($this, 'Surname', 'Surname of each person at this place on the night of the census', 'width: 120px;'),
			new KT_Census_CensusColumnRelationToHead($this, 'Relation', 'Relation to head of household'),
			new KT_Census_CensusColumnReligion($this, 'Religion', 'Religion'),
			new KT_Census_CensusColumnNull($this, 'Education', 'Able to read and write, read only, or write only'),
			new KT_Census_CensusColumnAgeMale($this, 'Age(M)', 'Age of males at last birthday'),
			new KT_Census_CensusColumnAgeFemale($this, 'Age(F)', 'Age of females at last birthday'),
			new KT_Census_CensusColumnOccupation($this, 'Occupation', 'Rank, profession, or occupation'),
			new KT_Census_CensusColumnConditionIrish($this, 'Marital Status', 'Married, Widower, Widow, or single'),
			new KT_Census_CensusColumnYearsMarried($this, 'YrM', 'Years married (Enter for each married woman)'),
			new KT_Census_CensusColumnChildrenBornAlive($this, 'ChB', 'Children born alive'),
			new KT_Census_CensusColumnChildrenLiving($this, 'ChL', 'Children who are still living'),
			new KT_Census_CensusColumnBirthPlaceSimple($this, 'Birth Place', 'Country or Place of Birth (if Ireland, specify county or city)'),
			new KT_Census_CensusColumnNull($this, 'Irish Language', 'State if Irish, or Irish & English spoken'),
			new KT_Census_CensusColumnNull($this, 'Infirm', 'If Deaf & Dumb; Dumb only; Blind; Imbecile or Idiot; or Lunatic'),
		);
	}
}
