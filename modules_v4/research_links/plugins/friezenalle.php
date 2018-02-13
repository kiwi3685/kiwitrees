<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class friezenalle_plugin extends research_base_plugin {
	static function getName() {
		return 'Friesland Alle Friezen';
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
		$base_url = 'https://www.allefriezen.nl/';

		$collection = array(
			"BS Geboorte"				 => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22BS%20Geboorte%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"BS Huwelijk"				 => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22BS%20Huwelijk%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"BS Overlijden"				 => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22BS%20Overlijden%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"Bevolkingsregister"		 => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22Bevolkingsregister%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"Burgerboeken"				 => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22Burgerboeken%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"DTB Begraven"				 => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22DTB%20Begraven%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"DTB Dopen"			  		 => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22DTB%20Dopen%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"DTB Lidmaten"			     => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22DTB%20Lidmaten%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"DTB Trouwen"			     => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22DTB%20Trouwen%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"Emigranten"			     => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22Emigranten%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"Inscripties/grafschriften"  => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22Inscripties%20en%20grafschriften%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"Kadaster 1832"              => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22Kadaster%201832%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"Kamer van koophandel"       => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22Kamer%20van%20koophandel%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"Memories van successie"     => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22Memories%20van%20successie%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"Militairen 1795-1815"       => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22Militairen%201795-1815%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"Naamsaanneming 1811"        => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22Naamsaanneming%201811%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"Nedergerechten"             => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22Nedergerechten%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"Notarieel archief"          => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22Notarieel%20archief%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"Onderduikers registers"     => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22Onderduikers%20registers%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"Ontvanger Generaal"         => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22Ontvanger%20Generaal%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"Pottenbakkers 1752-1900"    => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22Pottenbakkers%201752-1900%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"Quaclappen"                 => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22Quaclappen%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"Quotisatie kohieren"      	 => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22Quotisatie%20kohieren%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"Registres civiques"      	 => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22Registres%20civiques%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"Rolboeken arr. rechtbanken" => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22Rolboeken%20arr.%20rechtbanken%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"Stemkohieren"               => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22Stemkohieren%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"Studenten Franeker"         => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22Studenten%20Franeker%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"Tietjerksteradeel"          => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22Tietjerksteradeel%20e.o.%22,%22Tietjerksteradeel%20hypotheekboeken%22,%22Tietjerksteradeel%20informatieboeken%22,%22Tietjerksteradeel%20speciekohieren%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
			"Volkstelling 1744"          => "zoeken/persons?sa=%7B%22person_1%22:%7B%22search_t_voornaam%22:%22$givn%22,%22search_t_geslachtsnaam%22:%22$surname%22%7D,%22search_s_register_type_title%22:%5B%22Volkstelling%201744%22%5D%7D&sort=%7B%22order_s_deed_type_title%22:%22desc%22%22%7D",
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
