<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class westfriesarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Westfries archief';
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
		$base_url = 'https://www.westfriesarchief.nl/';

		$collection = array(
			"Begraafinschrijving"		     => "onderzoek/zoeken/personen?mivast=136&miadt=136&mizig=100&miview=tbl&milang=nl&micols=1&mip1=$surn&mip3=$givn&mib1=158",
			"Bidprentje"		             => "onderzoek/zoeken/personen?mivast=136&miadt=136&mizig=100&miview=tbl&milang=nl&micols=1&mip1=$surn&mip3=$givn&mib1=111",
			"Doopinschrijving"		         => "onderzoek/zoeken/personen?mivast=136&miadt=136&mizig=100&miview=tbl&milang=nl&micols=1&mip1=$surn&mip3=$givn&mib1=156",
			"Geboorteinschrijving"		     => "onderzoek/zoeken/personen?mivast=136&miadt=136&mizig=100&miview=tbl&milang=nl&micols=1&mip1=$surn&mip3=$givn&mib1=573",
			"Graf"	                         => "onderzoek/zoeken/personen?mivast=136&miadt=136&mizig=100&miview=tbl&milang=nl&micols=1&mip1=$surn&mip3=$givn&mib1=453",
			"Huwelijksafkondiging"           => "onderzoek/zoeken/personen?mivast=136&miadt=136&mizig=100&miview=tbl&milang=nl&micols=1&mip1=$surn&mip3=$givn&mib1=155",
			"Impost op begraven	"            => "onderzoek/zoeken/personen?mivast=136&miadt=136&mizig=100&miview=tbl&milang=nl&micols=1&mip1=$surn&mip3=$givn&mib1=402",
			"Impost op trouwen"              => "onderzoek/zoeken/personen?mivast=136&miadt=136&mizig=100&miview=tbl&milang=nl&micols=1&mip1=$surn&mip3=$givn&mib1=401",
			"Krantenartikel"                 => "onderzoek/zoeken/personen?mivast=136&miadt=136&mizig=100&miview=tbl&milang=nl&micols=1&mip1=$surn&mip3=$givn&mib1=278",
			"Lidmaat"                        => "onderzoek/zoeken/personen?mivast=136&miadt=136&mizig=100&miview=tbl&milang=nl&micols=1&mip1=$surn&mip3=$givn&mib1=199",
			"Notariele akte"                 => "onderzoek/zoeken/personen?mivast=136&miadt=136&mizig=100&miview=tbl&milang=nl&micols=1&mip1=$surn&mip3=$givn&mib1=224",
			"Ondertrouwregistratie"          => "onderzoek/zoeken/personen?mivast=136&miadt=136&mizig=100&miview=tbl&milang=nl&micols=1&mip1=$surn&mip3=$givn&mib1=174",
			"Overlijdensaangifte"            => "onderzoek/zoeken/personen?mivast=136&miadt=136&mizig=100&miview=tbl&milang=nl&micols=1&mip1=$surn&mip3=$givn&mib1=452",
			"Overlijdensinschrijving"        => "onderzoek/zoeken/personen?mivast=136&miadt=136&mizig=100&miview=tbl&milang=nl&micols=1&mip1=$surn&mip3=$givn&mib1=325",
			"Persoon in bevolkingsregister"  => "onderzoek/zoeken/personen?mivast=136&miadt=136&mizig=100&miview=tbl&milang=nl&micols=1&mip1=$surn&mip3=$givn&mib1=112",
		    "Trouwinschrijving"              => "onderzoek/zoeken/personen?mivast=136&miadt=136&mizig=100&miview=tbl&milang=nl&micols=1&mip1=$surn&mip3=$givn&mib1=157",
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
