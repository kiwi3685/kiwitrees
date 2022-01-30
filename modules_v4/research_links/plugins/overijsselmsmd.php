<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class overijsselmsmd_plugin extends research_base_plugin {
	static function getName() {
		return 'Overijssel MSMD';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return "https://mijnstadmijndorp.nl/app/zoeken/groep=Beeld%20en%20Geluid/groep=Bibliotheek%20en%20Kranten/groep=Documenten/groep=Museale%20objecten/groep=Nieuws%20en%20evenementen/groep=Thema%27s%20en%20tijdlijnen/groep=Verhalen/groep=Collecties/groep=Woordenboek%20van%20Overijssel/Globaal=%22$givn%20$surname%22/aantalpp=12/?nav_id=2-0";
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
