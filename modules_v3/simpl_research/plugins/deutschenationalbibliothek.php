<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class deutschenationalbibliothek_plugin extends research_base_plugin {

	static function getName() {
		return 'Deutsche Nationalbibliothek';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'DEU';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		$values	 = array($surname, $first);
		$query	 = implode('+', array_filter($values, function($v) { return $v !== null && $v !== ''; }));

		return $link = 'https://portal.dnb.de/opac.htm?query=' . $query . '&method=simpleSearch';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
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
