<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class mensenlinq_plugin extends research_base_plugin {
	static function getName() {
		return 'Mensenlinq.nl';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return $link = 'http://www.mensenlinq.nl/site/advertentie/overzicht?advzoek_vandag=01&advzoek_vanmaand=1&advzoek_vanjaar=2006&advzoek_totdag=&advzoek_totmaand=&advzoek_totjaar=&advzoek_dag=&advzoek_maand=&advzoek_jaar=&advzoek_provincie=&advzoek_titel=&advzoek_zoek=' . $surname . '&advzoek_plaats=&advzoek_voornaam=' . $first . '&advzoek_geboorteplaats=';
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
