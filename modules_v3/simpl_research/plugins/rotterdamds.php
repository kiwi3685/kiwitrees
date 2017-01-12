<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class rotterdamds_plugin extends research_base_plugin {
	static function getName() {
		return 'Rotterdam Digitale Stamboom';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = 'http://rotterdam.digitalestamboom.nl/search.aspx?lang=nl&verder=' . $givn . urlencode('||') . $prefix . urlencode('|') . $surn;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return false;
	}

	static function createLinkOnly() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}
