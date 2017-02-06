<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class find_a_grave_plugin extends research_base_plugin {
	static function getName() {
		return 'Find a Grave';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'INT';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		return $link = 'http://www.findagrave.com/cgi-bin/fg.cgi?page=gsr&GSfn=' . $first . '&GSmn=' . $middle . '&GSln=' . $surname				. '&GSbyrel=all&GSby=&GSdyrel=all&GSdy=&GScntry=0&GSst=0&GSgrid=&df=all&GSob=n';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		return false;
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
