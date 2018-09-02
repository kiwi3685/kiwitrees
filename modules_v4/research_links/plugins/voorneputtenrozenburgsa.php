<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class voorneputtenrozenburgsa_plugin extends research_base_plugin {
	static function getName() {
		return 'Voorne-Putten SA';
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
		$base_url = 'http://www.streekarchiefvp.nl/';

		$collection = array(
		"Alle personen"            => "zoeken-in-collecties/archieven/?search=jan+jansen&mivast=126&mizig=100&miadt=126&milang=nl&mizk_alle=$fullname&miview=tbl",
		"Bevolkingsregister"       => "zoeken-in-collecties/archieven/?search=jan+jansen&mivast=126&mizig=234&miadt=126&milang=nl&mizk_alle=$fullname&miview=ldt",
		"Notariele akten"          => "zoeken-in-collecties/archieven/?search=jan+jansen&mivast=126&mizig=57&miadt=126&milang=nl&mizk_alle=j$fullname&miview=ldt",		

		    );

		foreach($collection as $key => $value) {
			$link[] = array(
				'title' => KT_I18N::translate($key),
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
