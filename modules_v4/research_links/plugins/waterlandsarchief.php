<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class waterlandsarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Waterlands Archief';
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
		$base_url = 'https://waterlandsarchief.nl/';

		$collection = array(
			"Persoon in bevolkingsregister"	 => "personen?mivast=131&mizig=100&miadt=131&milang=nl&misort=ach%7Casc&mip1=$surn&mip3=$givn&mif1=112&miview=tbl",
			"Foto"		                     => "personen?mivast=131&mizig=100&miadt=131&milang=nl&misort=ach%7Casc&mip1=$surn&mip3=$givn&mif1=7&miview=tbl",
			"Notariele akte"	             => "personen?mivast=131&mizig=100&miadt=131&milang=nl&misort=ach%7Casc&mip1=$surn&mip3=$givn&mif1=224&miview=tbl",
			
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
