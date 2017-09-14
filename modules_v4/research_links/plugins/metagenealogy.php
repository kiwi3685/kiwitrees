<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class metagenealogy_plugin extends research_base_plugin {
	static function getName() {
		return 'Genealogy.net Meta Search';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'DEU';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return false;
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		// Often it's better to run the search just with the surname.
		// It's a post form, so it will be send by javascript in a new window.
		$url = 'http://meta.genealogy.net/search/index';
		$params = array(
			'lastname'	 => $surn,
			'placename'	 => ''
		);
		for ($i = 1; $i <= 19; $i++) {
			$params['partner' . $i] = 'on';
		}
		return "postresearchform('" . $url . "'," . json_encode($params) . ")";
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
