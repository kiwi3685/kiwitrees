<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class middenhollandstreekarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Midden Holland Streekarchief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return "https://samh.nl/bronnen/genealogie/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D%7D"; 
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return false;
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
