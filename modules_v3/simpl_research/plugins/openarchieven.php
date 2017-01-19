<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class openarchieven_plugin extends research_base_plugin {
	static function getName() {
		return 'Open Archieven';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		$languages = array('de', 'en', 'fr', 'nl');

		$language = WT_LOCALE;
		if (!in_array($language, $languages)) {
			$language = 'en';
		}
		return $link = 'https://www.openarch.nl/search.php?lang=' . $language . '&name=' . $fullname . '&number_show=10&sort=1';
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
