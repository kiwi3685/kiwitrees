<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class _1939register_plugin extends research_base_plugin {
	static function getName() {
		return '1939 Register';
	}

	static function getPaySymbol() {
		return true;
	}

	static function getSearchArea() {
		return 'GBR';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		return false;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year, $gender) {
		$options = array(
				'FindMyPast'	=>'http://search.findmypast.com/results/world-records/1939-register?firstname=' . $givn . '&firstname_variants=true&lastname=' . $surn . '&lastname_variants=true&yearofbirth=' . $birth_year,
				'Ancestry'		=>'https://search.ancestry.co.uk/cgi-bin/sse.dll?db=1939UKRegister&gss=sfs28_ms_db&new=1&rank=1&msT=1&gsfn=' . $givn . '&gsfn_x=0&gsln=' . $surn . '&gsln_x=0&msbdy=' . $birth_year . '&MSAV=1&uidh=lp3',
			);

		foreach($options as $x=>$x_value) {
			$link[] = array(
				'title' => KT_I18N::translate($x),
				'link'  => $x_value
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
