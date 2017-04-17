<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class leidenerfgoed_plugin extends research_base_plugin {
	static function getName() {
		return 'Leiden en omstreken Erfgoed';
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
		$base_url = 'https://www.erfgoedleiden.nl/';

		$collection = array(
			"BS Geboorte"			  => "collecties/personen/zoek-op-personen/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22BS%20Geboorte%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
			"BS Huwelijk"			  => "collecties/personen/zoek-op-personen/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22BS%20Huwelijk%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
			"BS Overlijden"			  => "collecties/personen/zoek-op-personen/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22BS%20Overlijden%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
			"Bevolkingsregister"	  => "collecties/personen/zoek-op-personen/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22Bevolkingsregister%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
			"Dopen"					  => "collecties/personen/zoek-op-personen/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22DTB%20Dopen%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
			"Trouwen"				  => "collecties/personen/zoek-op-personen/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22DTB%20Trouwen%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
			"Begraven"				  => "collecties/personen/zoek-op-personen/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22DTB%20Begraven%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
			"Bonboeken"				  => "collecties/personen/zoek-op-personen/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22Bonboeken%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D0",
			"Echtscheiding"			  => "collecties/personen/zoek-op-personen/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22BS%20Echtscheidingsakte%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
			"Index Notarieel Archief" => "collecties/personen/zoek-op-personen/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22Index%20Notarieel%20Archief%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
			"Militieregisters"		  => "collecties/personen/zoek-op-personen/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22Militieregisters%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
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
