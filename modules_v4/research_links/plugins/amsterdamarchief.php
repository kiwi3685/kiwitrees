<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class amsterdamarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Amsterdam Archief';
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
		$base_url = 'https://archief.amsterdam/';

		$collection = array(
				"Archiefkaarten"			        => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Archiefkaarten%22%5D%7D",
				"Averijgrossen"			            => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Averijgrossen%22%5D%7D",
				"Bevolkingsregisters 1851-1853"		=> "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Bevolkingsregister%201851-1853%22%5D%7D",
				"Bevolkingsregisters 1853-1863"		=> "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Bevolkingsregister%201853-1863%22%5D%7D",
				"Bevolkingsregisters 1864-1874"		=> "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Bevolkingsregister%201864-1874%22%5D%7D",
				"Bevolkingsregisters 1874-1893"		=> "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Bevolkingsregister%201874-1893%22%5D%7D",
				"BR Tijdelijk Verblijf"		        => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Bevolkingsregister%20tijdelijk%22%5D%7D",
				"BR Geannexeerde gemeenten"			=> "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Bevolkingsregisters%20geannexeerde%20gemeenten%22%5D%7D",
				"Bijzondere registers"			    => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Bijzondere%20registers%22%5D%7D",
				"Boedelpapieren"			        => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Boedelpapieren%22%5D%7D",
				"Boetes op begraven"	            => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Boetes%20op%20begraven%22%5D%7D",
				"Comportementboeken"		        => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Comportementsboeken%22%5D%7D",
				"Confessieboeken"			        => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Confessieboeken%22%5D%7D",
				"DTB Begraven"			            => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22DTB%20Begraven%22%5D%7D",
				"DTB Dopen"			                => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22DTB%20Dopen%22%5D%7D",
				"Gezinskaarten"			            => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Gezinskaarten%22%5D%7D",
				"Huiszittenhuizen"		            => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Huiszittenhuizen%22%5D%7D",
				"Kwijtscheldingen"		            => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Kwijtscheldingen%22%5D%7D",
				"Lidmatenregister Doopsgezinden "	=> "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Lidmatenregister%20Doopsgezinde%20Gemeente%22%5D%7D",
				"Marktkaarten"			            => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Marktkaarten%22%5D%7D",
				"Militieregisters"		            => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Militieregisters%22%5D%7D",
				"Notariële archieven"		        => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Notariële%20archieven%22%5D%7D",
				"Ondertrouwregister"		        => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Ondertrouwregister%22%5D%7D",
				"Overgenomen delen"		            => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Overgenomen%20delen%22%5D%7D",
				"Overledenen Gasthuis"	            => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Overledenen%20Gast-,%20Pest-,%20Werk-%20en%20Spinhuis%22%5D%7D",
				"Paspoortaanvragen '40-'45"			=> "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Paspoortaanvragen%20%2740-%2745%22%5D%7D",
				"Patientenregisters"		        => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Patiëntenregisters%22%5D%7D",
				"Pensioenkaarten"			        => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Pensioenkaarten%22%5D%7D",
				"Persoonskaarten"			        => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Persoonskaarten%22%5D%7D",
				"Politierapporten '40-'45"		    => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Politierapporten%20%2740-%2745%22%5D%7D",
				"Poorterboeken"				        => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Poorterboeken%22%5D%7D",
				"Tewerkgestelden '40-'45"			=> "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Tewerkgestelden%20%2740-%2745%22%5D%7D",
				"Veroordeelden"	                    => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Veroordeelden%22%5D%7D",
				"Vreemdelingenregister"	            => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Vreemdelingenregister%22%5D%7D",
				"Waterloo Gratificaties 1815"	    => "indexen/persons?sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surname%22,%22search_t_voornaam%22:%22$givn%22%7D,%22search_s_register_type_title%22:%5B%22Waterloo%20gratificaties%201815%22%5D%7D",
				"Woningkaarten"			            => "indexen/persons?sa=%7B%22search_s_register_type_title%22:%5B%22Woningkaarten%22%5D%7D",
				
			);
		foreach($collection as $x => $x_value) {
				$link[] = array(
					'title' => KT_I18N::translate($x),
					'link'  => $base_url. $x_value
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
