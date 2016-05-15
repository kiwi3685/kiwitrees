<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class dordrechtarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Dordrecht Regionaal Archief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = 'http://www.regionaalarchiefdordrecht.nl/archief/zoeken/?mizk_alle=' . $first . '+' . $surname;
	}

	static function create_sublink() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}
