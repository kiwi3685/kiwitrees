<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class gemertbakel_plugin extends research_base_plugin {
	static function getName() {
		return 'Gemert-Bakel GA';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		return 'http://gemertbakelpubliek.hosting.deventit.net/zoeken.php?zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=240&zoeken%5Bbeschrijvingssoorten%5D%5B224%5D=224&zoeken%5Bbeschrijvingssoorten%5D%5B35669825%5D=35669825&zoeken%5Bbeschrijvingssoorten%5D%5B35669879%5D=35669879&zoeken%5Bbeschrijvingssoorten%5D%5B35669933%5D=35669933&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=131&zoeken%5Bbeschrijvingssoorten%5D%5B108%5D=108&zoeken%5Bbeschrijvingssoorten%5D%5B35669687%5D=35669687&zoeken%5Bbeschrijvingssoorten%5D%5B35669733%5D=35669733&zoeken%5Bbeschrijvingssoorten%5D%5B35669779%5D=35669779&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=11653789&zoeken%5Bbeschrijvingssoorten%5D%5B11653744%5D=11653744&zoeken%5Bbeschrijvingssoorten%5D%5B11653794%5D=11653794&zoeken%5Bbeschrijvingssoorten%5D%5B11653905%5D=11653905&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=160&zoeken%5Bbeschrijvingssoorten%5D%5B139%5D=139&zoeken%5Bbeschrijvingssoorten%5D%5B35669988%5D=35669988&zoeken%5Bbeschrijvingssoorten%5D%5B35670022%5D=35670022&zoeken%5Bbeschrijvingssoorten%5D%5B35670056%5D=35670056&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=35088449&zoeken%5Bbeschrijvingssoorten%5D%5B35088416%5D=35088416&zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=259&zoeken%5Bbeschrijvingssoorten%5D%5B242%5D=242&zoeken%5Bbeschrijvingssoorten%5D%5B35670104%5D=35670104&zoeken%5Bbeschrijvingssoorten%5D%5B35670153%5D=35670153&zoeken%5Bbeschrijvingssoorten%5D%5B35670194%5D=35670194&zoeken%5Bvelden%5D%5BVrij+zoeken%5D%5Bwaarde%5D=' . $surn . '&zoeken%5Bvelden%5D%5BVrij+zoeken%5D%5Bhighlight%5D=t&zoeken%5Bvelden%5D%5BVrij+zoeken%5D%5Btype%5D=default&zoeken%5Bvelden%5D%5BVrij+zoeken%5D%5Bglobaal%5D=true&btn-submit=Zoeken';
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
