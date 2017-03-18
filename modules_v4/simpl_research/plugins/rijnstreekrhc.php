<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class rijnstreekrhc_plugin extends research_base_plugin {
	static function getName() {
		return 'Rijnstreek en Lopikerwaard';
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
		$base_url = 'http://rhcrijnstreek.nl/';

		$collection = array(
		"Burgelijke stand"				=> "bronnen/indexen/personen-zoeken/trefwoord/register_custom_s_type_archief/Burgerlijke%20stand/q/persoon_custom_t_naam/$givn%20$surn",
		"Kerkelijk archief"				=> "bronnen/indexen/personen-zoeken/trefwoord/register_custom_s_type_archief/Kerkelijk%20archief/q/persoon_custom_t_naam/$givn%20$surn",
		"Notarieel archief"				=> "bronnen/indexen/personen-zoeken/trefwoord/register_custom_s_type_archief/Notarieel%20archief/q/persoon_custom_t_naam/$givn%20$surn",
		"Oud rechterlijk en weeskamer"	=> "bronnen/indexen/personen-zoeken/trefwoord/register_custom_s_type_archief/Oud%28~%29rechterlijk%20en%20weeskamer/q/persoon_custom_t_naam/$givn%20$surn",
		"Plaatselijk bestuur"			=> "bronnen/indexen/personen-zoeken/sjabloon/index/facet_xml/personen/objecttype/personen/trefwoord/register_custom_s_type_archief/Plaatselijk%20bestuur/q/persoon_custom_t_naam/$givn%20$surn/start/0",
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
