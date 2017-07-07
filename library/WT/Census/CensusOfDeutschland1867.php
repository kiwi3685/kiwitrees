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
class WT_Census_CensusOfDeutschland1867 extends WT_Census_CensusOfDeutschland implements WT_Census_CensusInterface {
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
		return 'Mecklenburg-Schwerin, Deutschland';
	}	

	/**
	 * The columns of the census.
	 *
	 * @return CensusColumnInterface[]
	 */
	public function columns() {
		return array(
			new WT_Census_CensusColumnNull($this, '1.Nr.', 'Ordnungs-Nummer (1-15).'),
			new WT_Census_CensusColumnGivenNames($this, '2.Vorname', 'I. Vor- und Familien-Name jeder Person. Vorname'),
			new WT_Census_CensusColumnSurname($this, '3.Familienname', 'I. Vor- und Familien-Name jeder Person. Familienname.'),
			new WT_Census_CensusColumnNull($this, '4.männlich', 'II. Geschlecht männlich.'),
			new WT_Census_CensusColumnNull($this, '5.weiblich', 'II. Geschlecht weiblich.'),
			new WT_Census_CensusColumnBirthYear($this, '6.Geburtsjahr', 'III. Alter.'),
			new WT_Census_CensusColumnReligion($this, '7.Religion', 'IV. Religionsbekenntnis.'),
			new WT_Census_CensusColumnNull($this, '8.ledig', 'V. Familienstand. ledig.'),
			new WT_Census_CensusColumnNull($this, '9.verehelicht', 'V. Familienstand. verehelicht.'),
			new WT_Census_CensusColumnNull($this, '10.verwittwet', 'V. Familienstand. verwittwet.'),
			new WT_Census_CensusColumnNull($this, '11.geschieden', 'V. Familienstand. geschieden.'),
			new WT_Census_CensusColumnRelationToHeadGerman($this, '12.Stellung', 'V. Familienstand. Verhältnis der Familienglieder zum Haushaltungsvorstand.'),
			new WT_Census_CensusColumnOccupation($this, '13.Stand/Beruf', 'VI. Stand, Beruf oder Vorbereitung zum Beruf, Arbeits- und Dienstverhältnis.'),
			new WT_Census_CensusColumnNull($this, '14.StA_M-S', 'VII. Staatsangehörigkeit. Mecklenburg-Schwerinscher Unterthan.'),
			new WT_Census_CensusColumnNull($this, '15.StA', 'VII. Staatsangehörigkeit. Anderen Staaten angehörig. Welchem Staat?'),
			new WT_Census_CensusColumnNull($this, '16.', 'VIII. Art des Aufenthalts am Zählungsort. Norddeutscher und Zollvereins- See- und Flußschiffer.'),
			new WT_Census_CensusColumnNull($this, '17.', 'VIII. Art des Aufenthalts am Zählungsort. Reisender im Gasthof.'),
			new WT_Census_CensusColumnNull($this, '18.', 'VIII. Art des Aufenthalts am Zählungsort. Gast der Familie (zum Besuch aus).'),
			new WT_Census_CensusColumnNull($this, '19.', 'VIII. Art des Aufenthalts am Zählungsort. Alle übrigen Anwesenden.'),
			new WT_Census_CensusColumnNull($this, '20.blind', 'IX. Besondere Mängel einzelner Individuen. blind auf beiden Augen.'),
			new WT_Census_CensusColumnNull($this, '21.taubstumm', 'IX. Besondere Mängel einzelner Individuen. taubstumm.'),
			new WT_Census_CensusColumnNull($this, '22.blödsinnig', 'IX. Besondere Mängel einzelner Individuen. blödsinnig.'),
			new WT_Census_CensusColumnNull($this, '23.irrsinnig', 'IX. Besondere Mängel einzelner Individuen. irrsinnig.'),
		);
	}
}
