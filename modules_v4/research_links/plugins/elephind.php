<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class elephind_plugin extends research_base_plugin {
	static function getName() {
		return 'Elephind Newspapers';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'INT';
	}

	static function create_link($fullname, $givn, $first, $prefix, $surn, $surname, $birth_year, $death_year) {
		return $link = 'http://www.elephind.com/?a=q&hs=1&r=1&results=1&txq=' . $first . '+' . $surname . '&txf=txINtxCO&o=10&dafyq=&dafmq=&dafdq=&datyq=&datmq=&datdq=&puqname=Search+all+titles...&puq=&lcq=&csq=&e=-------en-10--1--txt-txINtxCO----------';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
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
