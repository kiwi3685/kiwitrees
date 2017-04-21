<?php

if (!defined('WT_KIWITREES')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

class fold3_plugin extends research_base_plugin {

    static function getName() {
        return 'Fold3';
    }

    static function getPaySymbol() {
		return true;
	}

	static function getSearchArea() {
		return 'USA';
	}

    static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return $link = 'https://go.fold3.com/query.php?query=' . $surname . '+' . $first;
    }

    static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
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
