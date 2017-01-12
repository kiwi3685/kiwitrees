<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class cbgfamiliewapens_plugin extends research_base_plugin {
	static function getName() {
		return 'CBG Familiewapens';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = 'http://cbgfamiliewapens.nl/databank/indeling/gallery?q_searchfield=' . $surname;
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
