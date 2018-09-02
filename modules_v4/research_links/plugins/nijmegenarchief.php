<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class nijmegenarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Nijmegen Regionaal Archief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return "https://studiezaal.nijmegen.nl/zoeken/groep=Personen%20en%20locaties/Persoon=$fullname/pagina=1/aantalpp=20/f_filterCollectie=Personen%20en%20locaties/?nav_id=4-0";
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return false;
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
