<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class allelimburgers_plugin extends research_base_plugin {
	static function getName() {
		return 'Alle Limburgers';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return $link = 'http://www.allelimburgers.nl/wgpublic/persoonu.php?&search_fd5=%3D%3D' . $surn . '&search_fd6=%3D%3D' . $givn . '&multisearch_fd6=7,8&multisearch_fd16=17&multisearch_fd20=21,22';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return false;
	}

	static function encode_plus() {
		return false;
	}
}
