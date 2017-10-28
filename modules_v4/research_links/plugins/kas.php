<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class kas_plugin extends research_base_plugin {
	static function getName() {
		return 'Kent Archealogical Society';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'GBR';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return $link = 'https://www.google.co.nz/search?q=%2FResearch%2FLibr%2FMIs%2F+' . $surname . '+site%3Ahttp%3A%2F%2Fwww.kentarchaeology.org.uk&oq=%2FResearch%2FLibr%2FMIs%2F+' . $surname . '+site%3Ahttp%3A%2F%2Fwww.kentarchaeology.org.uk';
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
