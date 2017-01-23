<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class leeuwardenhc_plugin extends research_base_plugin {
	static function getName() {
		return 'Leeuwarden HC';
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
		$base_url = 'https://historischcentrumleeuwarden.nl/';

		$collection = array(
			"BS-Bevolkingsregister 1811-1939"	=> "genealogie/q/persoon_voornaam_t_0/" . $givn . "/q/persoon_achternaam_t_0/" . $surname . "",
			"Overige Databases"					=> "external-sources?searchterm=" . $givn . "+" . $surname . "",
			"Archieven"							=> "onderzoek/archievenoverzicht?mivast=76&miadt=76&mizig=0&miview=ldt&milang=nl&micols=1&mires=0&mizk_alle=" . $givn . "+" . $surname . "",
			"Beeldbank"							=> "onderzoek/beeldmateriaal/beeldbank?q_searchfield=" . $givn . "+" . $surname . "",
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
