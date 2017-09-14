<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class deventerstadsarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Deventer Stadsarchief';
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
		$base_url = 'http://www.stadsarchiefdeventer.nl/';

		$collection = array(
			"Bevolkingsregister"   => "zoeken-in-de-collecties/archieven?mivast=45&mizig=100&miadt=45&miq=1&milang=nl&misort=last_mod%7Cdesc&mip1=$surn&mip3=$givn&mif1=112&miview=tbl",
            "Persoonbeschrijving"  => "zoeken-in-de-collecties/archieven?mivast=45&mizig=100&miadt=45&miq=1&milang=nl&misort=last_mod%7Cdesc&mip1=$surn&mip3=$givn&mif1=211&miview=tbl",
            "Kranten"              => "zoeken-in-de-collecties/archieven?mivast=45&miadt=45&mizig=91&miview=ldt&milang=nl&micols=1&mires=0&mizk_alle=$surn&mibj=$birth_year&miej=$death_year",
            "Beeldbank"            => "zoeken-in-de-collecties/archieven?mivast=45&miadt=45&mizig=101&miview=ldt&milang=nl&micols=1&mires=0&mizk_alle=$surn",
            "Archieftoegang"       => "zoeken-in-de-collecties/archieven?mivast=45&miadt=45&mizig=0&miview=lst&milang=nl&micols=1&misort=last_mod%7Cdesc&mires=0&mif3=4&mizk_alle=$surn",
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
