<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class noord_hollands_plugin extends research_base_plugin {
	static function getName() {
		return 'Noord-Hollands archief';
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
		$base_url = 'http://noord-hollandsarchief.nl/';

		$collection = array(
		"Faillissementsdossier"    => "personen/databases?mivast=236&miadt=236&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=297",
		"Gedetineerde"             => "personen/databases?mivast=236&miadt=236&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=161",
		"Notariele akte"           => "personen/databases?mivast=236&miadt=236&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=224",
		"Persoon"                  => "personen/databases?mivast=236&miadt=236&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=108",
		"Persoon in vonnis"        => "personen/databases?mivast=236&miadt=236&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=88",
		"Persoon voor krijgsraad"  => "personen/databases?mivast=236&miadt=236&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=274",
		"Studentdossier"           => "personen/databases?mivast=236&miadt=236&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=222",
		"Vredegerecht"             => "personen/databases?mivast=236&miadt=236&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=55",
		"Beeldbank"                => "beelden/beeldbank/?mode=gallery&view=horizontal&q=$surn&page=1&reverse=0",
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
