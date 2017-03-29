<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class zeeuwengezocht_plugin extends research_base_plugin {
	static function getName() {
		return 'Zeeuwen Gezocht';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return $link = 'http://www.zeeuwengezocht.nl/nl/zoeken?mivast=1539&miadt=239&mizig=862&miview=tbl&milang=nl&micols=1&mires=0&mip3='
						. $surn . '&mip2=' . $prefix . '&mip1=' . $givn;
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
