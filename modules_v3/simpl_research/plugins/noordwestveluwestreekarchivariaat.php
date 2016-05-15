<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class noordwestveluwestreekarchivariaat_plugin extends research_base_plugin {
	static function getName() {
		return 'Noordwest-Veluwe SA';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = 'http://www.streekarchivariaat.nl/nl/alle-personen?mivast=434&miadt=434&mizig=100&miview=ldt&milang=nl&micols=1&mires=0&mip1=' . $surname . '&mip3=' . $first;
	}

	static function create_sublink() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}
