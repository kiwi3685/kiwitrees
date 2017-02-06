<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class deliemersdoesburg_plugin extends research_base_plugin {
	static function getName() {
		return 'De Liemers-Doesburg';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		return false;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		$base_url = 'http://www.liemersverleden.nl/sald/';

		$collection = array(
		    "Angerlo BS"                      => "burgstand_search.php?gemeente=AN&aktesoort=&submit=maakt_niet_uit&achternaam=$surn&methode=bevat&voornaam=$givn&submit=Start+zoeken",
		    "Angerlo Bevolkingsregister"      => "indexbevangerlo_search.php?submit=maakt_niet_uit&achternaam=$surn&methode=bevat&voornaam=$givn&submit=Start+zoeken",
		    "Didam BS"                        => "burgstand_search.php?gemeente=DI&aktesoort=&submit=maakt_niet_uit&achternaam=$surn&methode=bevat&voornaam=$givn&submit=Start+zoeken",
		    "Didam Bevolkingsregister"        => "indexbevdidam_search.php?submit=maakt_niet_uit&achternaam=$surn&methode=bevat&voornaam=$givn&submit=Start+zoeken",
		    "Doesburg BS"                     => "burgstand_search.php?gemeente=DI&aktesoort=&submit=maakt_niet_uit&achternaam=$surn&methode=bevat&voornaam=$givn&submit=Start+zoeken",
		    "Duiven BS"                       => "burgstand_search.php?gemeente=DU&aktesoort=&submit=maakt_niet_uit&achternaam=$surn&methode=bevat&voornaam=$givn&submit=Start+zoeken",
		    "Herwen en Aerdt BS"              => "burgstand_search.php?gemeente=HA&aktesoort=&submit=maakt_niet_uit&achternaam=$surn&methode=bevat&voornaam=$givn&submit=Start+zoekenL",
		    "Pannerden BS"                    => "burgstand_search.php?gemeente=PA&aktesoort=&submit=maakt_niet_uit&achternaam=$surn&methode=bevat&voornaam=$givn&submit=Start+zoeken",
		    "Wehl BS"                         => "burgstand_search.php?gemeente=WH&aktesoort=&submit=maakt_niet_uit&achternaam=$surn&methode=bevat&voornaam=$givn&submit=Start+zoeken",
		    "Wehl Bevolkingsregister"         => "indexbevwehl_search.php?submit=maakt_niet_uit&achternaam=$surn&methode=bevat&voornaam=$givn&submit=Start+zoeken",		    
		    "Westervoort BS"                  => "burgstand_search.php?gemeente=WT&aktesoort=&submit=maakt_niet_uit&achternaam=$surn&methode=bevat&voornaam=$givn&submit=Start+zoeken",
		    "Westervoort Bevolkingsregister"  => "indexbevwestervoort_search.php?submit=maakt_niet_uit&achternaam=$surn&methode=bevat&voornaam=$givn&submit=Start+zoeken",		    
		    "Zevenaar BS"                     => "burgstand_search.php?gemeente=ZE&aktesoort=&submit=maakt_niet_uit&achternaam=$surn&methode=bevat&voornaam=$givn&submit=Start+zoeken",		    
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
