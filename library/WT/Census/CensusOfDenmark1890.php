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
class WT_Census_CensusOfDenmark1890 extends WT_Census_CensusOfDenmark implements WT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '01 FEB 1890';
	}

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new WT_Census_CensusColumnFullName($this, 'Navn', 'Samtlige Personers fulde Navn.', 'width: 200px;'),
			new WT_Census_CensusColumnSexMK($this, 'Køn', 'Kjønnet. Mandkøn (M.) eller Kvindekøn (Kv.).'),
			new WT_Census_CensusColumn($this, 'Alder', 'Alder. Alderen anføres med det fyldte Aar, men for Børn, der ikke have fyldt 1 Aar, anføres „Under 1 Aar“ of Fødselsdagen.'),
			new WT_Census_CensusColumnConditionDanish($this, 'Civilstand', 'Ægteskabelig Stillinge. Ugift (U.), Gift (G.), Enkemand eller Enke (E.), Separeret (S.), Fraskilt (F.).'),
			new WT_Census_CensusColumnReligion($this, 'Trossamfund', 'Trossamfund („Folkekirken“ eller andetSamfund, saasom „det frilutheranske“, „det romersk katholske“, det „mosaiske“ o.s.v.).'),
			new WT_Census_CensusColumnBirthPlace($this, 'Fødested', 'Fødested, nemlig Sognets og Amtets eller Kjøbstadens (eller Handelpladsens) Navn, og for de i Bilandene Fødte samt for Udlændinge Landet, hvori de ere fødte.'),
			new WT_Census_CensusColumnRelationToHead($this, 'Stilling i familien', 'Stilling i Familien (Husfader, Husmoder, Barn, Tjenestetyende, Logerende o.s.v.).'),
			new WT_Census_CensusColumnOccupation($this, 'Erhverv', 'Erhverv (Embede, Forretning, Næringsvej og Titel, samt Vedkommendes Stilling som Hovedperson eller Medhjælper, Forvalter, Svend eller Dreng o.s.v.). - Arten af Erhvervet (Gaardejer, Husmand, Grovsmed, Vognfabrikant, Høker o.s.v.). - Under Fattigforsørgelse.'),
			new WT_Census_CensusColumnNull($this, 'Erhvervsstedet', 'Erhvervsstedet (Beboelseskommunen eller hvilken anden Kommune).'),
			new WT_Census_CensusColumnNull($this, 'Døvstumme', 'Døvstumme.'),
			new WT_Census_CensusColumnNull($this, 'Døve', 'Døve (Hørelson aldeles berøvet).'),
			new WT_Census_CensusColumnNull($this, 'Blinde', 'Blinde (Synet aldeles borsvet).'),
			new WT_Census_CensusColumnNull($this, 'Idioter', 'Uden Forstandsovner (Idioter).'),
			new WT_Census_CensusColumnNull($this, 'Sindssyge', 'Sindssyge.'),
			new WT_Census_CensusColumnNull($this, 'Anmærkninger', 'Anmærkninger.'),
		);
	}
}
