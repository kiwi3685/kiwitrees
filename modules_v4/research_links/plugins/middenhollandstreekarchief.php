<?php

if (!defined('KT_KIWITREES')) {
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

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return false;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		$base_url = 'http://www.samh.nl/';

		$collection = array(
		    
		    "Archief"            => "search/?q=$givn%20$surname&qf[]=nave_objectSoort%3AArchief",
		    "Beeldmateriaal"     => "search/?q=$givn%20$surname&qf[]=nave_objectSoort%3ABeeldmateriaal",
		    "Genealogie"         => 'search/?q=' . urlencode('"') . $givn . '%20' . $surname . urlencode('"') . '&qf[]=nave_objectSoort%3AGenealogie',
		    "Kranten"            => 'search/?q=' . urlencode('"') . $givn . '%20' . $surname . urlencode('"') . '&qf[]=nave_objectSoort%3AKranten',
		    
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
