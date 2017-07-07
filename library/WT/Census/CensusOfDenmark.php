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
class WT_Census_CensusOfDenmark extends WT_Census_Census implements WT_Census_CensusPlaceInterface {
	/**
	 * All available censuses for this census place.
	 *
	 * @return CensusInterface[]
	 */
	public function allCensusDates() {
		return array(
			new WT_Census_CensusOfDenmark1787(),
			new WT_Census_CensusOfDenmark1801(),
			new WT_Census_CensusOfDenmark1803(),
			new WT_Census_CensusOfDenmark1834(),
			new WT_Census_CensusOfDenmark1835(),
			new WT_Census_CensusOfDenmark1840(),
			new WT_Census_CensusOfDenmark1845(),
			new WT_Census_CensusOfDenmark1850(),
			new WT_Census_CensusOfDenmark1855(),
			new WT_Census_CensusOfDenmark1860(),
			new WT_Census_CensusOfDenmark1870(),
			new WT_Census_CensusOfDenmark1880(),
			new WT_Census_CensusOfDenmark1885(),
			new WT_Census_CensusOfDenmark1890(),
			new WT_Census_CensusOfDenmark1901(),
			new WT_Census_CensusOfDenmark1906(),
			new WT_Census_CensusOfDenmark1911(),
			new WT_Census_CensusOfDenmark1916(),
			new WT_Census_CensusOfDenmark1921(),
			new WT_Census_CensusOfDenmark1925(),
			new WT_Census_CensusOfDenmark1930(),
			new WT_Census_CensusOfDenmark1940(),
		);
	}

	/**
	 * Where did this census occur, in GEDCOM format.
	 *
	 * @return string
	 */
	public function censusPlace() {
		return 'Danmark';
	}
}
