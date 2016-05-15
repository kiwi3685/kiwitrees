<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class zaanstadarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Zaanstad Archief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = 'http://archief.zaanstad.nl/voorouders?mivast=137&miadt=137&mizig=309&miview=ldt&milang=nl&micols=1&mires=0&mizk_alle=' . strtolower($first) . '+' . strtolower($surname);
	}

	static function create_sublink() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}
