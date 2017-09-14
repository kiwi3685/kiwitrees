<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class zutphenarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Zutphen Regionaal Archief';
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
		$base_url = 'http://www.regionaalarchiefzutphen.nl/';

		$collection = array(
		"Voorouders"	=> "voorouders/persons?ss=%7B%22q%22:%22$givn%20$surn%22%7D",
		"Archieven"		=> "archieven/search/list/keywords/$givn%20$surn",
		"Foto's"		=> "beeld/?q=$givn%20$surn",
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
