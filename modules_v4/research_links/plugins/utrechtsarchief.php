<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class utrechtsarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Utrechts Archief';
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
		$base_url = 'http://www.hetutrechtsarchief.nl/';

		$collection = array(
    "Begraafinschrijving"           => "onderzoek/resultaten/archieven?mivast=39&miadt=39&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=158",
    "Doopinschrijving"              => "onderzoek/resultaten/archieven?mivast=39&miadt=39&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=156",
    "Echtscheidingsakte"            => "onderzoek/resultaten/archieven?mivast=39&miadt=39&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=140",
    "Erkenningsakte"                => "onderzoek/resultaten/archieven?mivast=39&miadt=39&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=390",
    "Geboorteakte"                  => "onderzoek/resultaten/archieven?mivast=39&miadt=39&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=113",
    "Huwelijksakte"                 => "onderzoek/resultaten/archieven?mivast=39&miadt=39&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=109",
    "Memorie van successie"         => "onderzoek/resultaten/archieven?mivast=39&miadt=39&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=215",
    "Naamsverbetering"              => "onderzoek/resultaten/archieven?mivast=39&miadt=39&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=116",
    "Notariele akte"                => "onderzoek/resultaten/archieven?mivast=39&miadt=39&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=224",
    "Overlijdensakte"               => "onderzoek/resultaten/archieven?mivast=39&miadt=39&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=114",
    "Persoon in akte"               => "onderzoek/resultaten/archieven?mivast=39&miadt=39&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=102",
    "Persoon in bevolkingsregister" => "onderzoek/resultaten/archieven?mivast=39&miadt=39&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=112",
    "Trouwinschrijving"             => "onderzoek/resultaten/archieven?mivast=39&miadt=39&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=157",
    "Vonnis"                        => "onderzoek/resultaten/archieven?mivast=39&miadt=39&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=216",
		    
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
