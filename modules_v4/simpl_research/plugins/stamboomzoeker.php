<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class stamboomzoeker_plugin extends research_base_plugin {
	static function getName() {
		return 'Stamboomzoeker';
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
		$base_url = 'http://www.stamboomzoeker.nl/';

		$collection = array(
			"Personen"	=> 'search.php?l=nl&fn=' . $givn . '&sn=' . $surname . '&m=1&bd1=&bd2=&bp=&t=1&submit=Zoeken',
			"Stambomen"	=> 'search.php?l=nl&fn=' . $givn . '&sn=' . $surname . '&m=1&bd1=&bd2=&bp=&t=2&submit=Zoeken',		
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
