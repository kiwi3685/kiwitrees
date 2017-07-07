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
class WT_Census_CensusOfFrance extends WT_Census_Census implements WT_Census_CensusPlaceInterface {
	/**
	 * All available censuses for this census place.
	 *
	 * @return CensusInterface[]
	 */
	public function allCensusDates() {
		return array(
			new WT_Census_CensusOfFrance1831(),
			new WT_Census_CensusOfFrance1836(),
			new WT_Census_CensusOfFrance1841(),
			new WT_Census_CensusOfFrance1846(),
			new WT_Census_CensusOfFrance1851(),
			new WT_Census_CensusOfFrance1856(),
			new WT_Census_CensusOfFrance1861(),
			new WT_Census_CensusOfFrance1866(),
			new WT_Census_CensusOfFrance1872(),
			new WT_Census_CensusOfFrance1876(),
			new WT_Census_CensusOfFrance1881(),
			new WT_Census_CensusOfFrance1886(),
			new WT_Census_CensusOfFrance1891(),
			new WT_Census_CensusOfFrance1896(),
			new WT_Census_CensusOfFrance1901(),
			new WT_Census_CensusOfFrance1906(),
			new WT_Census_CensusOfFrance1911(),
			new WT_Census_CensusOfFrance1921(),
			new WT_Census_CensusOfFrance1926(),
			new WT_Census_CensusOfFrance1931(),
			new WT_Census_CensusOfFrance1936(),
			new WT_Census_CensusOfFrance1946(),
		);
	}

	/**
	 * Where did this census occur, in GEDCOM format.
	 *
	 * @return string
	 */
	public function censusPlace() {
		return 'France';
	}
}
