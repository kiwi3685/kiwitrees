<?php

if (!defined('WT_KIWITREES')) {
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
		$base_url	= 'http://search.findmypast.com/search/';
		$url		= '?firstname=' . $givn . '&firstname_variants=true&lastname=' . $surname . '&yearofbirth=' . $birth_year . '&yearofbirth_offset=2';

		$collection = array(
			"World"							=>"world-records",
			"Australia &amp; New Zealand"	=>"australia-and-new-zealand-records",
			"Ireland"						=>"ireland-records",
			"United Kingdom"				=>"united-kingdom-records",
			"United States &amp; Canada"	=>"united-states-records",
		);

		foreach($collection as $x=>$x_value) {
			$link[] = array(
				'title' => WT_I18N::translate($x),
				'link'  => $base_url. $x_value . $url
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
