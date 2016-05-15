<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class zoekaktenlandverhuizers_plugin extends research_base_plugin {
	static function getName() {
		return 'Zoekakten Landverhuizers';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = 'http://zoekakten.nl/zoeklv2.php?soort=0&naam=' .$surname . '&submit=Zoek';
	}

	static function create_sublink() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}
