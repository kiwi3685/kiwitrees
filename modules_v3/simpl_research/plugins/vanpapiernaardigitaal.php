<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class vanpapiernaardigitaal_plugin extends research_base_plugin {
	static function getName() {
		return 'Van Papier naar Digitaal';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		return false;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year, $death_year) {
		return false;
	}

	static function createLinkOnly() {
		return false;
	}

	static function createSubLinksOnly() {
		$base_url = 'http://www.vpnd.nl';

		$collection = array(
			"Groningen"					=> "/statuspagina-gr.html",
			"Friesland"					=> "/statuspagina-fr.html",
			"Drenthe"					=> "/statuspagina-dr.html",
			"Overijssel"				=> "/statuspagina-ov.html",
			"Gelderland"				=> "/statuspagina-ge.html",
			"Flevoland"					=> "/statuspagina-fl.html",
			"Utrecht"					=> "/statuspagina-ut.html",
			"Noord-Holland"				=> "/statuspagina-nh.html",
			"Zuid-Holland"				=> "/statuspagina-zh.html",
			"Zeeland"					=> "/statuspagina-ze.html",
			"Noord-Brabant"				=> "/statuspagina-nb.html",
			"Limburg"					=> "/statuspagina-li.html",
			"Nederland algemeen"		=> "/statuspagina-nl_alg.html",
			"Overzeese gebiedsdelen"	=> "/statuspagina-og.html",
		);

		foreach($collection as $key => $value) {
			$link[] = array(
				'title' => WT_I18N::translate($key),
				'link'  => $base_url. $value
			);
		}

		return $link;
	}

	static function encode_plus() {
		return false;
	}

}
