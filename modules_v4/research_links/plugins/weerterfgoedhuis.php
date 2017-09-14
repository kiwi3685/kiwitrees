<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class weerterfgoedhuis_plugin extends research_base_plugin {
	static function getName() {
		return 'Weert Erfgoedcluster';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return 'http://studiezaal.erfgoedhuisweert.nl/zoeken.php?/zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=213575952&zoeken%5Bbeschrijvingssoorten%5D%5B203592828%5D=203592828&zoeken%5Bbeschrijvingssoorten%5D%5B203592903%5D=203592903&zoeken%5Bbeschrijvingssoorten%5D%5B203593076%5D=203593076&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=203591258&zoeken%5Bbeschrijvingssoorten%5D%5B203591219%5D=203591219&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=203590035&zoeken%5Bbeschrijvingssoorten%5D%5B203557379%5D=203557379&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=198390950&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=203596302&zoeken%5Bbeschrijvingssoorten%5D%5B203596281%5D=203596281&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=198390981&zoeken%5Bbeschrijvingssoorten%5D%5B198390963%5D=198390963&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=203596861&zoeken%5Bbeschrijvingssoorten%5D%5B203596844%5D=203596844&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=203596300&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=203596320&zoeken%5Bbeschrijvingssoorten%5D%5B203596305%5D=203596305&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=209415267&zoeken%5Bbeschrijvingssoorten%5D%5B209415244%5D=209415244&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=203596339&zoeken%5Bbeschrijvingssoorten%5D%5B203596323%5D=203596323&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=203596357&zoeken%5Bbeschrijvingssoorten%5D%5B203596341%5D=203596341&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=203596377&zoeken%5Bbeschrijvingssoorten%5D%5B203596359%5D=203596359&zoeken%5Bvelden%5D%5BVrij+Zoeken%5D%5Bwaarde%5D=' . $givn . '+' . $surn . '&zoeken%5Bvelden%5D%5BVrij+Zoeken%5D%5Bhighlight%5D=t&zoeken%5Bvelden%5D%5BVrij+Zoeken%5D%5Bglobaal%5D=true&zoeken%5Btransformeren%5D=Publieknew&btn-submit=Zoeken';
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
