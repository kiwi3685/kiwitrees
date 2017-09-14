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
class KT_Census_CensusOfDeutschland1819 extends KT_Census_CensusOfDeutschland implements KT_Census_CensusInterface {
	/**
	 * When did this census occur.
	 *
	 * @return string
	 */
	public function censusDate() {
		return 'AUG 1819';
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
			new KT_Census_CensusColumnNull($this, 'Nr.', 'Laufende Num̅er.'),
			new KT_Census_CensusColumnNull($this, 'Geschlecht', 'Ob männlichen oder weiblichen Geschlechts.'),
			new KT_Census_CensusColumnFullName($this, 'Name', 'Vor- und Zuname.'),
			new KT_Census_CensusColumnBirthYear($this, 'Geburtsdatum', 'Jahr und Tag der Geburt.'),
			new KT_Census_CensusColumnBirthPlace($this, 'Geburtsort', 'Geburtsort.'),
			new KT_Census_CensusColumnNull($this, 'Kirchspiel', 'Kirchspiel, wohin der Geburtsort gehört.'),
			new KT_Census_CensusColumnNull($this, '', 'leere Spalte'),
			new KT_Census_CensusColumnOccupation($this, 'Stand/Beruf', 'Stand und Gewerbe.'),
			new KT_Census_CensusColumnNull($this, 'Besitz', 'Grundbesitz.'),
			new KT_Census_CensusColumnNull($this, 'hier seit', 'Wie lange er schon hier ist.'),
			new KT_Census_CensusColumnNull($this, 'Familienstand', 'Ob ledig oder verheirathet.'),
			new KT_Census_CensusColumnReligion($this, 'Religion', 'Religion.'),
			new KT_Census_CensusColumnNull($this, 'Bemerkungen', 'Allgemeine Bemerkungen.'),
		);
	}
}
