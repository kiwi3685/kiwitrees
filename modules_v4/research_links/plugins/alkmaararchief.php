<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class alkmaararchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Alkmaar Regionaal Archief';
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
		$base_url = 'https://www.regionaalarchiefalkmaar.nl/';

		$collection = array(
			"Bevolkingsregister"=> "collecties/personen-zoeken/zoeken-in-personen/persons?f=%7B%22search_s_register_type_title%22:%7B%22v%22:%22bevolkingsregister%22%7D%7D&sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D%7D",
        "Burgelijke stand"      => "collecties/personen-zoeken/zoeken-in-personen/persons?f=%7B%22search_s_register_type_title%22:%7B%22v%22:%22burgerlijke%20standregister%22%7D%7D&sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D%7D",
        "DTB"                   => "collecties/personen-zoeken/zoeken-in-personen/persons?f=%7B%22search_s_register_type_title%22:%7B%22v%22:%22doop-,%20trouw-%20en%20begraafregister%22%7D%7D&sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D%7D",
        "Notarieel"             => "collecties/personen-zoeken/zoeken-in-personen/persons?f=%7B%22search_s_register_type_title%22:%7B%22v%22:%22notari%C3%ABle%20archief%22%7D%7D&sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D%7D",
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
