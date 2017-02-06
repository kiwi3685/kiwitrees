<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class sittardgeleenrhc_plugin extends research_base_plugin {
	static function getName() {
		return 'Sittard-Geleen Domijnen';
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
		$base_url = 'https://www.dedomijnen.nl/';

		$collection = array(
		"Bidprentjes"          => "collecties/archieven/personen/?mivast=111&mizig=100&miadt=111&miq=1&milang=nl&misort=last_mod%7Cdesc&mizk_alle=$givn%20$surname&mif1=111&miview=tbl",
		"Bevolkingsregister"   => "collecties/archieven/personen/?mivast=111&mizig=100&miadt=111&miq=1&milang=nl&misort=last_mod%7Cdesc&mizk_alle=$givn%20$surname&mif1=112&miview=tbl",
		    
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
