<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class findmypast_plugin extends research_base_plugin {
	static function getName() {
		return 'findmypast';
	}

	static function getPaySymbol() {
		return true;
	}

	static function getSearchArea() {
		return 'INT';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return false;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		$base_url	= 'https://www.findmypast.com/search/';
		$url		= 'results?firstname=' . $givn . '&firstname_variants=true&lastname=' . $surname . '&yearofbirth=' . $birth_year . '&yearofbirth_offset=2';

		$collection = array(
			"World"							=>"",
			"Australia &amp; New Zealand"	=>"&sourcecountry=australasia~new%20zealand",
			"Ireland"						=>"&sourcecountry=ireland",
			"United Kingdom"				=>"&sourcecountry=great%20britain",
			"United States &amp; Canada"	=>"&sourcecountry=united%20states~canada~north%20america",
		);

		foreach($collection as $x=>$x_value) {
			$link[] = array(
				'title' => KT_I18N::translate($x),
				'link'  => $base_url . $url . $x_value
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
