<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class scotlands_people_plugin extends research_base_plugin {
	static function getName() {
		return 'Scotlands People';
	}

	static function getPaySymbol() {
		return true;
	}

	static function getSearchArea() {
		return 'GBR';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = 'http://www.scotlandspeople.gov.uk/people/' . $first .'_' . $surname .'_1513_2012';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return false;
	}

	static function encode_plus() {
		return false;
	}
}
