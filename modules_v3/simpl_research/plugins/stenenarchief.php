<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class stenenarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Stenen Archief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return 'http://www.stenenarchief.nl/phpr/nik/stenen_archief/nik_list.php?a=integrated&ctlSearchFor=' . $surn . '&simpleSrchFieldsComboOpt=achternaam&simpleSrchTypeComboNot=&simpleSrchTypeComboOpt=Contains';
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
