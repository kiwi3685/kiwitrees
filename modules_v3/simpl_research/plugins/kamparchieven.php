<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class kamparchieven_plugin extends research_base_plugin {
	static function getName() {
		return 'Kamparchieven';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = 'https://www.kamparchieven.nl/zoeken?mivast=959&miadt=959&mizig=0&miview=lst&milang=nl&micols=1&mires=0&mizk_alle=' . $surname . '';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return false; // NOT NORMALLY USED. LEAVE AS false FOR SIMPLE LINKS
	}

	static function createLinkOnly() {
		return false;
	}

	static function encode_plus() {
		return false;
	}

}
