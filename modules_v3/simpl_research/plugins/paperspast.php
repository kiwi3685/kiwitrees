<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class paperspast_plugin extends research_base_plugin {
	static function getName() {
		return 'PapersPast';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NZL';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = 'http://paperspast.natlib.govt.nz/cgi-bin/paperspast?a=q&hs=1&r=1&results=1&dafdq=&dafmq=&dafyq=&datdq=&datmq=&datyq=&pbq=&sf=&ssnip=&tyq=&t=2&txq=' . $first  . '+' . $surname . '&x=26&y=3&e=-------10--1----2%22Jessie+Ledger%22--';
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
