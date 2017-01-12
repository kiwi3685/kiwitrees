<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class westbrabantra_plugin extends research_base_plugin {
	static function getName() {
		return 'West Brabant Regionaal Archief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = 'http://westbrabantsarchief.nl/zoeken?trefwoord=' . $givn . '+' . $surname . '';
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
