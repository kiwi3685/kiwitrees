<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class deceased_online_plugin extends research_base_plugin {
	static function getName() {
		return 'Deceased Online';
	}

	static function getPaySymbol() {
		return true;
	}

	static function getSearchArea() {
		return 'GBR';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return $link = 'https://www.deceasedonline.com/servlet/GSDOSearch?' .'GSDOInptSName=' . $surname .'&GSDOInptFName=' . $first;
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
