<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class oorlogsgravenstichting_plugin extends research_base_plugin {
	static function getName() {
		return 'Oorlogsgravenstichting';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = 'https://oorlogsgravenstichting.nl/zoeken?object=persoon&q=&excact=1&filter%5B0%5D=achternaam&value%5B0%5D=' . $surname . '&filter%5B1%5D=voornamen&value%5B1%5D=' . $first . '';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return false; // NOT NORMALLY USED. LEAVE AS false FOR SIMPLE LINKS
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
