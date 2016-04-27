<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class groningerarchieven_plugin extends research_base_plugin {
	static function getName() {
		return 'RHC Groninger archieven';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return $link = 'https://www.groningerarchieven.nl/zoeken/mais/archief/?q=' . strtolower($first) . '+' . strtolower($surname);
	}

	static function create_sublink() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}
