<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class eindhovenrhc_plugin extends research_base_plugin {
	static function getName() {
		return 'Eindhoven RHC';
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
		$base_url = 'http://www.zoekjestamboom.nl/';

		$collection = array(
		"Bevolkingsregisters"    => "zoek-uitgebreid/15/Bevolkingsregister?mivast=48&miadt=48&mizig=332&miview=ldt&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn",
		"DTB"                    => "zoek-uitgebreid/14/DTB-register?mivast=48&miadt=48&mizig=347&miview=ldt&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn",
		"Burgelijke stand"       => "zoek-uitgebreid/13/Burgerlijke-stand?mivast=48&miadt=48&mizig=348&miview=ldt&milang=nl&micols=1&mires=0&mip1=$surn&mip2=$givn",
		"Personen"               => "zoek-uitgebreid/7/Personen?mivast=48&miadt=48&mizig=100&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn",
		"Genealogische bronnen"  => "zoek-uitgebreid/18/Genealogische-bronnen?mivast=48&miadt=48&mizig=380&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip3=$givn",
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
