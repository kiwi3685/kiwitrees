<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class tilburgarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Tilburg Regionaal Archief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return $link = 'http://www.regionaalarchieftilburg.nl/zoek-een-persoon/#/persons?%3Fss=%7B%22q%22:%22jan%22%7D&sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22' . strtolower($surname) . '%22,%22search_t_tussenvoegsel%22:%22%22,%22search_t_voornaam%22:%22' . strtolower($first) . '%22%7D%7D';
	}

	static function create_sublink() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}
