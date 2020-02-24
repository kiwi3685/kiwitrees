<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2020 kiwitrees.net
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
class KT_Census_CensusOfCzechRepublic1921 extends KT_Census_CensusOfCzechRepublic implements KT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '15 FEB 1921';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new KT_Census_CensusColumnFullName($this, 'Jméno', ''),
			new KT_Census_CensusColumnRelationToHead($this, 'Vztah', ''),
			new KT_Census_CensusColumnSexMZ($this, 'Pohlaví', ''),
			new KT_Census_CensusColumnNull($this, 'Stav', 'Rodinný stav'),
			new KT_Census_CensusColumnBirthDaySlashMonthYear($this, 'Narození', 'Datum narození'),
			new KT_Census_CensusColumnBirthPlace($this, 'Místo', 'Místo narození'),
			new KT_Census_CensusColumnNull($this, 'Přísluší', 'Domovské právo'),
			new KT_Census_CensusColumnNull($this, 'Jazyk', 'Jazyk v obcování'),
			new KT_Census_CensusColumnReligion($this, 'Vyznání', ''),
			new KT_Census_CensusColumnOccupation($this, 'Povolání', ''),
			new KT_Census_CensusColumnNull($this, 'Postavení', 'Postavení v zaměstnání'),
			new KT_Census_CensusColumnNull($this, '', ''),
			new KT_Census_CensusColumnNull($this, '', ''),
			new KT_Census_CensusColumnNull($this, '', ''),
		);
	}
}
