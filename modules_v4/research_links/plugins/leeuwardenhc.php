<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class leeuwardenhc_plugin extends research_base_plugin {
	static function getName() {
		return 'Leeuwarden HC';
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
		$base_url = 'https://historischcentrumleeuwarden.nl/';

		$collection = array(
			"Personen"                  => "onderzoek/archievenoverzicht?mivast=76&miadt=76&mizig=100&miview=tbl&milang=nl&micols=1&mip1=$surn&mip3=$givn",
			"Diverse Indexen tot 1811"	=> "onderzoek/archievenoverzicht?mivast=76&miadt=76&mizig=119&miview=ldt&milang=nl&micols=1&mip5=$givn&mip6=$surn",
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
