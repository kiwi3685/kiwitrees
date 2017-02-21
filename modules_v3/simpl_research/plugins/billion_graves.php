<?php

if (!defined('WT_WEBTREES')) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

class billion_graves_plugin extends research_base_plugin {

    static function getName() {
        return 'Billion Graves';
    }

    static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'INT';
	}

    static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
        return $link = 'https://billiongraves.com/search/results?given_names=' . $givn . '&family_names=' . $surname . '&birth_year=' . $birth_year . '&death_year=' . $death_year . '&year_range=5&action=search&exact=true#/';
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
