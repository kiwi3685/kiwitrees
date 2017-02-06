<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class alkmaararchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Alkmaar Regionaal Archief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		return false;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		$base_url = 'https://www.regionaalarchiefalkmaar.nl/';

		$collection = array(
			"Bevolkingsregister"=> "collecties/genealogie/aktes/trefwoord/register_type/bevolkingsregister/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/q/zoekwijze/s",
        "Burgelijke stand"      => "collecties/genealogie/aktes/trefwoord/register_type/burgerlijke%20standregister/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/q/zoekwijze/s",
        "DTB"                   => "collecties/genealogie/aktes/trefwoord/register_type/doop%28~%29%2C%20trouw%28~%29%20en%20begraafregister/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/q/zoekwijze/s",
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
