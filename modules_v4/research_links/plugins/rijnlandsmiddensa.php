<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class rijnlandsmiddensa_plugin extends research_base_plugin {
	static function getName() {
		return 'Rijnlands Midden Streekarchief';
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
		$base_url = 'http://www.streekarchiefrijnlandsmidden.nl/';

		$collection = array(
			"Bevolkingsregister"	=> "collecties/archiefbank?mivast=105&mizig=100&miadt=105&miq=1&milang=nl&misort=last_mod%7Cdesc&mip1=' . $surn . '&mip3=' . $givn . '&mif1=112&miview=tbl",
			"Geboorteakten"			=> "collecties/archiefbank?mivast=105&mizig=100&miadt=105&miq=1&milang=nl&misort=last_mod%7Cdesc&mip1=' . $surn . '&mip3=' . $givn . '&mif1=113&miview=tbl",
			"Huwelijksakten"		=> "collecties/archiefbank?mivast=105&mizig=100&miadt=105&miq=1&milang=nl&misort=last_mod%7Cdesc&mip1=' . $surn . '&mip3=' . $givn . '&mif1=109&miview=tbl",
			"Overlijdensakten"		=> "collecties/archiefbank?mivast=105&mizig=100&miadt=105&miq=1&milang=nl&misort=last_mod%7Cdesc&mip1=' . $surn . '&mip3=' . $givn . '&mif1=114&miview=tbl",
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
