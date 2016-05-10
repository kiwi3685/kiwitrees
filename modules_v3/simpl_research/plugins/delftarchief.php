<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class delftarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Delft Archief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return $link = 'http://collectie-delft.nl/zoeken?trefwoord=' . $first . '+' . $surname . '&search-source=bronnen';
	}

	static function create_sublink() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}
