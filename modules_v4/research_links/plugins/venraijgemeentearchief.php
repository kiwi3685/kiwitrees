<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class venraijgemeentearchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Venraij GA Rooynet';
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
		$base_url = 'https://rooynet.nl/';

		$collection = array(
		"Begraafakte"				    => "voorouders/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22Begraafakte%22%7D%7D&sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D%7D",
		"Doopakte"			            => "voorouders/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22Doopakte%22%7D%7D&sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D%7D",
		"Geboorteakte"			        => "voorouders/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22Geboorteakte%22%7D%7D&sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D%7D",
		"Huwelijksakte"			        => "voorouders/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22Huwelijksakte%22%7D%7D&sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D%7D",
		"Inschrijving register"			=> "voorouders/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22Inschrijving%20register%22%7D%7D&sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D%7D",
		"Notariele akte"		        => "voorouders/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22Notariële%20akte%22%7D%7D&sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D%7D",
		"Overlijdensakte"	            => "voorouders/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22Overlijdensakte%22%7D%7D&sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D%7D",
		"Trouwakte"			            => "voorouders/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22Trouwakte%22%7D%7D&sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D%7D",

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
