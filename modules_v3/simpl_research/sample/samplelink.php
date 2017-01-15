<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class samplelink_plugin extends research_base_plugin { // THIS NAME MUST MATCH EXACTLY TO THE FILE NAME AND SHOULD BE SIMILAR TO THE DISPLAY NAME FOR BEST SORTING
	static function getName() {
		return 'Sample Link'; // THIS IS THE DISPLAY NAME
	}

	static function getPaySymbol() {
		return true; // USE 'true' IF THE LINK IS PAY TO VIEW, FALSE IF NOT
	}

	static function getSearchArea() {
		return 'NLD'; //3-LETTER INTERNATIONAL CODE FOR LOCATION THE LINK RELATES TO. USE 'INT' FOR INTERNATIONAL OR MULTI-COUNTRY DATA
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		// SEE FAQ FOR EXPLANATION OF EACH PART
		//Replace next line with return '#'; if creating either a plugin with sub-links or a plugin for links only
		return $link = 'http://militieregisters.nl/zoek#?focus%3Dd00%26p04%3D' . $givn . '%26p05%3D' . $prefix . '%26p06%3D' . $surname;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		return false; // NOT NORMALLY USED. LEAVE AS false FOR SIMPLE LINKS
	}

	static function createLinkOnly() {
		return false;
		// NOT NORMALLY USED. LEAVE AS false FOR SIMPLE LINKS
		// OR REPLACE WITH FORM LIKE FOLLOWING FOR A "LINKS ONLY" PLUGIN
//		$base_url = 'http://zoekakten.nl/';

//		$collection = array(
//		    "Groningen"               => "prov.php?id=GR",
//		    "Friesland"               => "prov.php?id=FR",
//		    "Drenthe"                 => "prov.php?id=DR",
//		);
//
//		foreach($collection as $x => $x_value) {
//			$link[] = array(
//				'title' => WT_I18N::translate($x),
//				'link'  => $base_url. $x_value
//			);
//		}
//		return $link;
	}

	static function encode_plus() {
		return false; // NORMALLY LEFT AS false. USE true IF SITE REQUIRES ENCODING '+' BETWEEN NAMES INSTEAD OF '%20'
	}

}
