<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class vechtenvenenrhc_plugin extends research_base_plugin {
	static function getName() {
		return 'Vecht en Venen RHC';
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
		$base_url = 'http://www.rhcvechtenvenen.nl/';

		$collection = array(
		"Bevolkingsregistratie" => "collectie/?mivast=386&mizig=100&miadt=386&miq=1&milang=nl&misort=last_mod%7Cdesc&mip1=$surn&mip3=$givn&mif3=BR-Bevolkingsregistratie&miview=tblprov.php?id=GR",
		"BS Geboorten"          => "collectie/?mivast=386&mizig=100&miadt=386&miq=1&milang=nl&misort=last_mod%7Cdesc&mip1=$surn&mip3=$givn&mif3=BS-Geboorten&miview=tbl",
		"BS Huwelijken"         => "collectie/?mivast=386&mizig=100&miadt=386&miq=1&milang=nl&misort=last_mod%7Cdesc&mip1=$surn&mip3=$givn&mif3=BS-Huwelijken%20en%20echtscheidingen&miview=tbl",
		"DTB Dopen"             => "collectie/?mivast=386&mizig=100&miadt=386&miq=1&milang=nl&misort=last_mod%7Cdesc&mip1=$surn&mip3=$givn&mif3=DTB-Dopen&miview=tbl",
		"DTB Trouwen"           => "collectie/?mivast=386&mizig=100&miadt=386&miq=1&milang=nl&misort=last_mod%7Cdesc&mip1=$surn&mip3=$givn&mif3=DTB-Trouwen&miview=tbl",
		"Functionaris"          => "collectie/?mivast=386&mizig=100&miadt=386&miq=1&milang=nl&misort=last_mod%7Cdesc&mip1=$surn&mip3=$givn&mif3=Functionaris&miview=tbl",
		"Impost op begraven"    => "collectie/?mivast=386&mizig=100&miadt=386&miq=1&milang=nl&misort=last_mod%7Cdesc&mip1=$surn&mip3=$givn&mif3=Gaarder-Impost%20op%20het%20begraven&miview=tbl",
		"Gerecht Ondertrouwen"  => "collectie/?mivast=386&mizig=100&miadt=386&miq=1&milang=nl&misort=last_mod%7Cdesc&mip1=$surn&mip3=$givn&mif3=Gerecht-Ondertrouwen&miview=tbl",
		"Gerecht Trouwen"       => "collectie/?mivast=386&mizig=100&miadt=386&miq=1&milang=nl&misort=last_mod%7Cdesc&mip1=$surn&mip3=$givn&mif3=Gerecht-Trouwen&miview=tbl",
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
