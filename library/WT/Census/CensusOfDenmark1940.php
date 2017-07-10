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
class WT_Census_CensusOfDenmark1940 extends WT_Census_CensusOfDenmark implements WT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '05 NOV 1940';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new WT_Census_CensusColumnSurnameGivenNames($this, 'Navn', '', 'width: 200px;'),
			new WT_Census_CensusColumnNull($this, 'Nærværende', 'Hvis den i Rubrik 1 opførte Person er midleritidg nærværende d.v.s. har fast Bopæl ????? (er optaget under en anden Address i Folkeregistret), anføres her den faste Bopæls Adresse (Kommunens Navn og den fuldstændige Adresse i denne; for Udlændinge dog kun Landets Navn).'),
			new WT_Census_CensusColumnNull($this, 'Fraværende', 'Hvis den i Rubrik 1 opførte Person er midleritidg fraværende d.v.s. har fast Bopæl paa Tællingsstedet (er optaget underdenne Address i Folkeregistret), men den 5. Novemer ikke er til Stede paa Tællingsstedet, anføres her „fraværende“ og Adressen paa det midlertidige Opholdssted (ved Ophold i Udlandet anføres jun Landets Navn).'),
			new WT_Census_CensusColumnSexMK($this, 'Køn', 'Køn Mand (M) Kvinde (K)'),
			new WT_Census_CensusColumnBirthDaySlashMonth($this, 'Fødselsdag', ''),
			new WT_Census_CensusColumnBirthYear($this, 'Fødselsaar', ''),
			new WT_Census_CensusColumnBirthPlace($this, 'Fødested', ''),
			new WT_Census_CensusColumnNull($this, 'Statsbergerferhold', ''),
			new WT_Census_CensusColumnConditionDanish($this, 'Civilstand', 'Ægteskabelig Stillinge. Ugift (U), Gift (G), Enkemand eller Enke (E), Separeret (S), Fraskilt (F).'),
			new WT_Census_CensusColumnNull($this, 'Indgaaelse', 'Date for det nuværende Ægteskabs Indgaaelse. NB." RUbrikken udfyldes ikke al Enkemaend, Enker, Separerede eller Fraskilte.'),
			new WT_Census_CensusColumnRelationToHeadDanish($this, 'Stilling i familien', ''),
			new WT_Census_CensusColumnOccupation($this, 'Erhverv', ''),
			new WT_Census_CensusColumnNull($this, 'Virksomhedens', 'Virksomhedens (Branchens) Art'),
			new WT_Census_CensusColumnNull($this, 'Hustruen', 'Besvares kun af Hustruen og hjemmeboende Børn over 14 Aar'),
			new WT_Census_CensusColumnNull($this, 'Døtre', 'Besvares kun af hjemmeboende Døtre over 14 Aar'),
		);
	}
}
