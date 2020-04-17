<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class langstraatheusdenaltenasa_plugin extends research_base_plugin {
	static function getName() {
		return 'Langstraat Heusden Altena SA';
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
		$base_url = 'https://salha.nl/';

		$collection = array(
			"Bevolkingsregister"				=> "bronnen/genealogy/personen/persons?f=%7B%22search_s_register_type_title%22:%7B%22v%22:%22Bevolkingsregister%22%7D%7D&sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D%7D",
			"Burgerlijke standregister"			=> "bronnen/genealogy/personen/persons?f=%7B%22search_s_register_type_title%22:%7B%22v%22:%22Burgerlijke%20standregister%22%7D%7D&sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D%7D",
			"DTB registers"			            => "bronnen/genealogy/personen/persons?f=%7B%22search_s_register_type_title%22:%7B%22v%22:%22Doop,%20Trouw%20en%20Begraaf%20Registers%22%7D%7D&sa=%7B%22person_1%22:%7B%22search_t_geslachtsnaam%22:%22$surn%22,%22search_t_voornaam%22:%22$givn%22%7D%7D",
			"Notaris-en schepenakten"	=> "bronnen/genealogy/notaris-en-schepenakten?mivast=128&miadt=128&mizig=197&miview=tbl&milang=nl&micols=1&mip3=$surn&mip2=$givn",
		);

		foreach($collection as $key => $value) {
			$link[] = array(
				'title' => KT_I18N::translate($key),
				'link'	=> $base_url . $value
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
