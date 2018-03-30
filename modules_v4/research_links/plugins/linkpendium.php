<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class linkpendium_plugin extends research_base_plugin {
	static function getName() {
		return 'Linkpendium';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'INT';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		// This is a post form, so it will be sent with Javascript
		$url	 		= 'http://www.linkpendium.com/family-discoverer/';
		$params	 		= array(
			'query'		=> $surn,
			'first_name'=> $first,
			'state'		=> 'us',
			'start'		=> '0',
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
