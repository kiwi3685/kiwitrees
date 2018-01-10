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
class KT_Census_CensusOfFrance1846 extends KT_Census_CensusOfFrance implements KT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '15 JAN 1846';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new KT_Census_CensusColumnSurname($this, 'Noms', 'Noms de famille'),
			new KT_Census_CensusColumnGivenNames($this, 'Prénoms', ''),
			new KT_Census_CensusColumnOccupation($this, 'Titres', 'Titres, qualifications, état ou profession et fonctions'),
			new KT_Census_CensusColumnConditionFrenchGarcon($this, 'Garçons', ''),
			new KT_Census_CensusColumnConditionFrenchHomme($this, 'Hommes', 'Hommes mariés'),
			new KT_Census_CensusColumnConditionFrenchVeuf($this, 'Veufs', ''),
			new KT_Census_CensusColumnConditionFrenchFille($this, 'Filles', ''),
			new KT_Census_CensusColumnConditionFrenchFemme($this, 'Femmes', 'Femmes mariées'),
			new KT_Census_CensusColumnConditionFrenchVeuve($this, 'Veuves', ''),
			new KT_Census_CensusColumnAge($this, 'Âge', ''),
		);
	}
}
