<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class overijsselhistorischcentrum_plugin extends research_base_plugin {
	static function getName() {
		return 'Overijssel Historisch Centrum';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = 'http://www.historischcentrumoverijssel.nl/zoeken-in-de-collecties/archieven?mivast=141&miadt=141&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=' . $surn . '&mip1=' . $givn;
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
		return false;
	}
}
