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
class WT_Census_CensusOfFrance1876 extends WT_Census_CensusOfFrance implements WT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '20 JAN 1876';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new WT_Census_CensusColumnSurname($this, 'Noms', 'Noms de famille'),
			new WT_Census_CensusColumnGivenNames($this, 'Prénoms', ''),
			new WT_Census_CensusColumnOccupation($this, 'Titres', 'Titres, qualifications, état ou profession et fonctions'),
			new WT_Census_CensusColumnConditionFrenchGarcon($this, 'Garçons', ''),
			new WT_Census_CensusColumnConditionFrenchHomme($this, 'Hommes', 'Hommes mariés'),
			new WT_Census_CensusColumnConditionFrenchVeuf($this, 'Veufs', ''),
			new WT_Census_CensusColumnConditionFrenchFille($this, 'Filles', ''),
			new WT_Census_CensusColumnConditionFrenchFemme($this, 'Femmes', 'Femmes mariées'),
			new WT_Census_CensusColumnConditionFrenchVeuve($this, 'Veuves', ''),
			new WT_Census_CensusColumnAge($this, 'Âge', ''),
			new WT_Census_CensusColumnBirthPlace($this, 'Nationalité', 'Nationalité - Lieu de naissance'),
		);
	}
}
