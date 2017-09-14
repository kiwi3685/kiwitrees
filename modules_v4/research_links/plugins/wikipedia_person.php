<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class wikipedia_person_plugin extends research_base_plugin {
	static function getName() {
		return 'Wikipedia-Personensuche';  // uses German wikipedia
	}

	static function getPaySymbol() {
		return true;
	}

	static function getSearchArea() {
		return 'DEU';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return $link = 'https://toolserver.org/~apper/pd/person/' . $givn . '_' . $surname;
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
