<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class deventerstadsarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Deventer Stadsarchief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = 'http://www.stadsarchiefdeventer.nl/zoeken-in-de-collecties/archieven?mivast=45&miadt=45&mizig=0&miview=lst&milang=nl&micols=1&mires=0&mizk_alle=' . $first . '%20' . $surname;
	}

	static function create_sublink() {
		return false;
	}

	static function createLinkOnly() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}
