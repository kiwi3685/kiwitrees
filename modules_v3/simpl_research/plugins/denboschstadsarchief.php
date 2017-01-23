<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class denboschstadsarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Den Bosch Stadsarchief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		return 'http://denboschpubliek.hosting.deventit.net/zoeken.php?/zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=38089355&zoeken%5Bbeschrijvingssoorten%5D%5B11313400%5D=11313400&zoeken%5Bbeschrijvingssoorten%5D%5B177483877%5D=177483877&zoeken%5Bbeschrijvingssoorten%5D%5B177484175%5D=177484175&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=174274581&zoeken%5Bbeschrijvingssoorten%5D%5B11313385%5D=11313385&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D='  . $surn . '&zoeken%5Bvelden%5D%5BPeriode_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BSoort%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BSoort%5D%5Bvoorwaarde%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bvoorwaarde%5D=GelijkAan&searchtype=new&btn-submit=Zoeken';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
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
