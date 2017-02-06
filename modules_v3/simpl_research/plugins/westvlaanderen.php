<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class westvlaanderen_plugin extends research_base_plugin {
	static function getName() {
		return 'West-Vlaanderen Database';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'BEL';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		return false;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		return false;
	}

	static function createLinkOnly() {
		return 'http://www.vrijwilligersrab.be';
	}

	static function createSubLinksOnly() {
		return false;
	}

	static function encode_plus() {
		return false;
	}

}
