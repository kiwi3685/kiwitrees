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
class KT_Census_CensusOfDeutschlandNL1867 extends KT_Census_CensusOfDeutschland implements KT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '03 DEC 1867';
	}
	
	/**
	 * Where did this census occur, in GEDCOM format.
	 *
	 * @return string
	 */
	public function censusPlace() {
		return 'Mecklenburg-Schwerin (Nachtragsliste), Deutschland';
	}	

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new KT_Census_CensusColumnNull($this, '1.Nr.', 'Ordnungs-Nummer.'),
			new KT_Census_CensusColumnGivenNames($this, '2.Vorname', 'I. Vor- und Familienname jeder Person. Vorname.'),
			new KT_Census_CensusColumnSurname($this, '3.Familienname', 'I. Vor- und Familienname jeder Person. Familienname.'),
			new KT_Census_CensusColumnNull($this, '4.männlich', 'II. Geschlecht männlich.'),
			new KT_Census_CensusColumnNull($this, '5.weiblich', 'II. Geschlecht weiblich.'),
			new KT_Census_CensusColumnBirthYear($this, '6.Geburtsjahr', 'III. Alter.'),
			new KT_Census_CensusColumnReligion($this, '7.Religion', 'IV. Religionsbekenntnis.'),
			new KT_Census_CensusColumnNull($this, '8.ledig', 'V. Familienstand. ledig.'),
			new KT_Census_CensusColumnNull($this, '9.verehelicht', 'V. Familienstand. verehelicht.'),
			new KT_Census_CensusColumnNull($this, '10.verwittwet', 'V. Familienstand. verwittwet.'),
			new KT_Census_CensusColumnNull($this, '11.geschieden', 'V. Familienstand. geschieden.'),
			new KT_Census_CensusColumnNull($this, '12.StA_M-S', 'VI. Staatsangehörigkeit. Mecklenburg-Schwerinscher Unterthan.'),
			new KT_Census_CensusColumnNull($this, '13.StA', 'VI. Staatsangehörigkeit. Anderen Staaten angehörig. Welchem Staat?'),
			new KT_Census_CensusColumnNull($this, '14.', 'VII. Art des Abwesenheit vom Zählungsorte. Nicht über ein Jahr Abwesende als See- oder Flußschiffer.'),
			new KT_Census_CensusColumnNull($this, '15.', 'VII. Art des Abwesenheit vom Zählungsorte. Nicht über ein Jahr Abwesende auf Land- oder Seereisen.'),
			new KT_Census_CensusColumnNull($this, '16.', 'VII. Art des Abwesenheit vom Zählungsorte. Nicht über ein Jahr Abwesende auf Besuch außerhalb des Orts.'),
			new KT_Census_CensusColumnNull($this, '17.', 'VII. Art des Aufenthalts am Zählungsort. Ueber ein Jahr, oder in anderer Art als nach Spalte 14 bis 16 Abwesende.'),
			new KT_Census_CensusColumnNull($this, '18.Aufenthaltsort', 'VIII. Vermuthlicher Aufenthaltsort zur Zählungszeit.'),
		);
	}
}
