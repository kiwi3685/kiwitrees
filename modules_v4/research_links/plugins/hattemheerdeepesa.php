<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class hattemheerdeepesa_plugin extends research_base_plugin {
	static function getName() {
		return 'Hattem-Heerde-Epe SA';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return false;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		$base_url = 'https://grasduinen.streekarchiefepe.nl/';

		$collection = array(
		    "Personen"               => "zoeken.php?zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=285941&zoeken%5Bbeschrijvingssoorten%5D%5B691%5D=691&zoeken%5Bbeschrijvingssoorten%5D%5B285804%5D=285804&zoeken%5Bbeschrijvingssoorten%5D%5B285856%5D=285856&zoeken%5Bvelden%5D%5BVoornaam%5D%5Bwaarde%5D=$givn&zoeken%5Bvelden%5D%5BVoornaam%5D%5Bhighlight%5D=t&zoeken%5Bvelden%5D%5BVoornaam%5D%5Btype%5D=default&zoeken%5Bvelden%5D%5BVoornaam%5D%5Bglobaal%5D=false&zoeken%5Bvelden%5D%5BAchternaam%5D%5Bwaarde%5D=$surn&zoeken%5Bvelden%5D%5BAchternaam%5D%5Bhighlight%5D=t&zoeken%5Bvelden%5D%5BAchternaam%5D%5Btype%5D=default&zoeken%5Bvelden%5D%5BAchternaam%5D%5Bglobaal%5D=false&zoeken%5Bvelden%5D%5BRol%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BRol%5D%5BHeeftKeuzeLijst%5D=t&zoeken%5Bvelden%5D%5BRol%5D%5Bhighlight%5D=t&zoeken%5Bvelden%5D%5BRol%5D%5Btype%5D=default&zoeken%5Bvelden%5D%5BRol%5D%5Bglobaal%5D=false&zoeken%5Bvelden%5D%5BGeslacht%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BGeslacht%5D%5BHeeftKeuzeLijst%5D=t&zoeken%5Bvelden%5D%5BGeslacht%5D%5Bhighlight%5D=t&zoeken%5Bvelden%5D%5BGeslacht%5D%5Btype%5D=default&zoeken%5Bvelden%5D%5BGeslacht%5D%5Bglobaal%5D=false&zoeken%5Bvelden%5D%5BVrij+zoeken%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BVrij+zoeken%5D%5Bhighlight%5D=t&zoeken%5Bvelden%5D%5BVrij+zoeken%5D%5Btype%5D=default&zoeken%5Bvelden%5D%5BVrij+zoeken%5D%5Bglobaal%5D=true&zoeken%5Btransformeren%5D=&searchtype=new&btn-submit=Zoeken&unique-token=cc762ac440badde0c88e3d1e64dfef049d8c2a69cbe8add9ac723abf7cf727b1
",
		    "Kranten"               => "zoeken.php?zoeken%5BGlobalSearchTemplate%5D=true&zoeken%5Bbeschrijvingsgroepen%5D%5B1242229%5D=1242229&zoeken%5Bbeschrijvingssoorten%5D%5B1242240%5D=1242240&zoeken%5Bvelden%5D%5BVrij+zoeken%5D%5Bwaarde%5D=$surname&zoeken%5Bvelden%5D%5BVrij+zoeken%5D%5Bhighlight%5D=t&zoeken%5Bvelden%5D%5BVrij+zoeken%5D%5Btype%5D=default&zoeken%5Bvelden%5D%5BVrij+zoeken%5D%5Bglobaal%5D=true&btn-submit=Zoeken&breadcrumbs%5Boorsprong%5D=searchtemplate&breadcrumbs%5Bsearchtemplate%5D=global",
		);

		foreach($collection as $key => $value) {
			$link[] = array(
				'title' => KT_I18N::translate($key),
				'link'  => $base_url . $value
			);
		}

		return $link;
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
