<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class sittardgeleenrhc_plugin extends research_base_plugin {
	static function getName() {
		return 'Sittard-Geleen RHC';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = 'http://ehc.sittard-geleen.eu/zoeken-in-de-archieven-resultaat?mivast=111&miadt=111&mizig=0&miview=lst&milang=nl&micols=1&mires=0&mizk_alle=' . $givn . '%20' . $surname . '';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return false; // NOT NORMALLY USED. LEAVE AS false FOR SIMPLE LINKS
	}

	static function createLinkOnly() {
		return false;
	}

	static function encode_plus() {
		return false;
	}

}
