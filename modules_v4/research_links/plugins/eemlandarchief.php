<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class eemlandarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Eemland Archief';
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
		$base_url = 'http://www.archiefeemland.nl/';

		$collection = array(
			"BS personen"               => "collectie/voorouders/persons?ss=%7B%22q%22:%22$givn%20$surn%22%7D",	
    "Notariele Akten"           => "collectie/archieven/zoekresultaat?mivast=28&miadt=28&mizig=29&miview=ldt&milang=nl&micols=1&misort=last_mod%7Cdesc&mires=0&mip1=$surn",
	"Dorpsgerechten"            => "collectie/archieven/zoekresultaat?mivast=28&miadt=28&mizig=33&miview=tbl&milang=nl&micols=1&mires=0&mizk_alle=$givn%20$surn",
	"Archieven personen"        => "collectie/archieven/zoekresultaat?mivast=28&miadt=28&mizig=100&miview=tbl&milang=nl&micols=1&mip1=$surn&mip3=$givn",
	"Resoluties"                => "collectie/archieven/zoekresultaat?mivast=28&miadt=28&mizig=282&miview=ldt&milang=nl&micols=1&mires=0&mizk_alle=$surn",
	"Transportakten Amersfoort" => "collectie/archieven/zoekresultaat?mivast=28&miadt=28&mizig=273&miview=tbl&milang=nl&micols=1&mires=0&mizk_alle=$surn&mibj=$birth_year&miej=$death_year",
	"Belastingkohieren"         => "collectie/archieven/zoekresultaat?mivast=28&miadt=28&mizig=345&miview=ldt&milang=nl&micols=1&mires=0&mip1=$surn",
	"Lidmaten"                  => "collectie/archieven/zoekresultaat?mivast=28&miadt=28&mizig=244&miview=tbl&milang=nl&micols=1&mires=0&mip1=$givn&mip2=$givn",
	"Dienstplichtigen"          => "collectie/archieven/zoekresultaat?mivast=28&miadt=28&mizig=265&miview=tbl&milang=nl&micols=1&mires=0&mip1=$givn&mip2=$surn",
	"Volkstellingregister"      => "collectie/archieven/zoekresultaat?mivast=28&miadt=28&mizig=268&miview=tbl&milang=nl&micols=1&mires=0&mip2=$givn&mip1=$givn",
	"Inschrijving weeskamer"    => "collectie/archieven/zoekresultaat?mivast=28&miadt=28&mizig=71&miview=ldt&milang=nl&micols=1&mires=0&mizk_alle=$surn",
	"Akten van indemniteit"     => "collectie/archieven/zoekresultaat?mivast=28&miadt=28&mizig=264&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn",
	"Bestuurders"               => "collectie/archieven/zoekresultaat?mivast=28&miadt=28&mizig=266&miview=ldt&milang=nl&micols=1&mires=0&mip1=$givn&mip3=$surn",
	"Gastelingen"               => "collectie/archieven/zoekresultaat?mivast=28&miadt=28&mizig=267&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn",
	"Burgerrecht verleningen"   => "collectie/archieven/zoekresultaat?mivast=28&miadt=28&mizig=242&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn",
	"Weeskinderen"              => "collectie/archieven/zoekresultaat?mivast=28&miadt=28&mizig=243&miview=tbl&milang=nl&micols=1&mires=0&mip1=$givn&mip2=$givn",
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
