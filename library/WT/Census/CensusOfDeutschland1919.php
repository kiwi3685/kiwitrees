<?php
/**
 * webtrees: online genealogy
 * Copyright (C) 2015 webtrees development team
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
class WT_Census_CensusOfDeutschland1919 extends WT_Census_CensusOfDeutschland implements WT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '08 OCT 1919';
	}

	/**
	 * Where did this census occur, in GEDCOM format.
	 *
	 * @return string
	 */
	public function censusPlace() {
		return 'Mecklenburg-Schwerin, Deutschland';
	}	

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new WT_Census_CensusColumnNull($this, 'Nummer', 'Laufende Nummer'),
			new WT_Census_CensusColumnGivenNames($this, 'Vorname', 'Vorname'),
			new WT_Census_CensusColumnSurname($this, 'Familienname', 'Familienname'),
			new WT_Census_CensusColumnRelationToHeadGerman($this, 'Stellung im Haushalt', 'Stellung im Haushalt'),
			new WT_Census_CensusColumnNull($this, 'männlich', 'Geschlecht männlich'),
			new WT_Census_CensusColumnNull($this, 'weiblich', 'Geschlecht weiblich'),
			new WT_Census_CensusColumnNull($this, 'Familienstand', 'Familienstand'),
			new WT_Census_CensusColumnBirthDay($this, 'Geburts-Tag', 'Geburts-Tag'),
			new WT_Census_CensusColumnBirthMonth($this, 'Geburts-Monat', 'Geburts-Monat'),
			new WT_Census_CensusColumnBirthYear($this, 'Geburts-Jahr', 'Geburts-Jahr'),
			new WT_Census_CensusColumnBirthPlace($this, 'Geburtsort', 'Name des Geburtsorts'),
			new WT_Census_CensusColumnNull($this, 'Amt, Kreis, Bezirk', 'Amt, Kreis oder sonstiger obrigkeitlicher Bezirk'),
			new WT_Census_CensusColumnNull($this, 'StA', 'Staatsangehörigkeit'),
			new WT_Census_CensusColumnNull($this, 'Gemeinde Brotversorgung', 'Gemeinde der Brotversorgung'),
			new WT_Census_CensusColumnNull($this, 'Wohn-/ Aufenthaltsort', 'Wohnort bei nur vorübergehend Anwesenden. Aufenthaltsort bei vorübergehend Abwesenden'),
			new WT_Census_CensusColumnNull($this, 'Dienstgrad', 'Für Militärpersonen: Angabe des Dienstgrades'),
			new WT_Census_CensusColumnNull($this, 'Kriegsgefangener', 'Angabe ob Kriegsgefangener'),
		);
	}
}
