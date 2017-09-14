<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class denhaaggemeentearchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Den Haag Gemeentearchief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return 'http://hgapubliek.hosting.deventit.net/zoeken.php?zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=710006709&zoeken%5Bbeschrijvingssoorten%5D%5B709753399%5D=709753399&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=710007035&zoeken%5Bbeschrijvingssoorten%5D%5B709753386%5D=709753386&zoeken%5Bbeschrijvingssoorten%5D%5B709753435%5D=709753435&zoeken%5Bbeschrijvingssoorten%5D%5B709753487%5D=709753487&zoeken%5Bbeschrijvingssoorten%5D%5B709753525%5D=709753525&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=710006719&zoeken%5Bbeschrijvingssoorten%5D%5B709753411%5D=709753411&zoeken%5Bbeschrijvingssoorten%5D%5B709753474%5D=709753474&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=710007095&zoeken%5Bbeschrijvingssoorten%5D%5B709753423%5D=709753423&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=710007123&zoeken%5Bbeschrijvingssoorten%5D%5B709753447%5D=709753447&zoeken%5Bbeschrijvingssoorten%5D%5B709753563%5D=709753563&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=710007108&zoeken%5Bbeschrijvingssoorten%5D%5B709753499%5D=709753499&zoeken%5Bbeschrijvingssoorten%5D%5B709753550%5D=709753550&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=709762975&zoeken%5Bbeschrijvingssoorten%5D%5B709762987%5D=709762987&zoeken%5Bvelden%5D%5Bglobal%5D=' . $givn . '+' . $surname . '&btn-submit=Zoeken';
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
