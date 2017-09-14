<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class dordrechtarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Dordrecht Regionaal Archief';
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
		$base_url = 'http://www.regionaalarchiefdordrecht.nl/';

		$collection = array(
		    "DTB"                         => "archief/zoeken/?mivast=46&mizig=334&miadt=46&milang=nl&mizk_alle=$givn%20$surn&miview=ldt",
		    "Geboorteakten"               => "archief/zoeken/?mivast=46&mizig=133&miadt=46&milang=nl&mizk_alle=$givn%20$surn&miview=ldt",
		    "Huwelijksakten"              => "archief/zoeken/?mivast=46&mizig=53&miadt=46&milang=nl&mizk_alle=$givn%20$surn&miview=ldt",
		    "Overlijdensakten"            => "archief/zoeken/?mivast=46&mizig=782&miadt=46&milang=nl&mizk_alle=$givn%20$surn&miview=ldt",
		    "Bevolkingsregisters"         => "archief/zoeken/?mivast=46&mizig=106&miadt=46&milang=nl&mizk_alle=$givn%20$surn&miview=tbl",
		    "Eigendomsoverdracht"         => "archief/zoeken/?mivast=46&mizig=132&miadt=46&milang=nl&mizk_alle=$givn%20$surn&miview=ldt",
		    "Notariele Akten"             => "archief/zoeken/?mivast=46&mizig=232&miadt=46&milang=nl&mizk_alle=$givn%20$surn&miview=ldt",
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
