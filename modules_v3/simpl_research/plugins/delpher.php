<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class delpher_plugin extends research_base_plugin {
	static function getName() {
		return 'Delpher';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = '#';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		$base_url = 'http://www.delpher.nl/';
		$collection = array(
			"Kranten"			=> 'nl/kranten/results?query=' . urlencode('"') . $fullname . urlencode('"') . '&coll=ddd',
			"Boeken Basis"		=> 'nl/boeken/results?query=' . urlencode('"') . $fullname . urlencode('"') . '&page=1&coll=boeken',
			"Boeken Google"		=> 'nl/boeken1/results?query=' . urlencode('"') . $fullname . urlencode('"') . '&page=1&coll=boeken1',
			"Tijdschriften"		=> 'nl/tijdschriften/results?query=' . urlencode('"') . $fullname . urlencode('"') . '&page=1&coll=dts',
			"Radiobulletins"	=> 'nl/radiobulletins/results?query=' . urlencode('"') . $fullname . urlencode('"') . '&page=1&coll=anp',
		);
		foreach($collection as $x => $x_value) {
			$link[] = array(
				'title' => WT_I18N::translate($x),
				'link'  => $base_url. $x_value
			);
		}
		return $link;
	}

	static function createLinkOnly() {
		return false;
	}

	static function encode_plus() {
		return false;
	}
}
