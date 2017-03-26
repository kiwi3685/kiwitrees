<?php

if (!defined('WT_WEBTREES')) {
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
		$base_url = 'http://www.historischcentrumoverijssel.nl/';

		$collection = array(
		"Doopinschrijving"            => "zoeken-in-de-collecties/archieven?mivast=141&miadt=141&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=156",
		"Echtscheidingsakte"          => "zoeken-in-de-collecties/archieven?mivast=141&miadt=141&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=140",
		"Emigrant"                    => "zoeken-in-de-collecties/archieven?mivast=141&miadt=141&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=296",
		"Geboorteakte"                => "zoeken-in-de-collecties/archieven?mivast=141&miadt=141&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=113",
		"Huwelijksakte"               => "zoeken-in-de-collecties/archieven?mivast=141&miadt=141&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=109",
		"Inschrijving naamsaaneming"  => "zoeken-in-de-collecties/archieven?mivast=141&miadt=141&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=486",
		"Overlijdensakte"             => "zoeken-in-de-collecties/archieven?mivast=141&miadt=141&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mip1=$givn&mib1=114",
		"Persoon"                     => "zoeken-in-de-collecties/archieven?mivast=141&miadt=141&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=woldradesoen&mip1=johan&mib1=108",
		"Persoon bevolkingsregister"  => "zzoeken-in-de-collecties/archieven?mivast=141&miadt=141&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mib1=112",
		"Persoonbeschrijving"         => "zoeken-in-de-collecties/archieven?mivast=141&miadt=141&mizig=128&miview=tbl&milang=nl&micols=1&mires=0&mip2=$surn&mib1=211",
		"Zwolle Generale Index"       => "zoeken-in-de-collecties/archieven?mivast=141&miadt=141&mizig=235&miview=ldt&milang=nl&micols=1&misort=last_mod%7Casc&mires=0&mip1=$surn&mip2=$givn",
		    
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
