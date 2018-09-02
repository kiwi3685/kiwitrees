<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class rijnstreekrhc_plugin extends research_base_plugin {
	static function getName() {
		return 'Rijnstreek en Lopikerwaard';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return "https://archief.rhcrijnstreek.nl/zoeken.php?zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=227&zoeken%5Bbeschrijvingssoorten%5D%5B193%5D=193&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=7879447&zoeken%5Bbeschrijvingssoorten%5D%5B19764912%5D=19764912&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=7881182&zoeken%5Bbeschrijvingssoorten%5D%5B7866604%5D=7866604&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=19238956&zoeken%5Bbeschrijvingssoorten%5D%5B595%5D=595&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=6964847&zoeken%5Bbeschrijvingssoorten%5D%5B703%5D=703&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=19238958&zoeken%5Bbeschrijvingssoorten%5D%5B654%5D=654&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=7881575&zoeken%5Bbeschrijvingssoorten%5D%5B7877169%5D=7877169&zoeken%5Bvelden%5D%5BAlle+velden%5D%5Bwaarde%5D=$fullname&zoeken%5Bvelden%5D%5BAlle+velden%5D%5Bhighlight%5D=t&zoeken%5Bvelden%5D%5BAlle+velden%5D%5Btype%5D=default&zoeken%5Bvelden%5D%5BAlle+velden%5D%5Bglobaal%5D=true&zoeken%5Bvelden%5D%5BJaar_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BJaar_van%5D%5Bhighlight%5D=f&zoeken%5Bvelden%5D%5BJaar_van%5D%5Btype%5D=date&zoeken%5Bvelden%5D%5BJaar_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BJaar_tot%5D%5Bhighlight%5D=f&zoeken%5Bvelden%5D%5BJaar_tot%5D%5Btype%5D=date&zoeken%5Bvelden%5D%5BJaar%5D%5Bglobaal%5D=false&zoeken%5Btransformeren%5D=&searchtype=new&btn-submit=Zoeken";
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
