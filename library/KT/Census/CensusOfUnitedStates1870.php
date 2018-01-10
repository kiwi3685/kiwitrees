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
class KT_Census_CensusOfUnitedStates1870 extends KT_Census_CensusOfUnitedStates implements KT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return 'JUN 1870';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new KT_Census_CensusColumnFullName($this, 'Name', 'Name'),
			new KT_Census_CensusColumnAge($this, 'Age', 'Age'),
			new KT_Census_CensusColumnSexMF($this, 'Sex', 'Sex'),
			new KT_Census_CensusColumnNull($this, 'Color', 'White, Black, Mulatto, Chinese, Indian'),
			new KT_Census_CensusColumnOccupation($this, 'Occupation', 'Profession, occupation, or trade'),
			new KT_Census_CensusColumnNull($this, 'RE', 'Value of real estate owned'),
			new KT_Census_CensusColumnNull($this, 'PE', 'Value of personal estate owned'),
			new KT_Census_CensusColumnBirthPlaceSimple($this, 'Birthplace', 'Place of birth, naming the state, territory, or country'),
			new KT_Census_CensusColumnFatherForeign($this, 'FFB', 'Father of foreign birth'),
			new KT_Census_CensusColumnMotherForeign($this, 'MFB', 'Mother of foreign birth'),
			new KT_Census_CensusColumnMonthIfBornWithinYear($this, 'Born', 'If born within the year, state month'),
			new KT_Census_CensusColumnMonthIfMarriedWithinYear($this, 'Mar', 'If married within the year, state month'),
			new KT_Census_CensusColumnNull($this, 'School', 'Attended school within the year'),
			new KT_Census_CensusColumnNull($this, 'Read', 'Cannot read'),
			new KT_Census_CensusColumnNull($this, 'Write', 'Cannot write'),
			new KT_Census_CensusColumnNull($this, 'Infirm', 'Whether deaf and dumb, blind, insane, or idiotic'),
			new KT_Census_CensusColumnNull($this, 'Cit', 'Male citizen of US'),
			new KT_Census_CensusColumnNull($this, 'Dis', 'Male citizen of US, where right to vote is denied or abridged'),
		);
	}
}
