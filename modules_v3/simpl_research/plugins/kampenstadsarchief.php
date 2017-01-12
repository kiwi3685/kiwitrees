<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class kampenstadsarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Kampen Stadsarchief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return $link = 'http://www.stadsarchiefkampen.nl/direct-zoeken-2/doorzoek-alles-2?mivast=69&miadt=69&mizig=0&miview=lst&milang=nl&micols=1&mires=0&mizk_alle=' . $fullname . '';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return false;
	}

	static function createLinkOnly() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}
