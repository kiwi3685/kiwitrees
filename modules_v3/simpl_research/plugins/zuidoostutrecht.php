<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class zuidoostutrecht_plugin extends research_base_plugin {
	static function getName() {
		return 'Zuidoost Utrecht RHC';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		return $link = '#';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return false;
	}

	static function createLinkOnly() {
		$base_url = 'http://www.rhczuidoostutrecht.com/';

		$collection = array(
		    "DTB"                   => "aw_dtb_hua.php",
		    "Kranten"               => "aw_kranten.php",
	    );
		foreach($collection as $x => $x_value) {
			$link[] = array(
				'title' => WT_I18N::translate($x),
				'link'  => $base_url. $x_value
			);
		}
		return $link;
	}

	static function encode_plus() {
		return false;
	}

}
