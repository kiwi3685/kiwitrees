<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class eindhovenrhc_plugin extends research_base_plugin { // THIS NAME MUST MATCH EXACTLY TO THE FILE NAME AND SHOULD BE SIMILAR TO THE DISPLAY NAME FOR BEST SORTING
	static function getName() {
		return 'Eindhoven RHC'; // THIS IS THE DISPLAY NAME
	}

	static function getPaySymbol() {
		return false; // USE 'true' IF THE LINK IS PAY TO VIEW, FALSE IF NOT
	}

	static function getSearchArea() {
		return 'NLD'; //3-LETTER INTERNATIONAL CODE FOR LOCATION THE LINK RELATES TO. USE 'INT' FOR INTERNATIONAL OR MULTI-COUNTRY DATA
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) { // SEE FAQ FOR EXPLANATION OF EACH PART
		return $link = 'http://www.zoekjestamboom.nl/zoek-uitgebreid/7/Personen?mip3='. $givn . '&mip1=' . $surname . '';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return false; // NOT NORMALLY USED. LEAVE AS false FOR SIMPLE LINKS
	}

	static function encode_plus() {
		return false; // NORMALLY LEFT AS false. USE true IF SITE REQUIRES ENCODING '+' BETWEEN NAMES INSTEAD OF '%20'
	}

}
