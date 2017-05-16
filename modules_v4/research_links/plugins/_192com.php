<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class _192com_plugin extends research_base_plugin {
	static function getName() {
		return '192.com';
	}

	static function getPaySymbol() {
		return true;
	}

	static function getSearchArea() {
		return 'GBR';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		// This is a post form, so it will be sent with Javascript
		$url	 	= 'http://www.192.com/all/search/';
		$params	 	= array(
			'looking_for'		=> $first . ' ' . substr($middle, 0, 1) . ' ' . $surn
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
