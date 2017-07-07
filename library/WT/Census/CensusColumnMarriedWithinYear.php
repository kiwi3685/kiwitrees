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
 * Did the individual marry within the last year.
 */
class WT_Census_CensusColumnMarriedWithinYear extends WT_Census_AbstractCensusColumn implements WT_Census_CensusColumnInterface {
	/**
	 * Generate the likely value of this census column, based on available information.
	 *
	 * @param WT_Person     $individual
	 * @param Individual|null $head
	 *
	 * @return string
	 */
	public function generate(WT_Person $individual, WT_Person $head = null) {
		foreach ($individual->getSpouseFamilies() as $family) {
			foreach ($family->getFacts('MARR') as $fact) {
				$marriage_jd = $fact->getDate()->julianDay();
				$census_jd   = $this->date()->julianDay();
				if ($marriage_jd <= $census_jd && $marriage_jd >= $census_jd - 365) {
					return 'Y';
				}
			}
		}

		return '';
	}
}
