<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class register1939_plugin extends research_base_plugin {
	static function getName() {
		return '1939 Register (England & Wales)';
	}

	static function create_link($birth_year, $fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return $link = 'http://search.findmypast.com/results/world-records/1939-register?firstname=' . $givn . '&firstname_variants=true&lastname=' . $surn . '&lastname_variants=true&yearofbirth=' . $birth_year;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return false;
	}

	static function encode_plus() {
		return false;
	}
}
