<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class archivesnz_plugin extends research_base_plugin {
	static function getName() {
		return 'Archives NZ';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NZL';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return $link = 'https://archway.archives.govt.nz/SimpleSearch.do?keyword=' . $fullname;
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
		return true;
	}
}
