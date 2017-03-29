<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class overijsselmsmd_plugin extends research_base_plugin {
	static function getName() {
		return 'Overijssel MSMD';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return "https://www.onderzoekoverijssel.nl/resultaten.php?nav_id=3-0&Globaal=' . $givn . '%20' . $surname . '&Datum%20of%20periode_van=' . $birth_year . '&Datum%20of%20periode_tot=' . $death_year . '";
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
