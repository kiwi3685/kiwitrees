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
#[AllowDynamicProperties]
class KT_Census_Census {
	/**
	 * @return CensusPlaceInterface[]
	 */
	public static function allCensusPlaces() {
		return array(
			new KT_Census_CensusOfCzechRepublic,
			new KT_Census_CensusOfCanada,
			new KT_Census_CensusOfDenmark,
			new KT_Census_CensusOfDeutschland,
			new KT_Census_CensusOfEngland,
			new KT_Census_CensusOfFrance,
			new KT_Census_CensusOfIreland,
			new KT_Census_CensusOfScotland,
			new KT_Census_CensusOfUnitedStates,
			new KT_Census_CensusOfWales,
		);
	}
}
