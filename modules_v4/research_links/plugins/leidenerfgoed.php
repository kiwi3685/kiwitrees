<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class leidenerfgoed_plugin extends research_base_plugin {
	static function getName() {
		return 'Leiden en omstreken Erfgoed';
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
		$base_url = 'http://www.erfgoedleiden.nl/';

		$collection = array(
			"BS Geboorte"			=> "collecties/personen/zoek-op-personen/sjabloon/index/facet_xml/personen/objecttype/personen/trefwoord/akte_type/Burgerlijke%20stand%20geboren/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/start/0",
			"BS Huwelijk"			=> "collecties/personen/zoek-op-personen/sjabloon/index/facet_xml/personen/objecttype/personen/trefwoord/akte_type/Burgerlijke%20stand%20huwelijk/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/start/0",
			"BS Overlijden"			=> "collecties/personen/zoek-op-personen/sjabloon/index/facet_xml/personen/objecttype/personen/trefwoord/akte_type/Burgerlijke%20stand%20overlijden/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/start/0",
			"Bevolkingsregister"	=> "collecties/personen/zoek-op-personen/sjabloon/index/facet_xml/personen/objecttype/personen/trefwoord/akte_type/Bevolkingsregister/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/start/0",
			"Dopen"					=> "collecties/personen/zoek-op-personen/sjabloon/index/facet_xml/personen/objecttype/personen/trefwoord/akte_type/Dopen/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/start/0",
			"Trouwen"				=> "collecties/personen/zoek-op-personen/sjabloon/index/facet_xml/personen/objecttype/personen/trefwoord/akte_type/Trouwen/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/start/0",
			"Begraven"				=> "collecties/personen/zoek-op-personen/sjabloon/index/facet_xml/personen/objecttype/personen/trefwoord/akte_type/Begraven/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/start/0",
			"Bonboeken"				=> "collecties/personen/zoek-op-personen/sjabloon/index/facet_xml/personen/objecttype/personen/trefwoord/akte_type/Bonboeken/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/start/0",
			"Echtscheiding"			=> "collecties/personen/zoek-op-personen/sjabloon/index/facet_xml/personen/objecttype/personen/trefwoord/akte_type/echtscheidingsakte/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/start/0",
		);

		foreach($collection as $key => $value) {
			$link[] = array(
				'title' => WT_I18N::translate($key),
				'link'	=> $base_url . $value
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
