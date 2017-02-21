<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class sample_link_only_sublinks_plugin extends research_base_plugin {
	static function getName() {
		return 'Sample link only (sublinks)';
	}

	static function getPaySymbol() {
		return true;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return false;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return false;
	}

	static function createLinkOnly() {
		return false;
	}

	static function createSubLinksOnly() {
		$base_url = 'http://zoekakten.nl/';

		$collection = array(
		    "Groningen"	=> "prov.php?id=GR",
		    "Friesland"	=> "prov.php?id=FR",
		    "Drenthe"	=> "prov.php?id=DR",
		);

		foreach($collection as $key => $value) {
			$link[] = array(
				'title' => WT_I18N::translate($key),
				'link'  => $base_url. $value
			);
		}

		return $link;
	}

	static function encode_plus() {
		return false;
	}

}
