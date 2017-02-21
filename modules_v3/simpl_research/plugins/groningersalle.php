<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class groningersalle_plugin extends research_base_plugin {
	static function getName() {
		return 'Groningen Alle Groningers';
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
		$base_url = 'http://www.allegroningers.nl/';

		$collection = array(
		"Huwelijkscontract"       => "personen/trefwoord/akte_type/Huwelijkscontract/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/q/persoon_rol_s_0/0/q/persoon_rol_s_1/0/start/0",
		"Lidmaten"                => "personen/trefwoord/akte_type/Lidmaten/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/q/persoon_rol_s_0/0/q/persoon_rol_s_1/0/start/0",
		"Begraafakte"             => "personen/trefwoord/akte_type/begraafakte/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/q/persoon_rol_s_0/0/q/persoon_rol_s_1/0/start/0",
		"Besnijdenisakte"         => "personen/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/q/persoon_rol_s_0/0/q/persoon_rol_s_1/0",
		"Doopakte"                => "personen/trefwoord/akte_type/doopakte/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/q/persoon_rol_s_0/0/q/persoon_rol_s_1/0",
		"Emigratie"               => "personen/trefwoord/akte_type/emigratie/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/q/persoon_rol_s_0/0/q/persoon_rol_s_1/0/start/0",
		"Geboorteakte"            => "personen/trefwoord/akte_type/geboorteakte/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/q/persoon_rol_s_0/0/q/persoon_rol_s_1/0/start/0",
		"Huwelijksakte"           => "personen/trefwoord/akte_type/huwelijksakte/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/q/persoon_rol_s_0/0/q/persoon_rol_s_1/0/start/0",
		"Overlijdensakte"         => "personen/trefwoord/akte_type/overlijdensakte/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/q/persoon_rol_s_0/0/q/persoon_rol_s_1/0",
		"Successie"               => "personen/trefwoord/akte_type/successie/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/q/persoon_rol_s_0/0/q/persoon_rol_s_1/0/start/0",
		"Trouwakte (tot 1811)"    => "personen/trefwoord/akte_type/trouwakte%20%28tot%201811%29/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/q/persoon_rol_s_0/0/q/persoon_rol_s_1/0",
		"Bevolkingsregister"      => "personen/trefwoord/akte_type/vermelding%20in%20bevolkingsregister/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn/q/persoon_rol_s_0/0/q/persoon_rol_s_1/0/start/0",
		    
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
