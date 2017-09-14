<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class geldersarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Gelders Archief';
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
		$base_url = 'https://www.geldersarchief.nl/';

		$collection = array(
		    "Begraafinschrijving"           => "bronnen/personen?mivast=37&miadt=37&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=158",
		    "Deelbeschrijving"              => "bronnen/personen?mivast=37&miadt=37&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=4",
		    "Doopinschrijving"              => "bronnen/personen?mivast=37&miadt=37&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=156",
		    "Enkelvoudige beschrijving"     => "bronnen/personen?mivast=37&miadt=37&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=5",
		    "Erkenningsakte"                => "bronnen/personen?mivast=37&miadt=37&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=390",
		    "Familiewapen"                  => "bronnen/personen?mivast=37&miadt=37&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=471",
		    "Geboorteakte"                  => "bronnen/personen?mivast=37&miadt=37&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=113",
		    "Gerichtsakte"                  => "bronnen/personen?mivast=37&miadt=37&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=554",
		    "Huwelijksakte"                 => "bronnen/personen?mivast=37&miadt=37&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=109",
		    "Lidmaat"                       => "bronnen/personen?mivast=37&miadt=37&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=199",
		    "Lidmateninschrijving"          => "bronnen/personen?mivast=37&miadt=37&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=577",
		    "Overlijdensakte"               => "bronnen/personen?mivast=37&miadt=37&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=114",
		    "Persoon"                       => "bronnen/personen?mivast=37&miadt=37&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=108",
		    "Persoon in akte"               => "bronnen/personen?mivast=37&miadt=37&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=102",
		    "Persoon bevolkingsregister"    => "bronnen/personen?mivast=37&miadt=37&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=112",
		    "Persoon notariele akte"        => "bronnen/personen?mivast=37&miadt=37&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=234",
		    "Memorie van successie"         => "bronnen/personen?mivast=37&miadt=37&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=275",
		    "Persoonbeschrijving"           => "bronnen/personen?mivast=37&miadt=37&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=211",
		    "Trouwinschrijving"             => "bronnen/personen?mivast=37&miadt=37&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=157",
		    "Vermelding"                    => "bronnen/personen?mivast=37&miadt=37&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=392",
		    "Vinding"                       => "bronnen/personen?mivast=37&miadt=37&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=434",
		    
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
