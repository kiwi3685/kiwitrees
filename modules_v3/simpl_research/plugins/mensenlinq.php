<?php

if (!defined('WT_WEBTREES')) {
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

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return $link = 'http://www.mensenlinq.nl/site/advertentie/overzicht?advzoek_vandag=01&advzoek_vanmaand=1&advzoek_vanjaar=2006&advzoek_totdag=18&advzoek_totmaand=4&advzoek_totjaar=2016&advzoek_dag=18&advzoek_maand=4&advzoek_jaar=2016&advzoek_provincie=&advzoek_titel=&advzoek_zoek=' . $surname . '&advzoek_plaats=&advzoek_voornaam=' . $first . '&advzoek_geboorteplaats=';
	}

	static function create_sublink() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}
