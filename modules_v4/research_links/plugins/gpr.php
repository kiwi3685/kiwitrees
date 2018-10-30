<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class gpr_plugin extends research_base_plugin {
	static function getName() {
		return 'Gravestone Photographic Resource';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'INT';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		// This is a post form, so it will be sent with Javascript
		$url	 	= 'https://www.gravestonephotos.com/search/search.php';
		$params	 	= array(
			'forename'		=> $first,
			'exactforename'	=> 'no',
			'surname'		=> $surn,
			'exactsurname'	=> 'no',
			'country'		=> '',
			'area'			=> '',
			'search'		=> 'Full name'
		);
		return "postresearchform('" . $url . "'," . json_encode($params) . ")";
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
