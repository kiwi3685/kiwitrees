<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2021 kiwitrees.net
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
 * The nationality of the individual.
 */
class KT_Census_CensusColumnNationality extends KT_Census_AbstractCensusColumn implements KT_Census_CensusColumnInterface {
	/** @var array Convert a country name to a nationality */
	private $nationalities = array(
		'England'     	=> 'English',
		'Scotland'    	=> 'Scottish',
		'Wales'       	=> 'Welsh',
		'Ireland'		=> 'Irish',
		'Deutschland'	=> 'Deutsch',
		'Canada'		=> 'Canadian',
	);

	/**
	 * Generate the likely value of this census column, based on available information.
	 *
	 * @param KT_Person     $individual
	 * @param Individual|null $head
	 *
	 * @return string
	 */
	public function generate(KT_Person $individual, KT_Person $head = null) {
		$place = $individual->getBirthPlace();

		// No birthplace?  Assume born in the same country.
		if ($place === '') {
			$place = $this->place();
		}

		// Did we emigrate or naturalise?
		$indifacts = $individual->getIndiFacts();
		foreach ($indifacts as $fact) {
			if (in_array($fact, array('IMMI|EMIG|NATU')) && KT_Date::Compare($fact->getDate(), $this->date()) <= 0) {
				$place = $fact->getPlace()->getGedcomName();
				$place = $fact->getPlace();
			}
		}

		$place = $this->lastPartOfPlace($place);

		if (array_key_exists($place, $this->nationalities)) {
			return $this->nationalities[$place];
		} else {
			return $place;
		}
	}
}
