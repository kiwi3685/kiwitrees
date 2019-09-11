<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class overijsselhistorischcentrum_plugin extends research_base_plugin {
	static function getName() {
		return 'Overijssel Historisch Centrum';
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
		$base_url = 'https://www.historischcentrumoverijssel.nl/';

		$collection = array(
		"Archiefvormend persoon"      => "archieven/?mivast=20&miadt=141&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=133",
		"Echtscheidingsakte"          => "archieven/?mivast=20&miadt=141&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=140",
		"Emigrant"                    => "archieven/?mivast=20&miadt=141&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=296",
		"Functionaris"                => "archieven/?mivast=20&miadt=141&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=73",
		"Geboorteakte"                => "archieven/?mivast=20&miadt=141&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=113",
		"Huwelijksakte"               => "archieven/?mivast=20&miadt=141&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=109",
		"Inschrijving naamsaaneming"  => "archieven/?mivast=20&miadt=141&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=486",
		"Overlijdensakte"             => "archieven/?mivast=20&miadt=141&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=114",
		"Persoon"                     => "archieven/?mivast=20&miadt=141&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=108",
		"Persoon bevolkingsregister"  => "archieven/?mivast=20&miadt=141&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=112",
		"Persoonbeschrijving"         => "archieven/?mivast=20&miadt=141&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=211",
		"Zwolle Generale Index"       => "archieven/?mivast=20&miadt=141&mizig=235&miview=ldt&milang=nl&micols=1&mires=0&mizk_alle=$givn%20$surn",
		    
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
