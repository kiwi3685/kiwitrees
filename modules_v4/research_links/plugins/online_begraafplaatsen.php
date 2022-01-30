<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class online_begraafplaatsen_plugin extends research_base_plugin {
	static function getName() {
		return 'Online Begraafplaatsen';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return false;
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		// This is a post form, so it will be sent with Javascript
		$url	 = 'https://www.online-begraafplaatsen.nl/zoeken.asp?command=zoekform';
		$params	 = array(
			'achternaam' => $surn,
			'voornaam'	 => $first . ''
		);
		return "postresearchform('" . $url . "'," . json_encode($params) . ")";
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
