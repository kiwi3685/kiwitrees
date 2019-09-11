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
		$base_url = 'https://historischcentrumoverijssel.nl/';

		$collection = array(
		"Bouwhistorisch object"         => "archieven/?mivast=20&miadt=45&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mires=0&mip1=$surn&mip3=jan&mib1=276",
        "Bronvermelding"                => "archieven/?mivast=20&miadt=45&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mires=0&mip1=$surn&mip3=jan&mib1=77",
		"Doopinschrijving"              => "archieven/?mivast=20&miadt=45&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mires=0&mip1=$surn&mip3=jan&mib1=156",
        "Persoon in akte"               => "archieven/?mivast=20&miadt=45&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mires=0&mip1=$surn&mip3=jan&mib1=102",
        "Persoon in bevolkingsregister" => "archieven/?mivast=20&miadt=45&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mires=0&mip1=$surn&mip3=jan&mib1=112",
        "Schepenakte"                   => "archieven/?mivast=20&miadt=45&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mires=0&mip1=$surn&mip3=jan&mib1=225",
        "Trouwinschrijving"             => "archieven/?mivast=20&miadt=45&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mires=0&mip1=$surn&mip3=$surn&mib1=157",
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
