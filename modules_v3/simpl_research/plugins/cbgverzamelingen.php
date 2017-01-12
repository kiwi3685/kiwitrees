<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class cbgverzamelingen_plugin extends research_base_plugin {
	static function getName() {
		return 'CBG Verzamelingen';
	}

	static function getPaySymbol() {
		return true;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = '#';
	}
	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		$base_url = 'http://cbgverzamelingen.nl/';

		$collection = array(
		"Algemeen politieblad"			=> "zoeken?search=" . $surname . "&collection=Algemeen+politieblad",
		"Bidprentjes"			        => "zoeken?search=" . $surname . "&collection=Bidprentjes",
		"Familieadvertenties"			=> "zoeken?search=" . $surname . "&collection=Familieadvertenties",
		"Familiearchieven"			    => "zoeken?search=" . $surname . "&collection=Familiearchieven",
		"Familiedossiers"			    => "zoeken?search=" . $surname . "&collection=Familiedossiers",
		"Familiedrukwerk"			    => "zoeken?search=" . $surname . "&collection=Familiedrukwerk",
		"Foto's"			            => "zoeken?search=" . $surname . "&collection=Foto%27s",
		"Handschriften"			        => "zoeken?search=" . $surname . "&collection=Handschriften",
		"Oorlogsbronnen"			    => "zoeken?search=" . $surname . "&collection=Oorlogsbronnen",
		"Oost-Indische bronnen"			=> "zoeken?search=" . $surname . "&collection=Oost-Indische+bronnen",
			);
		foreach($collection as $x => $x_value) {
				$link[] = array(
					'title' => WT_I18N::translate($x),
					'link'  => $base_url. $x_value
				);
			}
			return $link;
	}

	static function createLinkOnly() {
		return false;
	}

	static function encode_plus() {
		return false;
	}
}
