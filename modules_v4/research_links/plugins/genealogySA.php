<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class genealogySA_plugin extends research_base_plugin {
	static function getName() {
		return 'Genealogy SA';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'AUS';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		// This is a post form, so it will be sent with Javascript
		$url	 	= 'https://www.genealogysa.org.au/index.php?option=com_search&Itemid=32';
		$params	 	= array(
			'surname'	=> $surn,
			'gname'		=> $first,
			'year'		=> '',
			'range'		=> 0,
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
