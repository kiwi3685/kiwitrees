<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class noord_hollands_plugin extends research_base_plugin {
	static function getName() {
		return 'Noord-Hollands archief';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return $link = 'http://noord-hollandsarchief.nl/personen/databases?mivast=236&miadt=236&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=' . $surn . '&mip3=' . $givn;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return false;
	}

	static function encode_plus() {
		return false;
	}
}
