<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class zaanstadarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Zaanstad Archief';
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
		$base_url = 'http://archief.zaanstad.nl/';

		$collection = array(
		    
		    "Bevolkingsregister"       => "archieven/zoek-in-de-archieven?mivast=137&miadt=137&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn&mib1=388",
		    "Bidprentje"               => "archieven/zoek-in-de-archieven?option=com_maisinternet&view=maisinternet&Itemid=141&mivast=137&miadt=137&mizig=100&miview=tbl&milang=nl&micols=1&misort=ach%7Casc&mires=0&mip1=$surn&mip3=$givn&mib1=111",
			"Geboorteakte"             => "archieven/zoek-in-de-archieven?option=com_maisinternet&view=maisinternet&Itemid=141&mivast=137&miadt=137&mizig=100&miview=tbl&milang=nl&micols=1&misort=ach%7Casc&mires=0&mif1=113&mip1=$surn&mip3=$givn",
		    "Huwelijksakte"            => "archieven/zoek-in-de-archieven?option=com_maisinternet&view=maisinternet&Itemid=141&mivast=137&miadt=137&mizig=100&miview=tbl&milang=nl&micols=1&misort=ach%7Casc&mires=0&mif1=109&mip1=$surn&mip3=$givn",
		    "Overlijdensakte"          => "archieven/zoek-in-de-archieven?option=com_maisinternet&view=maisinternet&Itemid=141&mivast=137&miadt=137&mizig=100&miview=tbl&milang=nl&micols=1&misort=ach%7Casc&mires=0&mif1=114&mip1=$surn&mip3=$givn",		    
		    "Transcripties"            => "archieven/zoek-in-de-archieven?mivast=137&miadt=137&mizig=309&miview=ldt&milang=nl&micols=1&mires=0&mizk_alle=$givn%20$surn",
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
