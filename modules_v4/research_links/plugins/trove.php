<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class trove_plugin extends research_base_plugin {
	static function getName() {
		return 'Trove';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'AUS';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return $link = 'http://trove.nla.gov.au/result?q=+%22'. $fullname .'%22&l-australian=y';
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
		return true;
	}
}
