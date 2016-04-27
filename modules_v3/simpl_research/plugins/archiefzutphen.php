<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class archiefzutphen_plugin extends research_base_plugin {
	static function getName() {
		return 'Regionaal Archief Zutphen';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return $link = 'http://www.regionaalarchiefzutphen.nl/voorouders/persons?ss=%7B%22q%22:%22' . $first . '%20' . $surname . '%22%7D';
	}

	static function create_sublink() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}
