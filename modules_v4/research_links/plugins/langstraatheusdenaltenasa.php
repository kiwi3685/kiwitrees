<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class langstraatheusdenaltenasa_plugin extends research_base_plugin {
	static function getName() {
		return 'Langstraat Heusden Altena SA';
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
		$base_url = 'ttp://salha.nl/';

		$collection = array(
			"Genealogie"				=> "archieven-en-collecties/voorouders/genealogie/q/persoon_achternaam_t_0/$surn/q/persoon_voornaam_t_0/$givn",
			"Notaris-en schepenakten"	=> "archieven-en-collecties/voorouders/notaris-en-schepenakten?mivast=128&miadt=128&mizig=197&miview=tbl&milang=nl&micols=1&mires=0&mip3=$surn&mip2=$givn",
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
