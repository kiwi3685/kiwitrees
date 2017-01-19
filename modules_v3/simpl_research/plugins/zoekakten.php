<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class zoekakten_plugin extends research_base_plugin {
	static function getName() {
		return 'Zoekakten';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) { // SEE FAQ FOR EXPLANATION OF EACH PART
		return false;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		 $base_url = 'http://zoekakten.nl/';

		$collection = array(
		    "NL Groningen"               => "prov.php?id=GR",
		    "NL Friesland"               => "prov.php?id=FR",
		    "NL Drenthe"                 => "prov.php?id=DR",
		    "NL Overijssel"              => "prov.php?id=OV",
		    "NL Gelderland"              => "prov.php?id=GL",
		    "NL Flevoland"               => "prov.php?id=FL",
		    "NL Utrecht"                 => "prov.php?id=UT",
		    "NL Noord-Holland"           => "prov.php?id=NH",
		    "NL Zuid-Holland"            => "prov.php?id=ZH",
		    "NL Zeeland"                 => "prov.php?id=ZL",
		    "NL Noord-Brabant"           => "prov.php?id=NB",
		    "NL Limburg"                 => "prov.php?id=LB",
		    "NL Nederlands Antillen"     => "prov.php?id=NA",
		    "NL Suriname"                => "prov.php?id=SU",
		    "NL Militaire Stamboeken"    => "zoekmil2.php?soort=2&anaam=' . $surn . '&vnaam=' . $givn . '&submit=Zoek'",
		    "NL Landverhuizers"          => "zoeklv2.php?soort=2&naam=' . $surn . '&submit=Zoek'",
		    "NL Kraamvrouwbekentenis"    => "kraamvrouwen2.php?naam=' . $surn",
		    "NL Bijzondere Collectie"    => "collectie.php",
		    "Zoek op plaatsnaam NL+BE"   => "zoekplts.php",
		    "Franse Jaartelling"         => "fsfrans.php",
		    "BE West-Vlaanderen"         => "prov.php?id=VW",
		    "BE Oost-Vlaanderen"         => "prov.php?id=VO",
		    "BE Antwerpen"               => "prov.php?id=AW",
		    "BE Limburg"                 => "prov.php?id=BL",
		    "BE Vlaams-Brabant"          => "prov.php?id=BV",
		    "BE Brussel"                 => "prov.php?id=BR",
		    "BE Henegouwen"              => "prov.php?id=HT",
		    "BE Waals-Brabant"           => "prov.php?id=BW",
		    "BE Namen"                   => "prov.php?id=NM",
		    "BE Luik"                    => "prov.php?id=LG",
		    "BE Luxemburg"               => "prov.php?id=LX",
		);

		foreach($collection as $x => $x_value) {
			$link[] = array(
				'title' => WT_I18N::translate($x),
				'link'  => $base_url. $x_value
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
