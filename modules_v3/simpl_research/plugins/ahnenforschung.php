<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class ahnenforschung_plugin extends research_base_plugin {

	static function getName() {
		return 'Ahnenforschung.net';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'DEU';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = 'http://ahnenforschung.net/metasuche.php?query=' . $surname;
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
