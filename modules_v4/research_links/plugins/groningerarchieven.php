<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class groningerarchieven_plugin extends research_base_plugin {
	static function getName() {
		return 'Groninger Archieven RHC';
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
		$base_url = 'https://www.groningerarchieven.nl/';

		$collection = array(
		"Boedelbeschrijving"     => "zoeken/mais/archief/?mivast=5&miadt=5&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=29",
		"Doopakte"               => "zoeken/mais/archief/?mivast=5&miadt=5&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=106",
		"Functionaris"           => "zoeken/mais/archief/?mivast=5&miadt=5&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=73",
		"Gedetineerde"           => "zoeken/mais/archief/?mivast=5&miadt=5&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=161",
		"Vonnis"                 => "zoeken/mais/archief/?mivast=5&miadt=5&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=216",
		"Resoluties"             => "zoeken/mais/archief/?q=$givn+$surn&mivast=5&mizig=154&miadt=5&milang=nl&mizk_alle=$givn%20$surn&miview=lst",
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
