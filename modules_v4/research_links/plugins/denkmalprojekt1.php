<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class denkmalprojekt1_plugin extends research_base_plugin {

	static function getName() {
		return 'Denkmalprojekt (eigene Suche)';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'DEU';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		$values	 = array(strtoupper($surname), ucfirst($first));
		$query	 = implode('+', array_filter($values, function($v) { return $v !== null && $v !== ''; }));

		return $link = 'http://www.denkmalprojekt.org/search/search.pl?Match=0&Realm=All&Terms=%22' . $query . '%22';
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
