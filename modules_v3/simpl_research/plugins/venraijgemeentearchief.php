<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class venraijgemeentearchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Venraij Gemeentearchief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return $link = 'http://gemeentearchiefvenray.nl/genealogie/zoeken-door-personen/q/persoon_achternaam_t_0/' . $surname . '/q/persoon_voornaam_t_0/' . strtolower($first);
	}

	static function create_sublink() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}