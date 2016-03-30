<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class allefriezen_plugin extends research_base_plugin {
	static function getName() {
		return 'Alle Friezen';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return $link = 'https://www.allefriezen.nl/zoeken/persons?ss=%7B%22q%22:%22' . $givn . '%20' . $surn . '%22%7D';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return false;
	}

	static function encode_plus() {
		return false;
	}
}
