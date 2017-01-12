<?php

if (!defined('WT_WEBTREES')) {
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

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = 'http://archief.hengelo.nl/genealogie/zoek.php?type=advanced&achternaam=' . $surname . '&voornaam=' . $first . '&gezinsbladen=1&geboortedatum_dag=&geboortedatum_maand=&geboortedatum_jaar=&gezinskaarten=1&kostgangerskaarten=1&geboorten=1&aktenummer=&aktejaar=&huwelijken=1&overlijden=1';
	}

	static function create_sublink() {
		return false;
	}

	static function createLinkOnly() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}
