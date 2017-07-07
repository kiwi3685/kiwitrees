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
class WT_Census_CensusOfDeutschland1900 extends WT_Census_CensusOfDeutschland implements WT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return '01 DEC 1900';
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
			new WT_Census_CensusColumnNull($this, 'Lfd.Nr.', 'Laufende Nummer'),
			new WT_Census_CensusColumnGivenNames($this, 'Vorname', 'Vorname'),
			new WT_Census_CensusColumnSurname($this, 'Familienname', 'Familienname'),
			new WT_Census_CensusColumnRelationToHeadGerman($this, 'Stellung', 'Verwandtschaft oder sonstige Stellung zum Haushaltungsvorstand'),
			new WT_Census_CensusColumnNull($this, 'männlich', 'Geschlecht männlich'),
			new WT_Census_CensusColumnNull($this, 'weiblich', 'Geschlecht weiblich'),
			new WT_Census_CensusColumnNull($this, 'Blind', 'Blind auf beiden Augen'),
			new WT_Census_CensusColumnNull($this, 'Taubstumm', 'Taubstumm'),
			new WT_Census_CensusColumnNull($this, 'Bemerkungen', 'Bemerkungen'),
			new WT_Census_CensusColumnNull($this, '', 'Nachfolgend die detaillierten Zählkartenangaben'),
			new WT_Census_CensusColumnFullName($this, 'ZK 1.Name', '1. Vor- und Familienname:'),
			new WT_Census_CensusColumnNull($this, 'ZK 2.Geschlecht', '2. Geschlecht:'),
			new WT_Census_CensusColumnNull($this, 'ZK 3.Familienstand', '3. Familienstand:'),
			new WT_Census_CensusColumnBirthDayDotMonthYear($this, 'ZK 4.Alter', '4. Alter: geboren den ... im Jahre ...'),
			new WT_Census_CensusColumnBirthPlace($this, 'ZK 5.Geburtsort', '5. Geburtsort: ... im Bezirk (Amt) ...'),
			new WT_Census_CensusColumnNull($this, 'ZK 5.Land/Provinz', 'für außerhalb des Großherzogthums Geborene auch Geburtsland, für in Preußen Geborene auch Provinz: ...'),
			new WT_Census_CensusColumnOccupation($this, 'ZK 6.Beruf/Stand', '6. Beruf, Stand, Erwerb, Gewerbe, Geschäft oder Nahrungszweig:'),
			new WT_Census_CensusColumnNull($this, 'ZK 7a.Gemeinde Wohnort', '7.a. Gemeinde (Ortschaft), in welcher der Wohnort (Wohnung), bei verheiratheten Personen der Familienwohnsitz liegt:'),
			new WT_Census_CensusColumnNull($this, 'ZK 7a.Land/Provinz', 'für außerhalb des Großherzogthums Wohnende auch Staat und für in Preußen Wohnende auch Provinz: ...'),
			new WT_Census_CensusColumnNull($this, 'ZK 7b.Gemeinde Erwerbsort', '7.b. Gemeinde (Ortschaft), in welcher der Beruf (die Erwerbsthätigkeit) zur Zeit ausgeübt wird oder zuletzt ausgeübt wurde:'),
			new WT_Census_CensusColumnNull($this, 'ZK 7b.Land/Provinz', 'für außerhalb des Großherzogthums Arbeitende auch Staat und für in Preußen Arbeitende auch Provinz: ...'),
			new WT_Census_CensusColumnReligion($this, 'ZK 8.Religion', '8. Religionsbekenntnis:'),
			new WT_Census_CensusColumnNull($this, 'ZK 9.Muttersprache', '9. Muttersprache (ob deutsch oder welche andere Sprache?):'),
			new WT_Census_CensusColumnNull($this, 'ZK 10.StA', '10. Staatsangehörigkeit:'),
			new WT_Census_CensusColumnNull($this, 'ZK 11.Dienstgrad', '11. Für Militärpersonen im aktiven Dienste: Dienstgrad:'),
			new WT_Census_CensusColumnNull($this, 'ZK 11.Einheit', 'Truppentheil, Kommando- oder Verwaltungsbehörde:'),
			new WT_Census_CensusColumnNull($this, 'ZK 12.Gebrechen', '12. Etwaige körperliche Mängel und Gebrechen:'),
		);
	}
}
