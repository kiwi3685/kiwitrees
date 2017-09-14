<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class googlewide_plugin extends research_base_plugin {
	static function getName() {
		return 'Google wide search';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'INT';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return false;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		$base_url = 'https://google.com/';

		$collection = array(
		"All"					=> '#q=' . urlencode('"') . $first . '%20' . $surname . urlencode('"') . '',
		"Genealogy search"		=> 'search?q=genealogy&as_epq=' . urlencode('"') . $first . '%20' . $surname . urlencode('"') . '&as_oq=&as_eq=&as_nlo=&as_nhi=&lr=&cr=&as_qdr=all&as_sitesearch=&as_occt=any&safe=images&as_filetype=&as_rights=',
		"Family tree search"	=> 'search?q=family+tree&as_epq=' . urlencode('"') . $first . '%20' . $surname . urlencode('"') . '&as_oq=&as_eq=&as_nlo=&as_nhi=&lr=&cr=&as_qdr=all&as_sitesearch=&as_occt=any&safe=images&as_filetype=&as_rights=',
		"Images"				=> 'search?q=' . urlencode('"') . $first . '%20' . $surname . urlencode('"') . '&tbm=isch',
		"News"					=> 'search?q=' . urlencode('"') . $first . '%20' . $surname . urlencode('"') . '&tbm=nws',
		"Videos"				=> 'search?q=' . urlencode('"') . $first . '%20' . $surname . urlencode('"') . '&tbm=vid',
		"Books"					=> 'search?q=' . urlencode('"') . $first . '%20' . $surname . urlencode('"') . '&tbm=bks',
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
