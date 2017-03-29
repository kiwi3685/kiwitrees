<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class geneanet_plugin extends research_base_plugin {
	static function getName() {
		return 'Geneanet.org';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'INT';
	}

	static function create_link($fullname, $givn, $first, $prefix, $surn, $surname) {
		return $link = 'http://www.geneanet.org/fonds/individus/?go=1&nom=' . $surname . '&prenom=' . $givn . '&prenom_operateur=or&with_variantes_nom=&with_variantes_nom_conjoint=&with_variantes_prenom=&with_variantes_prenom_conjoint=&size=10';
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
		return true;
	}

}
