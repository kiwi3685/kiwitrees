<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class middenhollandstreekarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Midden Holland Streekarchief';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		return false;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		$base_url = 'http://www.samh.nl/';

		$collection = array(
		    
		    "Archief"       => "hs_search/?q=$givn%20$surname&selected_facets=object_type_exact:Archiefstuk",
		    "Beeldbank"     => "hs_search/?q=$givn%20$surname&selected_facets=object_type_exact:Beeldbank",
		    "Genealogie"    => 'hs_search/?q=' . urlencode('"') . $givn . '%20' . $surname . urlencode('"') . '&selected_facets=object_type_exact:Genealogie%20Document',
		    "Kranten"       => 'hs_search/?q=' . urlencode('"') . $givn . '%20' . $surname . urlencode('"') . '&selected_facets=object_type_exact:Krant',
		    
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
