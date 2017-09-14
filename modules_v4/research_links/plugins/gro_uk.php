<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class gro_uk_plugin extends research_base_plugin {
	static function getName() {
		return 'General Register Office';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'GBR';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return false;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		// This is a post form, so it will be sent with Javascript
		$url = 'https://www.gro.gov.uk/gro/content/certificates/indexes_search.asp';

		$params_birth = array(
			'index'					=> 'EW_Birth',
			'Surname'				=> $surn,
			'SurnameMatches'		=> '4', // "Similar sounding variations"
			'Forename1'				=> $first,
			'ForenameMatches'		=> '0',
			'Gender'				=> $gender,
			'MothersSurnameMatches'	=> '0',
			'Year'					=> $birth_year,
			'Range'					=> '2',
			'SearchIndexes'			=> 'Search',
		);

		$params_death = array(
			'index'					=> 'EW_Death',
			'Surname'				=> $surn,
			'SurnameMatches'		=> '4', // "Similar sounding variations"
			'Forename1'				=> $first,
			'ForenameMatches'		=> '0',
			'Gender'				=> $gender,
			'MothersSurnameMatches'	=> '0',
			'Year'					=> $death_year,
			'Range'					=> '2',
			'SearchIndexes'			=> 'Search',
		);

		$collection = array(
			"Birth"	=> json_encode($params_birth),
			"Death"	=> json_encode($params_death),
		);

		foreach($collection as $key => $value) {
			$link[] = array(
				'title' => KT_I18N::translate($key),
				'link'  => "postresearchform('" . $url . "'," . $value . ")"
			);
		}

		return  $link;
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
