<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class voorneputtenrozenburgsa_plugin extends research_base_plugin {
	static function getName() {
		return 'Voorne-Putten-Rozenburg SA';
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
		$base_url = 'http://www.streekarchiefvpr.nl/';

		$collection = array(
		"Geboorteakte"                 => "pages/nl/zoeken-in-collecties.php?mivast=126&mizig=100&miadt=126&miq=1&milang=nl&misort=last_mod%7Cdesc&mip1=$surname%20%2C%20$givn&mif1=113&miview=tbl",
		"Overlijdensakte"              => "pages/nl/zoeken-in-collecties.php?mivast=126&mizig=100&miadt=126&miq=1&milang=nl&misort=last_mod%7Cdesc&mip1=$surname%20%2C%20$givn&mif1=114&miview=tbl",
		"Huwelijksakte"                => "pages/nl/zoeken-in-collecties.php?mivast=126&mizig=100&miadt=126&miq=1&milang=nl&misort=last_mod%7Cdesc&mip1=$surname%20%2C%20$givn&mif1=109&miview=tbl",
		"Doopakte"                     => "pages/nl/zoeken-in-collecties.php?mivast=126&mizig=100&miadt=126&miq=1&milang=nl&misort=last_mod%7Cdesc&mip1=$surname%20%2C%20$givn&mif1=106&miview=tbl",
		"Persoon in militie"           => "pages/nl/zoeken-in-collecties.php?mivast=126&miadt=126&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mires=0&mif1=265&mip1=$surn&mip3=$givn",
		"Functionaris"                 => "pages/nl/zoeken-in-collecties.php?mivast=126&miadt=126&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mires=0&mif1=73&mip1=$surname%20%2C%20$givn",
		"Echtscheidingsakte"           => "pages/nl/zoeken-in-collecties.php?mivast=126&miadt=126&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mires=0&mif1=140&mip1=$givn%20$surname",
		"Wettiging kinderen"           => "pages/nl/zoeken-in-collecties.php?mivast=126&miadt=126&mizig=100&miview=tbl&milang=nl&micols=1&misort=last_mod%7Cdesc&mires=0&mif1=141&mip1=$givn%20$surname",
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
