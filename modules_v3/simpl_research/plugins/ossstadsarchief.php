<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class ossstadsarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Oss Stadsarchief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		return 'http://osspubliek.hosting.deventit.net/zoeken.php??zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=855905&zoeken%5Bbeschrijvingssoorten%5D%5B855856%5D=855856&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=184&zoeken%5Bbeschrijvingssoorten%5D%5B140%5D=140&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=1127850&zoeken%5Bbeschrijvingssoorten%5D%5B1127808%5D=1127808&zoeken%5Bvelden%5D%5BVrij+zoeken%5D%5Bwaarde%5D=' . $givn . '+' . $surn .'&zoeken%5Bvelden%5D%5BVrij+zoeken%5D%5Bhighlight%5D=t&zoeken%5Bvelden%5D%5BVrij+zoeken%5D%5Btype%5D=default&zoeken%5Bvelden%5D%5BVrij+zoeken%5D%5Bglobaal%5D=true&zoeken%5Btransformeren%5D=&searchtype=new&btn-submit=Zoeken';
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
