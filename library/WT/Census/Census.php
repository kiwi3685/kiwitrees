<?php
/**
 * webtrees: online genealogy
 * Copyright (C) 2017 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Definitions for a census
 */
class WT_Census_Census {
	/**
	 * @return CensusPlaceInterface[]
	 */
	public static function allCensusPlaces() {
		return array(
			new WT_Census_CensusOfCzechRepublic,
			new WT_Census_CensusOfCanada,
			new WT_Census_CensusOfDenmark,
			new WT_Census_CensusOfDeutschland,
			new WT_Census_CensusOfEngland,
			new WT_Census_CensusOfFrance,
			new WT_Census_CensusOfScotland,
			new WT_Census_CensusOfUnitedStates,
			new WT_Census_CensusOfWales,
		);
	}
}
