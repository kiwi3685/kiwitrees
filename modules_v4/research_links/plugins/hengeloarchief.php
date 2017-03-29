<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class hengeloarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Hengelo Archief';
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
		$base_url = 'http://archief.hengelo.nl/';

		$collection = array(
		"Gezinsbladen"         => "genealogie/zoek.php?type=advanced&achternaam=$surn&voornaam=$givn&gezinsbladen=1&geboortedatum_dag=&geboortedatum_maand=&geboortedatum_jaar=&aktenummer=&aktejaar=",
		"Gezinskaarten"	       => "genealogie/zoek.php?type=advanced&achternaam=$surn&voornaam=$givn&geboortedatum_dag=&geboortedatum_maand=&geboortedatum_jaar=&gezinskaarten=1&aktenummer=&aktejaar=",
		"Kostgangerskaarten "  => "genealogie/zoek.php?type=advanced&achternaam=$surn&voornaam=$givn&geboortedatum_dag=&geboortedatum_maand=&geboortedatum_jaar=&kostgangerskaarten=1&aktenummer=&aktejaar=",
		"Geboorteakten"		   => "genealogie/zoek.php?type=advanced&achternaam=$surn&voornaam=$givn&geboortedatum_dag=&geboortedatum_maand=&geboortedatum_jaar=&geboorten=1&aktenummer=&aktejaar=",
		"Huwelijksakten"	   => "genealogie/zoek.php?type=advanced&achternaam=$surn&voornaam=$givn&geboortedatum_dag=&geboortedatum_maand=&geboortedatum_jaar=&aktenummer=&aktejaar=&huwelijken=1",
		"Overlijdensakten"	   => "genealogie/zoek.php?type=advanced&achternaam=$surn&voornaam=$givn&geboortedatum_dag=&geboortedatum_maand=&geboortedatum_jaar=&aktenummer=&aktejaar=&overlijden=1",
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
