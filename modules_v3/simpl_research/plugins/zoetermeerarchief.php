<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class zoetermeerarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Zoetermeer Oud Soetermeer';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		return false;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		$base_url = 'http://www.allezoetermeerders.nl/';

		$collection = array(
			"Burgelijke stand"          => "stamboom/2/zoeken.html?year_field=document&last_name=$surn&middle_name=&first_name=$givn&start_year=&end_year=&document_type=&role=",
			"Notariele akten"           => "stamboom/29/zoeken_in_notariele_akten.html?first_name=$givn&middle_name=&last_name=$surn&akte=&year_from=&year_until=&search=",
			"Belastingregisters"        => "stamboom/17?search=$surn&year_from=&year_until=&source_type_id=2&city_id=0",
			"Morgenboeken"              => "stamboom/17?search=$surn&year_from=&year_until=&source_type_id=3&city_id=0",
			"DTB"                       => "stamboom/17?search=$surn&year_from=&year_until=&source_type_id=1&city_id=0",
			"Transporten en Hypotheken" => "stamboom/17?search=$surn&year_from=&year_until=&source_type_id=5&city_id=0",
			"Overige voor 1600"         => "stamboom/17?search=$surn&year_from=&year_until=&source_type_id=6&city_id=0",
			"Overige 1600-1695"         => "stamboom/17?search=$surn&year_from=&year_until=&source_type_id=7&city_id=0",
			"Overige 1695-1811"         => "stamboom/17?search=$surn&year_from=&year_until=&source_type_id=8&city_id=0",
			"Overige 1811-1998"         => "stamboom/17?search=$surn&year_from=&year_until=&source_type_id=9&city_id=0",
			"Regio 1406-1949"           => "stamboom/17?search=$surn&year_from=&year_until=&source_type_id=10&city_id=0",
			"Functionarissen"           => "stamboom/17?search=$surn&year_from=&year_until=&source_type_id=11&city_id=0",
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
