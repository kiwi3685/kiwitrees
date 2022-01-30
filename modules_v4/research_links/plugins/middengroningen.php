<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class middengroningen_plugin extends research_base_plugin {
	static function getName() {
		return 'Midden-Groningen HA';
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
		$base_url = 'https://historischarchief.midden-groningen.nl/';

		$collection = array(
			"Bevolkingsregister"  => "collectie/personen/personen-view/persons?f=%7B%22search_s_register_naam%22:%7B%22v%22:%22Bevolkingsregister%22%7D%7D&sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D%7D",
			"Geboorteregister"    => "collectie/personen/personen-view/persons?f=%7B%22search_s_register_naam%22:%7B%22v%22:%22Geboorteregister%22%7D%7D&sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D%7D",
			"Huwelijksregister"   => "collectie/personen/personen-view/persons?f=%7B%22search_s_register_naam%22:%7B%22v%22:%22Huwelijksregister%22%7D%7D&sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D%7D",
			"Overlijdensregister" => "collectie/personen/personen-view/persons?f=%7B%22search_s_register_naam%22:%7B%22v%22:%22Overlijdensregister%22%7D%7D&sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D%7D",
            "Volkstelling"        => "collectie/personen/personen-view/persons?f=%7B%22search_s_register_naam%22:%7B%22v%22:%22Volkstelling%22%7D%7D&sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D%7D",
					);

		foreach($collection as $key => $value) {
			$link[] = array(
				'title' => KT_I18N::translate($key),
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
