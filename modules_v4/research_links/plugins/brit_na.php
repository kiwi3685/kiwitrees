<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class brit_na_plugin extends research_base_plugin {
	static function getName() {
		return 'British Newspaper Archive';
	}

	static function getPaySymbol() {
		return true;
	}

	static function getSearchArea() {
		return 'GBR';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		$birth_year ? $birth_year = $birth_year . '-01-01/' : $birth_year = '';
		$death_year ? $death_year = $death_year . '-12-31' : $death_year = '';
		return $link = 'http://www.britishnewspaperarchive.co.uk/search/results/' . $birth_year . $death_year . '?basicsearch=%22' . $givn . '%20' . $surn . '%22&phrasesearch=' . $givn . '%20' . $surn . '&sortorder=score';
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
