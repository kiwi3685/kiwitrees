<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class rijnlandsmiddensa_plugin extends research_base_plugin {
	static function getName() {
		return 'Rijnlands Midden Streekarchief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = 'http://www.streekarchiefrijnlandsmidden.nl/collecties/archiefbank?mivast=105&miadt=105&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=' . $surname . '&mip3=' . $givn . '';
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
