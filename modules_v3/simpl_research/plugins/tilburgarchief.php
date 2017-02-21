<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class tilburgarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Tilburg Regionaal Archief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return false;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		$base_url = 'http://www.regionaalarchieftilburg.nl/';

		$collection = array(
			"Akten"			    => "zoek-een-persoon/#/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_deed_type_title%22:%5B%22akte%22%5D%7D",
			"Begraafakte"		=> "zoek-een-persoon/#/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_deed_type_title%22:%5B%22begraafakte%22%5D%7D",
			"Bidprentje"		=> "zoek-een-persoon/#/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_deed_type_title%22:%5B%22bidprentje%22%5D%7D",
			"Doopakte"		    => "zoek-een-persoon/#/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_deed_type_title%22:%5B%22doopakte%22%5D%7D",
			"Geboorteakte"	    => "zoek-een-persoon/#/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_deed_type_title%22:%5B%22geboorteakte%22%5D%7D",
			"Huwelijksakte"		=> "zoek-een-persoon/#/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_deed_type_title%22:%5B%22huwelijksakte%22%5D%7D",
			"Inschrijving"		=> "zoek-een-persoon/#/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_deed_type_title%22:%5B%22inschrijving%22%5D%7D",
			"Overlijdensakte"	=> "zoek-een-persoon/#/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_deed_type_title%22:%5B%22overlijdensakte%22%5D%7D",
			"Trouwakte"			=> "zoek-een-persoon/#/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_deed_type_title%22:%5B%22trouwakte%22%5D%7D",
		);

		foreach($collection as $key => $value) {
			$link[] = array(
				'title' => WT_I18N::translate($key),
				'link'  => $base_url . $value
			);
		}

		return $link;
	}

	static function createLinkOnly() {
		return false;
	}

	static function createSubLinksOnly() {
		return false;
	}

	static function encode_plus() {
		return false;
	}

}
