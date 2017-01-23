<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class apeldoorncoda_plugin extends research_base_plugin {
	static function getName() {
		return 'Apeldoorn CODA';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		return 'http://archieven.coda-apeldoorn.nl/zoeken.php?/zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=56649628&zoeken%5Bbeschrijvingssoorten%5D%5B56649545%5D=56649545&zoeken%5Bbeschrijvingssoorten%5D%5B56649630%5D=56649630&zoeken%5Bbeschrijvingssoorten%5D%5B56649842%5D=56649842&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=105&zoeken%5Bbeschrijvingssoorten%5D%5B79%5D=79&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=16438161&zoeken%5Bbeschrijvingssoorten%5D%5B16438147%5D=16438147&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=122&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=66456484&zoeken%5Bbeschrijvingssoorten%5D%5B108%5D=108&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=66448399&zoeken%5Bbeschrijvingssoorten%5D%5B66448361%5D=66448361&zoeken%5Bvelden%5D%5BVrij+zoeken%5D%5Bwaarde%5D=' . $givn . '+' . $surn . '&zoeken%5Bvelden%5D%5BVrij+zoeken%5D%5Bhighlight%5D=t&zoeken%5Bvelden%5D%5BVrij+zoeken%5D%5Bglobaal%5D=true&zoeken%5Bvelden%5D%5BPeriode_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_van%5D%5Bhighlight%5D=f&zoeken%5Bvelden%5D%5BPeriode_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_tot%5D%5Bhighlight%5D=f&zoeken%5Bvelden%5D%5BPeriode%5D%5Bglobaal%5D=false&zoeken%5Btransformeren%5D=Toon+zoekresultaten+als+Archieven&btn-submit=Zoeken';
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
