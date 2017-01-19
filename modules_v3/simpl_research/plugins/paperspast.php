<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class paperspast_plugin extends research_base_plugin {
	static function getName() {
		return 'PapersPast';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NZL';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		return $link = 'https://paperspast.natlib.govt.nz/newspapers?phrase=2&query=+' . $first  . '+' . $surname . '&start_date=' . $birth_year . '-01-01&end_date=' . $death_year . '-12-31';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		return false;
	}

	static function createLinkOnly() {
		return false;
	}

	static function createSubLinksOnly() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}
