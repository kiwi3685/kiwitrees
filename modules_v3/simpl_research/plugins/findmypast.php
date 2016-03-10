<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class findmypast_plugin extends research_base_plugin {
	static function getName() {
		return 'findmypast';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = 'http://search.findmypast.com/search/world-records?firstname=' . $givn . '&firstname_variants=true&lastname=' . $surname . '&yearofbirth=' . $birth_year . '&yearofbirth_offset=2';
	}
	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return false;
	}

	static function encode_plus() {
		return false;
	}
}
