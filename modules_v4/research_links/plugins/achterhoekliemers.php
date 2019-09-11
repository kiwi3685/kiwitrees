<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class achterhoekliemers_plugin extends research_base_plugin {
	static function getName() {
		return 'Achterhoek Liemers erfgoed';
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
		$base_url = 'http://www.ecal.nu/';

		$collection = array(
			"Persoon in bevolkingsregister"	=> "?title=archief&mivast=26&miadt=26&mizig=118&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip3=$givn",
			"Persoon in troepenmacht"		=> "?title=archief&mivast=26&miadt=26&mizig=300&miview=tbl&milang=nl&micols=1&mires=0&mip1=$surn&mip2=$givn",
			"Persoon in procesverbaal"		=> "?title=archief&mivast=26&miadt=26&mizig=19&miview=ldt&milang=nl&micols=1&mires=0&mip1=$givn&mip2=$surn",
			"Notariele akten"				=> "?title=archief&mivast=26&mizig=57&miadt=26&milang=nl&mizk_alle=%22$givn%20$surn%22&miview=tbl",
			"Rechterlijke archieven"		=> "?title=archief&mivast=26&mizig=56&miadt=26&milang=nl&mizk_alle=%22$givn%20$surn%22&miview=ldt",
			"Andere archieven"				=> "?title=archief&mistart=18&mivast=26&mizig=0&miadt=26&milang=nl&misort=last_mod%7Cdesc&miview=lst&mizk_alle=%22$givn%20$surn%22",
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
