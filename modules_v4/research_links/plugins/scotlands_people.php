<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class scotlands_people_plugin extends research_base_plugin {
	static function getName() {
		return 'Scotlands People';
	}

	static function getPaySymbol() {
		return true;
	}

	static function getSearchArea() {
		return 'GBR';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		$birth_year ? $birth_year = $birth_year - 10 : $birth_year = '';
		return $link = 'https://www.scotlandspeople.gov.uk/js/search-results?search_type=People&surname=' . $surname . '&forename=' . $first . '&to_year=&from_year=' . $birth_year;
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
