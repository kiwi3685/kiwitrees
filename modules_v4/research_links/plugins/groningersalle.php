<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class groningersalle_plugin extends research_base_plugin {
	static function getName() {
		return 'Groningen Alle Groningers';
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
		$base_url = 'http://www.allegroningers.nl/';

		$collection = array(
		"Huwelijkscontract"       => "zoeken-op-naam/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22Huw.%20contract%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
		"Begraafakte"             => "zoeken-op-naam/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22begraafakte%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
		"Doopakte"                => "zoeken-op-naam/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22doopakte%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
		"Geboorteakte"            => "zoeken-op-naam/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22geboorteakte%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
		"Huwelijksakte"           => "zoeken-op-naam/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22huwelijksakte%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
		"Overlijdensakte"         => "zoeken-op-naam/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22overlijdensakte%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
		"Registratie"             => "zoeken-op-naam/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22registratie%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
		"Trouwakte (tot 1811)"    => "zoeken-op-naam/persons?f=%7B%22search_s_deed_type_title%22:%7B%22v%22:%22trouwakte%20(tot%201811)%22%7D%7D&ss=%7B%22q%22:%22$givn%20$surname%22%7D",
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
