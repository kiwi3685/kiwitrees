<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class freebmd_plugin extends research_base_plugin {
	static function getName() {
		return 'Free BMD';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'GBR';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		// This is a post form, so it will be sent with Javascript
		$birth_year == '' ? $birth_year = '' : $birth_year = $birth_year - 5;
		$birth_year = max($birth_year, 1837); // Sep qtr 1837 is earlies avaiable data
		$death_year == '' ? $death_year = '' : $death_year = $death_year + 5;
		$death_year = min($death_year, 1983); // Only data before 1984 is available
		$url	 	= 'https://www.freebmd.org.uk/cgi/search.pl';
		$params	 	= array(
			'type'		=> 'All Types',
			'surname'	=> $surn,
			'given'		=> $first,
			'sq'		=> ($birth_year == 1837 ? '3' : '1'),
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
