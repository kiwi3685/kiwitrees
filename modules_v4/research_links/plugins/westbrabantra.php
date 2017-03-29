<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class westbrabantra_plugin extends research_base_plugin {
	static function getName() {
		return 'West Brabant Regionaal Archief';
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
		$base_url = 'http://westbrabantsarchief.nl/';

		$collection = array(
		    
		    "Archieven"                 => "collectie/archieven/search/context/keywords/%5E$givn%20$surn",
			"Beeldbank"                 => "collectie/beeldbank/?mode=gallery&view=horizontal&q=$givn%20$surn&page=1&reverse=0",
			"Notariele bronnen"         => "collectie/bladeren-in-bronnen/registers?ss=%7B%22q%22:%22$givn%20$surn%22%7D&sa=%7B%22search_s_type_title%22:%5B%22notari%C3%ABle%20archieven%22%5D%7D",
			"Voorouders"                => "collectie/voorouders/persons?ss=%7B%22q%22:%22%5E$givn%20$surn%22%7D",	    
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
