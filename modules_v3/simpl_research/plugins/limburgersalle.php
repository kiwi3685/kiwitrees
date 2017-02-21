<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class limburgersalle_plugin extends research_base_plugin {
	static function getName() {
		return 'Limburg Alle Limburgers';
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
		$base_url = 'http://www.allelimburgers.nl/';

		$collection = array(
			"Geboorteaangifte"		      => "wgpublic/persoonu.php?&search_fd5=%3D%3D$surn&search_fd6=%3D%3D$givn&multisearch_fd6=7,8&search_fd12=200&multisearch_fd16=17&multisearch_fd20=21,22",
			"Burgelijk Huwelijk " 	      => "wgpublic/persoonu.php?&search_fd5=%3D%3D$surn&search_fd6=%3D%3D$givn&multisearch_fd6=7,8&search_fd12=220&multisearch_fd16=17&multisearch_fd20=21,22",
			"Overlijdensaangifte "	      => "wgpublic/persoonu.php?&search_fd5=%3D%3D$surn&search_fd6=%3D%3D$givn&multisearch_fd6=7,8&search_fd12=240&multisearch_fd16=17&multisearch_fd20=21,22",
			"Dopen"	                      => "wgpublic/persoonu.php?&search_fd5=%3D%3D$surn&search_fd6=%3D%3D$givn&multisearch_fd6=7,8&search_fd12=100&multisearch_fd16=17&multisearch_fd20=21,22",
			"Kerkelijk Huwelijk"	      => "wgpublic/persoonu.php?&search_fd5=%3D%3D$surn&search_fd6=%3D%3D$givn&multisearch_fd6=7,8&search_fd12=120&multisearch_fd16=17&multisearch_fd20=21,22",
			"Begraven"                    => "wgpublic/persoonu.php?&search_fd5=%3D%3D$surn&search_fd6=%3D%3D$givn&multisearch_fd6=7,8&search_fd12=140&multisearch_fd16=17&multisearch_fd20=21,22",
			"Communie"                    => "wgpublic/persoonu.php?&search_fd5=%3D%3D$surn&search_fd6=%3D%3D$givn&multisearch_fd6=7,8&search_fd12=110&multisearch_fd16=17&multisearch_fd20=21,22",
			"Bevolkingsregister"          => "wgpublic/persoonu.php?&search_fd5=%3D%3D$surn&search_fd6=%3D%3D$givn&multisearch_fd6=7,8&search_fd12=280&multisearch_fd16=17&multisearch_fd20=21,22",
			"Kerkelijk Huwelijk Modern"   => "wgpublic/persoonu.php?&search_fd5=%3D%3D$surn&search_fd6=%3D%3D$givn&multisearch_fd6=7,8&search_fd12=121&multisearch_fd16=17&multisearch_fd20=21,22",
			
		);

		foreach($collection as $key => $value) {
			$link[] = array(
				'title' => WT_I18N::translate($key),
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
