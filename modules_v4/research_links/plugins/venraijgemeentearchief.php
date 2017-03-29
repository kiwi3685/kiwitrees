<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class venraijgemeentearchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Venraij Gemeentearchief';
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
		$base_url = 'http://gemeentearchiefvenray.nl/';

		$collection = array(
		"Geboorteakten"				=> "genealogie/zoeken-door-personen/trefwoord/akte_type/geboorteakte/q/persoon_achternaam_t_0/' . $surn . '/q/persoon_voornaam_t_0/' . $givn .'/q/zoekwijze/s",
		"Huwelijksakten"			=> "/genealogie/zoeken-door-personen/trefwoord/akte_type/huwelijksakte/q/persoon_achternaam_t_0/' . $surn . '/q/persoon_voornaam_t_0/' . $givn .'/q/zoekwijze/s",
		"Overlijdensakten"			=> "genealogie/zoeken-door-personen/trefwoord/akte_type/overlijdensakte/q/persoon_achternaam_t_0/' . $surn . '/q/persoon_voornaam_t_0/' . $givn .'/q/zoekwijze/s",
		"Dopen voor 1798"			=> "genealogie/zoeken-door-personen/trefwoord/akte_type/doopakte/q/persoon_achternaam_t_0/' . $surn . '/q/persoon_voornaam_t_0/' . $givn .'/q/zoekwijze/s",
		"Trouwen voor 1798"			=> "genealogie/zoeken-door-personen/trefwoord/akte_type/trouwakte/q/persoon_achternaam_t_0/' . $surn . '/q/persoon_voornaam_t_0/' . $givn .'/q/zoekwijze/s",
		"Begraven voor 1798"		=> "genealogie/zoeken-door-personen/trefwoord/akte_type/begraafakte/q/persoon_achternaam_t_0/' . $surn . '/q/persoon_voornaam_t_0/' . $givn .'/q/zoekwijze/s",
		"Bevolkingsregistraties"	=> "genealogie/zoeken-door-personen/trefwoord/akte_type/inschrijving%20register/q/persoon_achternaam_t_0/' . $surn . '/q/persoon_voornaam_t_0/' . $givn .'/q/zoekwijze/s",
		"Notariele Akten"			=> "genealogie/zoeken-door-personen/trefwoord/akte_type/akte/q/persoon_achternaam_t_0/' . $surn . '/q/persoon_voornaam_t_0/' . $givn .'/q/zoekwijze/s",

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
