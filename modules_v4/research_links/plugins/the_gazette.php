<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class the_gazette_plugin extends research_base_plugin {
	static function getName() {
		return 'The Gazette';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'GBR';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		$birth_year == '' ? $birth_year = '' : $birth_year = $birth_year - 5 . '-01-01';
		$death_year == '' ? $death_year = '' : $death_year = $death_year + 5 . '-12-31';

		return $link = 'https://www.thegazette.co.uk/all-notices/notice?end-publish-date=' . $death_year . '&text=%22' . $first . '+' . $surname .'%22&start-publish-date=' . $birth_year . '&location-distance-1=1&service=all-notices&categorycode-all=all&numberOfLocationSearches=1&results-page-size=10';
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
