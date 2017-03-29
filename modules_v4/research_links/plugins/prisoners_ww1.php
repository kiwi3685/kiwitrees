<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class prisoners_ww1_plugin extends research_base_plugin {

	static function getName() {
		return 'Prisoners of the 1st World War';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'INT';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return $link = 'http://grandeguerre.icrc.org/en/File/Search#/3/2/107/0/British%20and%20Commonwealth/Military/' . $surname;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return false;
	}

	static function createLinkOnly() {
		return false;
	}

	static function createSubLinksOnly() {
		return false;
	}

	static function encode_plus() {
		return false;
	}

}
