<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class westfriesarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Westfries archief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = 'http://www.westfriesarchief.nl/onderzoek/zoeken/personen?mivast=136&miadt=136&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=' . $surname . '&mip3=' . $first;
	}

	static function create_sublink() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}
