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
class WT_Census_CensusOfDenmark1940 extends WT_Census_CensusOfDenmark implements WT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '05 NOV 1940';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new WT_Census_CensusColumnFullName($this, 'Navn', ''),
			new WT_Census_CensusColumnBirthDaySlashMonth($this, 'Fødselsdag', 'Dato og måned'),
			new WT_Census_CensusColumnBirthYear($this, 'Fødselsår', 'Fødselsåret'),
			new WT_Census_CensusColumnConditionDanish($this, 'Civilstand', 'Ægteskabelig Stilling. Ugift (U), Gift (G), Enkemand eller Enke (E), Separeret (S), Fraskilt (F).'),
			new WT_Census_CensusColumnRelationToHeadDanish($this, 'Stilling i hustanden', ''),
			new WT_Census_CensusColumnOccupation($this, 'Erhverv', 'Hentes hvis data er indtastet'),
			new WT_Census_CensusColumnBirthPlace($this, 'Fødested', 'Fødselsstedet'),
		);
	}
}
