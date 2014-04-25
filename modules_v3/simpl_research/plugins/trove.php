<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class trove_plugin extends research_base_plugin {
	static function getName() {
		return 'Trove (Australia)';
	}	
	
	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return $link = 'http://trove.nla.gov.au/result?q=+%22'. $fullname .'%22&l-australian=y';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return false;
	}

	static function encode_plus() {
		return true;	
	}
}
