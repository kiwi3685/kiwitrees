<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class ancestry_plugin extends research_base_plugin {
	static function getName() {
		return 'Ancestry';
	}

	static function getPaySymbol() {
		return true;
	}

	static function getSearchArea() {
		return 'INT';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return false;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		$domain = array(
			// these are all the languages supported by ancestry. See: http://corporate.ancestry.com/about-ancestry/international/
			'de'		=> 'de',		// German
			'en_GB' 	=> 'co.uk',		// English
			'en_US'		=> 'com',		// American
			'en_AUS'	=> 'com.au',	// Australian
			'fr'		=> 'fr',		// French
			'it'		=> 'it',		// Italian
			'sv'		=> 'se',		// Swedish
			);
		// ancestry supports Canada in English and French versions, too; but kiwitrees doesn't support these language versions
		if (isset($domain[WT_LOCALE])) {
			$ancestry_domain = $domain[WT_LOCALE];
		} else {
			$ancestry_domain = $domain['en_US'];
		}
		$url = 'http://search.ancestry.' . $ancestry_domain . '/cgi-bin/sse.dll?new=1&gsfn=' . $givn.'&gsln=' . $surname . '&msbdy=' . $birth_year . '&gl=ROOT_CATEGORY&rank=1';
		$collection = array(
				"All Collections"	=>"0",
				"Australia"			=>"2",
				"Canada"			=>"3",
				"England"			=>"4",
				"France"			=>"5",
				"Germany"			=>"6",
				"Ireland"			=>"8",
				"Italy"				=>"7",
				"New Zealand"		=>"14",
				"Scotland"			=>"9",
				"Sweden"			=>"10",
				"UK & Ireland"		=>"11",
				"United States"		=>"12",
				"Wales"				=>"13",
				"African American"	=>"100",
				"Jewish"			=>"101",
				"Native American"	=>"102",

			);

		foreach($collection as $x=>$x_value) {
			$link[] = array(
				'title' => WT_I18N::translate($x),
				'link'  => $url. '&cp=' . $x_value
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
