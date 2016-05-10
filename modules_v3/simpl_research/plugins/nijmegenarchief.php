<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class nijmegenarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Nijmegen Regionaal Archief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return $link = 'http://studiezaal.nijmegen.nl/ran/_resultaat.aspx?abc_mode=1&invalshoek=22849198&invalshoeknaam=GENERIEK&aantalperpagina=20&uitgebreid_zoeken=true&locatie=0&personen=1&bsoorten_2126473497=2126473497%2C2126473695%2C2126473881%2C2126473625%2C2126473784&bsoorten_136=136&cmveldValue_VrijXXzoeken=' . strtolower($first) . '+' . strtolower($surname) . '&cmveldName_VrijXXzoeken=VrijXXzoeken&cmveldValue_Periode_van=&cmveldName_Periode_van=Periode&cmveldValue_Periode_tot=&cmveldName_Periode_zoeken=Periode&cmveldValue_Gemeente=&cmveldValue_Sectienr=&cmveldValue_rubriekenselect=X0X&sortering=';
	}

	static function create_sublink() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}
