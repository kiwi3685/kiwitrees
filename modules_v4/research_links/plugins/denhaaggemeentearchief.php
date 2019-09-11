<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class denhaaggemeentearchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Den Haag Gemeentearchief';
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
		$base_url = 'https://haagsgemeentearchief.nl/';

		$collection = array(
			"Echtscheidingsakte"  => "archieven-mais/overzicht?mivast=59&mizig=100&miadt=59&miq=1&milang=nl&misort=an%7Casc&mip1='$surname'&mip3='$givn'&mif1=140&miview=tbl",
			"Geboorteakte"		  => "archieven-mais/overzicht?mivast=59&mizig=100&miadt=59&miq=1&milang=nl&misort=an%7Casc&mip1='$surname'&mip3='$givn'&mif1=113&miview=tbl",
			"Huwelijksakte"		  => "archieven-mais/overzicht?mivast=59&mizig=100&miadt=59&miq=1&milang=nl&misort=an%7Casc&mip1='$surname'&mip3='$givn'&mif1=109&miview=tbl",
			"Notariele Akte"	  => "archieven-mais/overzicht?mivast=59&mizig=100&miadt=59&miq=1&milang=nl&misort=an%7Casc&mip1='$surname'&mip3='$givn'&mif1=224&miview=tbl",
			"Overlijdensakte"	  => "archieven-mais/overzicht?mivast=59&mizig=100&miadt=59&miq=1&milang=nl&misort=an%7Casc&mip1='$surname'&mip3='$givn'&mif1=114&miview=tbl",
			"Bevolkingsregister"  => "archieven-mais/overzicht?mivast=59&mizig=100&miadt=59&miq=1&milang=nl&misort=an%7Casc&mip1='$surname'&mip3='$givn'&mif1=388&miview=tbl",
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
