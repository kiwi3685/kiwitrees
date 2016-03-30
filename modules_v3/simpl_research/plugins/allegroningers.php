<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class allegroningers_plugin extends research_base_plugin {
	static function getName() {
		return 'Alle Groningers';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return $link = 'http://www.allegroningers.nl/personen/q/persoon_achternaam_t_0/' . $surn . '/q/persoon_voornaam_t_0/' . $givn . '/q/persoon_rol_s_0/0/q/persoon_rol_s_1/0';

	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return false;
	}

	static function encode_plus() {
		return false;
	}
}
