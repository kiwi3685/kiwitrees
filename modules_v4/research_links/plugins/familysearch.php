<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class familysearch_plugin extends research_base_plugin {
	static function getName() {
		return 'Family Search';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'INT';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return $link = 'https://www.familysearch.org/search/record/results?q.givenName=' . $givn . '&q.surname=' . $surname;
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
