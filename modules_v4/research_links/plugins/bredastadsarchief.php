<?php

if (!defined('KT_KIWITREES')) {
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
		"Allerhande acten"				=> "collectie/archief/genealogische-bronnen/persons?f=%7B%22search_s_register_type_title%22:%7B%22v%22:%22Allerhande%20acten%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
		"Bevolkingsregister"			=> "collectie/archief/genealogische-bronnen/persons?f=%7B%22search_s_register_type_title%22:%7B%22v%22:%22Bevolkingsregister%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
		"Borgbrieven"					=> "collectie/archief/genealogische-bronnen/persons?f=%7B%22search_s_register_type_title%22:%7B%22v%22:%22Borgbrieven%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
		"Burgelijke Standregister"		=> "collectie/archief/genealogische-bronnen/persons?f=%7B%22search_s_register_type_title%22:%7B%22v%22:%22Burgerlijke%20Standregister%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
		"Comissieboeken"				=> "collectie/archief/genealogische-bronnen/persons?f=%7B%22search_s_register_type_title%22:%7B%22v%22:%22Comissieboeken%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
		"DTB"							=> "collectie/archief/genealogische-bronnen/persons?f=%7B%22search_s_register_type_title%22:%7B%22v%22:%22Doop-,%20Trouw-%20en%20Begraafregisters%20(DTB)%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
		"Notariele archieven"			=> "collectie/archief/genealogische-bronnen/persons?f=%7B%22search_s_register_type_title%22:%7B%22v%22:%22Notari%C3%ABle%20archieven%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
		"Patentregisters"				=> "collectie/archief/genealogische-bronnen/persons?f=%7B%22search_s_register_type_title%22:%7B%22v%22:%22Patentregisters%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
		"Poorterboeken"					=> "collectie/archief/genealogische-bronnen/persons?f=%7B%22search_s_register_type_title%22:%7B%22v%22:%22Poorterboeken%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
		"Registre civique"				=> "collectie/archief/genealogische-bronnen/persons?f=%7B%22search_s_register_type_title%22:%7B%22v%22:%22Registre%20civique%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
		"Staat gebouwde eigendommen"	=> "collectie/archief/genealogische-bronnen/persons?f=%7B%22search_s_register_type_title%22:%7B%22v%22:%22Staat%20gebouwde%20eigendommen%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
		"Staten en inventarissen"		=> "collectie/archief/genealogische-bronnen/persons?f=%7B%22search_s_register_type_title%22:%7B%22v%22:%22Staten%20en%20inventarissen%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
		"Vestbrieven"					=> "collectie/archief/genealogische-bronnen/persons?f=%7B%22search_s_register_type_title%22:%7B%22v%22:%22Vestbrieven%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
		);
		foreach($collection as $key => $value) {
			$link[] = array(
				'title' => KT_I18N::translate($key),
				'link'  => $base_url . $value
			);
		}
https://stadsarchief.breda.nl/collectie/archief/genealogische-bronnen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22jansen%22,%22search_t_voornaam%22:%22jan%22%7D%7D
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
