<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class belgianwardeadregister_plugin extends research_base_plugin {
	static function getName() {
		return 'Belgian War Dead Register';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'BEL';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = 'http://www.wardeadregister.be/nl/wardead-register?field_conflict_tid=All&field_familienaam_value=' . $surname . '&field_voornaamnl_value=' . $first . '&field_overledenvermistopdateaaff_value%5Bvalue%5D%5Bdate%5D=&field_overledenvermistte__tid=&field_plaatsvanherbegravingnl_tid=&field_plaatsvanherbegravingfr_tid=&field_plaatsvanherbegravinggb_tid=&field_plaatsvanherbegravingal__tid=&field_extra_1_value=&field_extra_2_value=';
	}

	static function create_sublink() {
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
