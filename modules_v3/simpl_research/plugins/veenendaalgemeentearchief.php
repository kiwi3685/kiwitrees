<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class veenendaalgemeentearchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Veenendaal Gemeentearchief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return $link = 'http://collecties.veenendaal.nl/component/search_all/result?trefwoord=' . $fullname . '';
	}

	static function create_sublink() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}
