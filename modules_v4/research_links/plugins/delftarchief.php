<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class delftarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Delft Archief';
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
		$base_url = 'http://collectie-delft.nl/';

		$collection = array(
		"Burgelijke stand"   => "nadere-toegangen/trefwoord/persoon_custom_s_facet_bron/Burgerlijke%20standregister/q/persoon_voornaam_t_0/$givn/q/persoon_achternaam_t_0/$surn",
	    "DTB register"       => "nadere-toegangen/trefwoord/persoon_custom_s_facet_bron/Doop%2C%20Trouw%20en%20Begraaf%20Registers/q/persoon_voornaam_t_0/$givn/q/persoon_achternaam_t_0/$surn",
	    "Bevolkingsregister" => "nadere-toegangen/trefwoord/persoon_custom_s_facet_bron/Bevolkingsregister/q/persoon_voornaam_t_0/$givn/q/persoon_achternaam_t_0/$surn",
	    "Onroerend goed  "   => "nadere-toegangen/trefwoord/persoon_custom_s_facet_bron/onroerend%20goed/q/persoon_voornaam_t_0/$givn/q/persoon_achternaam_t_0/$surn",
	    "Bevolking  "        => "nadere-toegangen/trefwoord/persoon_custom_s_facet_bron/bevolking/q/persoon_voornaam_t_0/$givn/q/persoon_achternaam_t_0/$surn",
	    "Vergunningen  "     => "nadere-toegangen/trefwoord/persoon_custom_s_facet_bron/vergunningen/q/persoon_voornaam_t_0/$givn/q/persoon_achternaam_t_0/$surn",
	    "Zorg personen  "    => "nadere-toegangen/sjabloon/index/facet_xml/personen/objecttype/personen/trefwoord/persoon_custom_s_facet_bron/zorg_personen/q/persoon_voornaam_t_0/$givn/q/persoon_achternaam_t_0/$surn/start/0",
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
