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
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Definitions for a census
 */
class KT_Census_CensusOfUnitedStates extends KT_Census_Census implements KT_Census_CensusPlaceInterface {
	/**
	 * All available censuses for this census place.
	 *
	 * @return CensusInterface[]
	 */
	public function allCensusDates() {
		return array(
			new KT_Census_CensusOfUnitedStates1790(),
			new KT_Census_CensusOfUnitedStates1800(),
			new KT_Census_CensusOfUnitedStates1810(),
			new KT_Census_CensusOfUnitedStates1820(),
			new KT_Census_CensusOfUnitedStates1830(),
			new KT_Census_CensusOfUnitedStates1840(),
			new KT_Census_CensusOfUnitedStates1850(),
			new KT_Census_CensusOfUnitedStates1860(),
			new KT_Census_CensusOfUnitedStates1870(),
			new KT_Census_CensusOfUnitedStates1880(),
			new KT_Census_CensusOfUnitedStates1890(),
			new KT_Census_CensusOfUnitedStates1900(),
			new KT_Census_CensusOfUnitedStates1910(),
			new KT_Census_CensusOfUnitedStates1920(),
			new KT_Census_CensusOfUnitedStates1930(),
			new KT_Census_CensusOfUnitedStates1940(),
		);
	}

	/**
	 * Where did this census occur, in GEDCOM format.
	 *
	 * @return string
	 */
	public function censusPlace() {
		return 'USA';
	}
}
