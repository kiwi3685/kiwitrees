<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class _1921UKcensus_plugin extends research_base_plugin {
	static function getName() {
		return '1921 Census of England and Wales';
	}

	static function getPaySymbol() {
		return true;
	}

	static function getSearchArea() {
		return 'GBR';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return $link = 'https://www.findmypast.com/search/results
		?firstname=' . $first . '
		&firstname_variants=true
		&lastname=' . $surname . '
		&yearofbirth=' . $birth_year . '
		&yearofbirth_offset=1
		&datasetname=1921%20census%20of%20england%20%26%20wales&sid=21';

	}

	static function create_sublink($first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
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
