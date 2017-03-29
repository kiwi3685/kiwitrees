<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class drentsarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Drents archief';
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
		$base_url = 'http://www.drentsarchief.nl/';

		$collection = array(
		    "Archiefstukken"             => "onderzoeken/archiefstukken?mivast=34&miadt=34&mizig=0&miview=ldt&milang=nl&micols=1&mires=0&mizk_alle=$givn%20$surn",
		    "Beeldbank"                  => "onderzoeken/beeldbank-home/zoekresultaat/indeling/gallery/form/advanced?q_searchfield=jansen",
		    "Film & geluid"              => "onderzoeken/film-en-geluid?mivast=34&miadt=34&mizig=47&miview=ldt&milang=nl&micols=1&mires=0&mizk_alle=$givn+$surn",
		    "Emigranten"                 => "onderzoeken/emigranten?name=$givn+$surn",
		    "Koloniehuizen"              => "onderzoeken/koloniehuizen?q_searchfield=$givn+$surn&objecttype=person&rm=text",
		    "Kentekens"                  => "onderzoeken/kentekens/indeling/text?q_kenteken_searchfield=$givn+$surn",
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
