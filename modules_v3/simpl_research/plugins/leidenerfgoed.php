<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class leidenerfgoed_plugin extends research_base_plugin {
	static function getName() {
		return 'Leiden en omstreken Erfgoed';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return $link = 'https://www.erfgoedleiden.nl/collecties/personen/zoek-op-personen/q/text/' . strtolower($first) . '%20' . strtolower($surname);
	}

	static function create_sublink() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}
