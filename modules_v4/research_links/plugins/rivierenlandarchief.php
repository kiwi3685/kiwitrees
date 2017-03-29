<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class rivierenlandarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Rivierenland Regionaal Archief';
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
		$base_url = 'http://regionaalarchiefrivierenland.nl/';

		$collection = array(
		    "Begraafinschrijving"        => "archieven?mivast=102&miadt=102&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=158",
		    "Belastingregister"          => "archieven?mivast=102&miadt=102&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=479",
		    "Bidprentjes"                => "archieven?mivast=102&miadt=102&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=111",
		    "Borgbrief"                  => "archieven?mivast=102&miadt=102&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=288",
		    "Bouwdossier"                => "archieven?mivast=102&miadt=102&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=31",
		    "Doopinschrijving"           => "archieven?mivast=102&miadt=102&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=156",
		    "Dossier"                    => "archieven?mivast=102&miadt=102&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=203",
		    "Echtscheidingsakte"         => "archieven?mivast=102&miadt=102&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=140",
		    "Foto"                       => "archieven?mivast=102&miadt=102&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=7",
		    "Geboorteakte"               => "archieven?mivast=102&miadt=102&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=113",
		    "Hinderwetvergunning"        => "archieven?mivast=102&miadt=102&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=204",
		    "Huwelijksakte"              => "archieven?mivast=102&miadt=102&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=109",
		    "Inschrijving Butgerboek"    => "archieven?mivast=102&miadt=102&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=451",
		    "Lidmateninschrijving"       => "archieven?mivast=102&miadt=102&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=577",
		    "Notariele Akte"             => "archieven?mivast=102&miadt=102&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=224",
		    "Overlijdensakte"            => "archieven?mivast=102&miadt=102&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=114",
		    "Persoon Bevolkingsregister" => "archieven?mivast=102&miadt=102&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=112",
		    "Persoonskaarten"            => "archieven?mivast=102&miadt=102&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=455",
		    "Procesdossier"              => "archieven?mivast=102&miadt=102&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=26",
		    "Trouwinschrijving"          => "archieven?mivast=102&miadt=102&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=157",
		    "Kranten"                    => "archieven?mivast=102&mizig=91&miadt=102&milang=nl&mizk_alle=$givn%20$surn&miview=ldt",
		    "Beeldbank"                  => "archieven?mivast=102&mizig=293&miadt=102&milang=nl&mizk_alle=$givn%20$surn&miview=gal1",
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
