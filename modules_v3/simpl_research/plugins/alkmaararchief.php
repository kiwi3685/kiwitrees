<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class alkmaararchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Alkmaar Regionaal Archief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return $link = 'https://www.regionaalarchiefalkmaar.nl/collecties/genealogie/aktes/q/persoon_achternaam_t_0/' . strtolower($surname) . '/q/persoon_voornaam_t_0/' . strtolower($first) . '/q/zoekwijze/s';
	}

	static function create_sublink() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}
