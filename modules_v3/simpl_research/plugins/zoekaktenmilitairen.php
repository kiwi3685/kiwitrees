<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class zoekaktenmilitairen_plugin extends research_base_plugin {
	static function getName() {
		return 'Zoekakten Militaire Stamboeken';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return $link = 'http://zoekakten.nl/zoekmil2.php?soort=0&anaam=' .$surname . '&vnaam=' . $givn . '&submit=Zoek';
	}

	static function create_sublink() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}
