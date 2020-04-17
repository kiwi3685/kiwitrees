<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class alphenaandenrijn_plugin extends research_base_plugin {
	static function getName() {
		return 'Alphen aan den Rijn GA';
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
		$base_url = 'https://gemeentearchief.alphenaandenrijn.nl/';

		$collection = array(
			"Begraafinschrijving"	     => "collectie?mivast=105&miadt=105&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mif1=158&mip1=$surn&mip3=$givn",
			"Doopinschrijving"	         => "collectie?mivast=105&miadt=105&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mif1=156&mip1=$surn&mip3=$givn",
			"Echtscheidingsakte"	     => "collectie?mivast=105&miadt=105&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mif1=140&mip1=$surn&mip3=$givn",
			"Erkenningsakte"	         => "collectie?mivast=105&miadt=105&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mif1=390&mip1=$surn&mip3=$givn",
			"Geboorteakte"	             => "collectie?mivast=105&miadt=105&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mif1=113&mip1=$surn&mip3=$givn",
			"Huwelijksakte"	             => "collectie?mivast=105&miadt=105&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mif1=109&mip1=$surn&mip3=$givn",
			"Overlijdensakte"            => "collectie?mivast=105&miadt=105&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mif1=114&mip1=$surn&mip3=$givn", 
			"Huwelijksakte"	             => "collectie?mivast=105&miadt=105&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mif1=109&mip1=$surn&mip3=$givn",
			"Persoon in akte"	         => "collectie?mivast=105&miadt=105&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mif1=102&mip1=$surn&mip3=$givn",
			"Persoon bevolkingsregister" => "collectie?mivast=105&miadt=105&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mif1=112&mip1=$surn&mip3=$givn",
			"Trouwinschrijving"	         => "collectie?mivast=105&miadt=105&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mif1=157&mip1=$surn&mip3=$givn",
		
			
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
