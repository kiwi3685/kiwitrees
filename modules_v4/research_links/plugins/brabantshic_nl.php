<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class brabantshic_nl_plugin extends research_base_plugin {
	static function getName() {
		return 'Brabant BHIC';
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
		$base_url = 'https://www.bhic.nl/';

		$collection = array(
		"DTB"                   => "memorix/genealogy/search?serviceUrl=%2Fgenealogie%2Fq%2Fpersoon_achternaam_t_0%2F$surn%2Fq%2Fpersoon_voornaam_t_0%2F$givn%2Fq%2Fzoekwijze%2Fs%2Fq%2Fakte_type_short%2Fdtb_d%3Fapikey%3D0ea60280-55ad-11e2-bcfd-0800200c9a66%26template%3Dxhtml%26form%3Dpersonen%26zoeken%3DStart%2Bzoeken",
        "Burgelijke stand"      => "memorix/genealogy/search?serviceUrl=%2Fgenealogie%2Ftrefwoord%2Fregister_type%2Fburgerlijke%2520stand%2Fq%2Ftext%2F$givn%2520$surn%3Fapikey%3D0ea60280-55ad-11e2-bcfd-0800200c9a66",
        "Bevolkingsregister"    => "memorix/genealogy/search?serviceUrl=%2Fgenealogie%2Ftrefwoord%2Fregister_type%2Fbevolkingsregister%2Fq%2Ftext%2F$givn%2520$surn%3Fapikey%3D0ea60280-55ad-11e2-bcfd-0800200c9a66",
        "Memorie van successie" => "memorix/genealogy/search?serviceUrl=%2Fgenealogie%2Ftrefwoord%2Fregister_type%2Fmemories%2520van%2520successie%2Fq%2Ftext%2F$givn%2520$surn%3Fapikey%3D0ea60280-55ad-11e2-bcfd-0800200c9a66",
        "Gevangenisregister"    => "memorix/genealogy/search?serviceUrl=%2Fgenealogie%2Ftrefwoord%2Fregister_type%2Fgevangenisregisters%2Fq%2Ftext%2F$givn%2520$surn%3Fapikey%3D0ea60280-55ad-11e2-bcfd-0800200c9a66",
        "Militieregister"       => "memorix/genealogy/search?serviceUrl=%2Fgenealogie%2Ftrefwoord%2Fregister_type%2Fmilitieregisters%2Fq%2Ftext%2F$givn%2520$surn%3Fapikey%3D0ea60280-55ad-11e2-bcfd-0800200c9a66",
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
