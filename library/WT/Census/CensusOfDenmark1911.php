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
class WT_Census_CensusOfDenmark1911 extends WT_Census_CensusOfDenmark implements WT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '01 FEB 1911';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new WT_Census_CensusColumnFullName($this, 'Navn', 'Samtlige Personers Navn (ogsaa Fornavn). Ved Børn, endnu uden Navn, sættes „Dreng“ eller „Pige“. Midlertidig fraværerade Personer anføres ikke her, men paa Skemaeta Bagside)', 'width: 200px;'),
			new WT_Census_CensusColumnSexMK($this, 'Køn', 'Kjønnet. Mandkøn (M) eller Kvindekøn (K).'),
			new WT_Census_CensusColumnBirthDaySlashMonthYear($this, 'Fødselsdag', 'Føderlsaar og Føderladag.'),
			new WT_Census_CensusColumnConditionDanish($this, 'Civilstand', 'Ægteskabelig Stillinge. Ugift (U), Gift (G), Enkemand eller Enke (E), Separeret (S), Fraskilt (F).'),
			new WT_Census_CensusColumnReligion($this, 'Trossamfund', 'Trossamfund (Folkekirken eller Navnet paa det Trossamfund, man tilhører, eller „udenfor Trossamfund“).'),
			new WT_Census_CensusColumnBirthPlace($this, 'Fødested', ''),
			new WT_Census_CensusColumnRelationToHead($this, 'Stilling i familien', 'Stilling i Familien: Husfader, Husmoder, Barn, Slangtning o.l., Tjenestetyende, Logerende, Pensioner'),
			new WT_Census_CensusColumnOccupation($this, 'Erhverv', ''),
			new WT_Census_CensusColumnNull($this, 'Anmærkninger', 'Anmærkninger.'),
		);
	}
}
