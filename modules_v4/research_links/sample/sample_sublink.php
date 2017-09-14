<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class sample_sublink_plugin extends research_base_plugin {
	static function getName() {
		return 'Sample sublink';
	}

	static function getPaySymbol() {
		return true;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return false;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		$base_url = 'http://www.delpher.nl/';

		$collection = array(
			"Kranten"			=> 'nl/kranten/results?query=' . urlencode('"') . $fullname . urlencode('"') . '&coll=ddd',
			"Boeken Basis"		=> 'nl/boeken/results?query=' . urlencode('"') . $fullname . urlencode('"') . '&page=1&coll=boeken',
			"Boeken Google"		=> 'nl/boeken1/results?query=' . urlencode('"') . $fullname . urlencode('"') . '&page=1&coll=boeken1',
			"Tijdschriften"		=> 'nl/tijdschriften/results?query=' . urlencode('"') . $fullname . urlencode('"') . '&page=1&coll=dts',
			"Radiobulletins"	=> 'nl/radiobulletins/results?query=' . urlencode('"') . $fullname . urlencode('"') . '&page=1&coll=anp',
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
