<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class sample_js_link_plugin extends research_base_plugin {
	static function getName() {
		return 'Sample JavaScript link';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'GBR';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		$url	 	= 'http://www.freebmd.org.uk/cgi/search.pl';
		$params	 	= array(
			'type'		=> 'All Types',
			'surname'	=> $surn,
			'given'		=> $first,
			'sq'		=> '1',
			'start'		=> $birth_year,
			'eq'		=> '4',
			'end'		=> $death_year,
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
