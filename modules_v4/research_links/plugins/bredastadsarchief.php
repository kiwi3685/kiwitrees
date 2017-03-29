<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class bredastadsarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Breda Stadsarchief';
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
		$base_url = 'https://stadsarchief.breda.nl/';

		$collection = array(
		"Adresboeken"               => "collecties/adresboeken?url=https%3A%2F%2Fbreda-adresboeken.courant.nu%2Fsearch%3Fquery%3D$givn%2520$surname'%26period%3D",
		"Genealogie"                => "collecties/genealogie/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn",
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
