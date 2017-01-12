<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class cbgfamilienamen_plugin extends research_base_plugin {
	static function getName() {
		return 'CBG Familienamen';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = 'http://www.cbgfamilienamen.nl/nfb/detail_naam.php?gba_lcnaam=' . $surname . '&gba_naam=' . $surname . '&nfd_naam=' . $surname . '%20(y)&operator=eq&taal=';
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
