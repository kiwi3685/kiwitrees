<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class enschedestadsarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Enschede Erfgoed';
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
		$base_url = 'https://collecties.erfgoedenschede.nl';

		$collection = array(

		    "Bevolkingsregister"      => "/zoeken/groep=Personen%2C%20Akten%20en%20registers/Achternaam=$surname/Voornaam=$givn/Documenttype=Bevolkingsregister/aantalpp=12/?nav_id=0-0",
		    "DTB Dopen"               => "/zoeken/groep=Personen%2C%20Akten%20en%20registers/Achternaam=$surname/Voornaam=$givn/Documenttype=DTB-Dopen/aantalpp=12/?nav_id=2-0",
		    "Geboortenakten"          => "/zoeken/groep=Personen%2C%20Akten%20en%20registers/Achternaam=$surname/Voornaam=$givn/Documenttype=Geboorteakten/aantalpp=12/?nav_id=1-0",
		    "Huwelijksakten"          => "/zoeken/groep=Personen%2C%20Akten%20en%20registers/Achternaam=$surname/Voornaam=$givn/Documenttype=Huwelijksakten/aantalpp=12/?nav_id=3-0",
		    "Militieregister"         => "/zoeken/groep=Personen%2C%20Akten%20en%20registers/Achternaam=$surname/Voornaam=$givn/Documenttype=Militieregisters/aantalpp=12/?nav_id=4-0",
		    "Notarieel Archief"       => "/zoeken/groep=Personen%2C%20Akten%20en%20registers/Achternaam=$surname/Voornaam=$givn/Documenttype=Notarieel%20Archief/aantalpp=12/?nav_id=5-0",
		    "Oud Rechterlijk archief" => "/zoeken/groep=Personen%2C%20Akten%20en%20registers/Achternaam=$surname/Voornaam=$givn/Documenttype=Oud%20Rechterlijk%20Archief/aantalpp=12/?nav_id=6-0n",
		    "Overlijdensakten"        => "zoeken/groep=Personen%2C%20Akten%20en%20registers/Achternaam=$surname/Voornaam=$givn/Documenttype=Overlijdensakten/aantalpp=12/?nav_id=7-0",
            "Overlijdensverklaringen" => "zoeken/groep=Personen%2C%20Akten%20en%20registers/Achternaam=$surname/Voornaam=$givn/Documenttype=Overlijdensverklaringen/aantalpp=12/?nav_id=8-0",
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
