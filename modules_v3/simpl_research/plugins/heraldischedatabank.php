<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class heraldischedatabank_plugin extends research_base_plugin {
	static function getName() {
		return 'Heraldische databank';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return $link = 'http://www.heraldischedatabank.nl/databank/indeling/gallery?q_searchfield=' . $surname;
	}

	static function create_sublink() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}
