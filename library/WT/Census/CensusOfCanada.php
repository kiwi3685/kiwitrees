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
class WT_Census_CensusOfCanada extends WT_Census_Census implements WT_Census_CensusPlaceInterface {
	/**
	 * All available censuses for this census place. (English Canadian)
	 *
	 * @return(),Interface[]
	 */
	public function allCensusDates() {
		return array(
//			new WT_Census_CensusOfCanada1825(),
//			new WT_Census_CensusOfCanada1842(),
//			new WT_Census_CensusOfCanada1851(),
//			new WT_Census_CensusOfCanada1861(),
			new WT_Census_CensusOfCanada1871(),
//			new WT_Census_CensusOfCanada1881(),
//			new WT_Census_CensusOfCanada1891(),
//			new WT_Census_CensusOfCanada1901(),
//			new WT_Census_CensusOfCanada1906(),
//			new WT_Census_CensusOfCanada1911(),
//			new WT_Census_CensusOfCanada1916(),
//			new WT_Census_CensusOfCanada1921(),
		);
	}

	/**
	 * Where did this census occur, in GEDCOM format.
	 *
	 * @return string
	 */
	public function censusPlace() {
		return 'Canada';
	}
}
