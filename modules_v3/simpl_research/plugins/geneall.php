<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class geneall_plugin extends research_base_plugin {
	static function getName() {
		return 'Geneall';
	}

	static function getPaySymbol() {
		return true;
	}

	static function getSearchArea() {
		return 'INT';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		// this plugin needs refactoring. Multiple websites for multiple country categories. Not on a per language base. See: http://www.geneall.net/site/home.php
		$languages = array(
			'de'	 => 'de',
			'en_GB'	 => 'en',
			'en_US'	 => 'en',
			'fr'	 => 'fr',
			'it'	 => 'it',
			'es'	 => 'es',
			'pt'	 => 'pt',
			'pt_BR'	 => 'pt',
		);

		if (isset($languages[WT_LOCALE])) {
			$language = $languages[WT_LOCALE];
		} else {
			$language = $languages['en_US'];
		}

		return $link = 'http://geneall.net/' . $language . '/search/?s=' . $fullname . '&t=p';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		return false;
	}

	static function createLinkOnly() {
		return false;
	}

	static function createSubLinksOnly() {
		return false;
	}

	static function encode_plus() {
		return true;
	}
}
