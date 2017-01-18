<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class google_plugin extends research_base_plugin {
	static function getName() {
		return 'Google';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'INT';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
	   return false;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		$base_url = 'https://google.com/';

	   $collection = array(
	   "All"                  => '#q=' . urlencode('"') . $fullname . urlencode('"') . '',
	   "Images"               => 'search?q=' . urlencode('"') . $fullname . urlencode('"') . '&tbm=isch',
	   "News"                 => 'search?q=' . urlencode('"') . $fullname . urlencode('"') . '&tbm=nws',
	   "Videos"               => 'search?q=' . urlencode('"') . $fullname . urlencode('"') . '&tbm=vid',
	   "Books"                => 'search?q=' . urlencode('"') . $fullname . urlencode('"') . '&tbm=bks',
	   "Advanced search"      => 'search?q=genealogy&as_epq=' . urlencode('"') . $fullname . urlencode('"') . '&as_oq=&as_eq=&as_nlo=&as_nhi=&lr=&cr=&as_qdr=all&as_sitesearch=&as_occt=any&safe=images&as_filetype=&as_rights='
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

	static function encode_plus() {
		return true;
	}
}
