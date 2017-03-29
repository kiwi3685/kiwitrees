<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class cartago_plugin extends research_base_plugin {
	static function getName() {
		return 'Cartago';
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
		$base_url = 'http://www.cartago.nl/';

		$collection = array(
		    "Achternaam"               => "nl/zoeken/index.php?option=com_cartago&from=zoekscherm&veld=arn&waarde=$surn&action=Zoeken",
		    "Voornaam"                 => "nl/zoeken/index.php?option=com_cartago&from=zoekscherm&veld=vrn&waarde=$givn&action=Zoeken",
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
