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
class KT_Census_CensusOfDenmark1860 extends KT_Census_CensusOfDenmark implements KT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '01 FEB 1860';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new KT_Census_CensusColumnFullName($this, 'Navn', 'Fornavn og efternavn', 'width: 200px;'),
			new KT_Census_CensusColumnAge($this, 'Alder', 'Beregnet alder fra fødselsåret ved folketællingen'),
			new KT_Census_CensusColumnConditionDanish($this, 'Civilstand', 'Ægteskabelig Stilling. Ugift (U), Gift (G), Enkemand eller Enke (E), Separeret (S), Fraskilt (F).'),
			new KT_Census_CensusColumnRelationToHeadDanish($this, 'Stilling i hustanden', ''),
			new KT_Census_CensusColumnOccupation($this, 'Erhverv', 'Hentes hvis data er indtastet'),
			new KT_Census_CensusColumnBirthPlace($this, 'Fødested', 'Fødselsstedet'),
		);
	}
}
