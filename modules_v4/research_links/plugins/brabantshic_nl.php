<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class brabantshic_nl_plugin extends research_base_plugin {
	static function getName() {
		return 'Brabant BHIC';
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
		$base_url = 'https://www.bhic.nl/';

		$collection = array(
		"BS geboorteakte"                => "memorix/genealogy/search/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_deed_type_title%22:%5B%22BS%20geboorteakte%22%5D%7D",
        "BS huwelijksakte"               => "memorix/genealogy/search/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_deed_type_title%22:%5B%22BS%20huwelijksakte%22%5D%7D",
        "BS overlijdensakte"             => "memorix/genealogy/search/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_deed_type_title%22:%5B%22BS%20overlijdensakte%22%5D%7D",
        "DTB begraafakte"                => "memorix/genealogy/search/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_deed_type_title%22:%5B%22DTB%20begraafakte%22%5D%7D",
        "DTB doopakte"                   => "memorix/genealogy/search/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_deed_type_title%22:%5B%22DTB%20doopakte%22%5D%7D",
        "DTB trouwakte"                  => "memorix/genealogy/search/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_deed_type_title%22:%5B%22DTB%20trouwakte%22%5D%7D",
        "Echtscheidingsakte"             => "memorix/genealogy/search/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_deed_type_title%22:%5B%22echtscheidingsakte%22%5D%7D",
        "Memorie van successie"          => "memorix/genealogy/search/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_deed_type_title%22:%5B%22memorie%20van%20successie%22%5D%7D",
        "Registratie bevolkingsregister" => "memorix/genealogy/search/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_deed_type_title%22:%5B%22registratie%20bevolkingsregister%22%5D%7D",
        "Registratie gevangenisregister" => "memorix/genealogy/search/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_deed_type_title%22:%5B%22registratie%20gevangenisregister%22%5D%7D",
        "Registratie militieregister"    => "memorix/genealogy/search/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_deed_type_title%22:%5B%22registratie%20militieregister%22%5D%7D",
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
