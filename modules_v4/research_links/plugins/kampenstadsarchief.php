<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class kampenstadsarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Kampen Stadsarchief';
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
		$base_url = 'http://www.stadsarchiefkampen.nl/';

		$collection = array(
		    "Doopinschrijving"      => "direct-zoeken-2/doorzoek-alles-2?mivast=69&mizig=100&miadt=69&miq=1&milang=nl&misort=last_mod%7Cdesc&mizk_alle=$givn%20$surname&mif1=156&miview=tbl",
		    "Notariele akte"        => "direct-zoeken-2/doorzoek-alles-2?mivast=69&mizig=100&miadt=69&miq=1&milang=nl&misort=last_mod%7Cdesc&mizk_alle=$givn%20$surname&mif1=224&miview=tbl",
		    "Volkstelling 1795"     => "direct-zoeken-2/doorzoek-alles-2?mivast=69&mizig=100&miadt=69&miq=1&milang=nl&misort=last_mod%7Cdesc&mizk_alle=$givn%20$surname&mif1=112&miview=tbl",
		    "Lidmateninschrijving"  => "direct-zoeken-2/doorzoek-alles-2?mivast=69&mizig=100&miadt=69&miq=1&milang=nl&misort=last_mod%7Cdesc&mizk_alle=$givn%20$surname&mif1=577&miview=tbl",
		    "Krantenartikelen"      => "direct-zoeken-2/doorzoek-alles-2?mivast=69&mizig=222&miadt=69&milang=nl&mizk_alle=$givn%20$surname&miview=ldt",
		    "Beeldbank"             => "direct-zoeken-2/doorzoek-alles-2?mivast=69&miadt=69&mizig=216&miview=gal1&milang=nl&micols=3&mires=0&mizk_alle=$surname",
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
