<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class tholengemeentearchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Tholen Gemeentearchief';
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
		$base_url = 'http://www.delpher.nl/';

		$collection = array(
			"Begraafakte"		    => "onze-bronnen/voorouders/trefwoord/akte_type/begraafakte/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/q/zoekwijze/s",
			"Doopakte"		        => "onze-bronnen/voorouders/trefwoord/akte_type/doopakte/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/q/zoekwijze/s",
			"Inschrijving"		    => "onze-bronnen/voorouders/trefwoord/akte_type/inschrijving/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/q/zoekwijze/s",
			"Lidmatenregistratie"	=> "onze-bronnen/voorouders/trefwoord/akte_type/lidmatenregistratie/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/q/zoekwijze/s",
			"Transportakte"		    => "onze-bronnen/voorouders/trefwoord/akte_type/transportakte/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/q/zoekwijze/s",
			"Trouwakte"	            => "onze-bronnen/voorouders/layout/default/sjabloon/index/facet_xml/personen/objecttype/personen/trefwoord/akte_type/trouwakte/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/q/zoekwijze/s/start/0",
			"Weeskamerakte"		    => "onze-bronnen/voorouders/trefwoord/akte_type/weeskamerakte/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/q/zoekwijze/s",
		);

		foreach($collection as $key => $value) {
			$link[] = array(
				'title' => WT_I18N::translate($key),
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
