<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class cbgbibliotheek_plugin extends research_base_plugin {
	static function getName() {
		return 'CBG Bibliotheek';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return false;
	}
	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		$base_url = 'http://cbgbibliotheek.nl/';

		$collection = array(
		'Bibliotheek'				=> 'zoeken?search=' . $surname . '&collection=Bibliotheek',
		'Biografische index'		=> 'zoeken?search=' . $surname . '&collection=Biografische+index',
		'Genealogisch repertorium'	=> 'zoeken?search=' . $surname . '&collection=Genealogisch+repertorium',

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

	static function createSubLinksOnly() {
		return false;
	}

	static function encode_plus() {
		return false;
	}
}
