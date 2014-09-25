<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class brit_na_plugin extends research_base_plugin {
	static function getName() {
		return 'British Newspaper Archive';
	}

	static function create_link($fullname, $givn, $first, $prefix, $surn, $surname) {
		return $link = 'http://www.britishnewspaperarchive.co.uk/search/results?basicsearch=%22' . $first . '%20' . $surname				. '%22';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return false;
	}
	
	static function encode_plus() {
		return false;	
	}
}
