<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class cwgc_plugin extends research_base_plugin {
	static function getName() {
		return 'Commonwealth War Graves Commission';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'GBR';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		// This is a post form, so it will be sent with Javascript
		$url	 	= 'http://www.cwgc.org/find-war-dead.aspx?cpage=1';
		$params	 	= array(
			'ctl00$ctl00$ctl00$ContentPlaceHolderDefault$cpMain$ctlHomepageCasualtySearch$txtName'				=> $fullname,
			'ctl00$ctl00$ctl00$ContentPlaceHolderDefault$cpMain$ctlHomepageCasualtySearch$btnCasualtySearch'	=> 'Search',
		);
		return "postresearchform('" . $url . "'," . json_encode($params) . ")";
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
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
