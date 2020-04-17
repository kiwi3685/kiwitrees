<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class gemertbakel_plugin extends research_base_plugin {
	static function getName() {
		return 'Gemert-Bakel GA';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return 'https://collecties.gemeentearchiefgemert-bakel.nl/zoeken/groep=Personen/Voornaam=' . $givn . '/Achternaam=' . $surn . '/aantalpp=14/?nav_id=0-0';
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
