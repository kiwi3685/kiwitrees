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
class WT_Census_CensusOfScotland1891 extends WT_Census_CensusOfScotland implements WT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '05 APR 1891';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new WT_Census_CensusColumnFullName($this, 'Name', 'Name and surname'),
			new WT_Census_CensusColumnRelationToHead($this, 'Relation', 'Relation to head of household'),
			new WT_Census_CensusColumnConditionEnglish($this, 'Condition', 'Condition as to marriage'),
			new WT_Census_CensusColumnAgeMale($this, 'AgeM', 'Age (males)'),
			new WT_Census_CensusColumnAgeFemale($this, 'AgeF', 'Age (females)'),
			new WT_Census_CensusColumnOccupation($this, 'Occupation', 'Rank, profession or occupation'),
			new WT_Census_CensusColumnNull($this, 'Empl', 'Employer'),
			new WT_Census_CensusColumnNull($this, 'Empd', 'Employed'),
			new WT_Census_CensusColumnNull($this, 'OAC', 'Own account'),
			new WT_Census_CensusColumnBirthPlace($this, 'Birthplace', 'Where born'),
			new WT_Census_CensusColumnNull($this, 'Lang', 'Language spoken'),
			new WT_Census_CensusColumnNull($this, 'Infirm', 'Whether deaf-and-dumb, blind, lunatic or imbecile'),
		);
	}
}
