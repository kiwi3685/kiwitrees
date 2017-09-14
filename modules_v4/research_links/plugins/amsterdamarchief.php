<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class amsterdamarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Amsterdam Archief';
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
		$base_url = 'https://archief.amsterdam/indexen/';

		$collection = array(
				"Archiefkaarten 1939-1994"			=> "archiefkaarten_1939-1994/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=1&z=b",
				"Averijgrossen 1700-1810"			=> "averijgrossen_1700-1810/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=32&z=b",
				"Begraafregisters 1553-1811"		=> "begraafregisters_1553-1811/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=12&z=b",
				"Begraafplaatsen 1660-2010"			=> "begraafplaatsen_1660-2010/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=2&z=b",
				"Bevolkingsregisters 1851-1853"		=> "bevolkingsregisters_1851-1853/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=17&z=b",
				"Bevolkingsregisters 1853-1863"		=> "bevolkingsregisters_1853-1863/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=37&z=b",
				"Bevolkingsregisters 1874-1893"		=> "bevolkingsregisters_1874-1893/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=22&z=b",
				"Overgenomen Delen 1892-1920"		=> "overgenomen_delen_1892-1920/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=25&z=b",
				"Tijdelijk Verblijf 1854-1934"		=> "tijdelijk_verblijf_1854-1934/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=33&z=b",
				"Boedelpapieren 1634-1938"			=> "boedelpapieren_1634-1938/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=9&z=b",
				"Trouw- en Begraafboete 1685-1795"	=> "trouw-_en_begraafboete_1685-1795/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=27&z=b",
				"Comportementboeken 1785-1971"		=> "comportementboeken_1785-1971/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=25&z=b",
				"Confessieboeken 1535-1732"			=> "confessieboeken_1535-1732/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=26&z=b",
				"Doopregisters 1564-1811"			=> "doopregisters_1564-1811/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=19&z=b",
				"Gezinskaarten 1893-1939"			=> "gezinskaarten_1893-1939/zoek/query.nl.pl?i1=1&a1=" . $surname . "&x=3&z=b",
				"Huiszittenhuizen 1808-1870"		=> "huiszittenhuizen_1808-1870/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=4&z=b",
				"Lidmaten Doopsgezinden 1668-1829"	=> "lidmaten_doopsgezinden_1668-1829/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=28&z=b",
				"Marktkaarten 1922-1954"			=> "marktkaarten_1922-1954/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=10&z=b",
				"Ondertrouwregisters 1565-1811"		=> "ondertrouwregisters_1565-1811/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=20&z=b",
				"Overledenen Gasthuis 1739-1812"	=> "overledenen_gasthuis_1739-1812/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=30&z=b",
				"Paspoorten 1940-1945"				=> "paspoorten_1940-1945/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=6&z=b",
				"Patientenregisters 1818-1899"		=> "patientenregisters_1818-1899/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=34&z=b",
				"Pensioenkaarten 1894-1915"			=> "pensioenkaarten_1894-1915/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=36&z=b",
				"Persoonskaarten 1939-1994"			=> "persoonskaarten_1939-1994/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=11&z=b",
				"Politierapporten 1940-1945"		=> "politierapporten_1940-1945/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=16&z=b",
				"Poorters 1531-1652"				=> "poorters_1531-1652/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=29&z=b",
				"Signalementenregister 1880-1917"	=> "signalementenregister_1880-1917/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=7&z=b",
				"Tewerkgestelden 1940-1945"			=> "tewerkgestelden_1940-1945/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=5&z=b",
				"Transportakten 1563-1811"			=> "transportakten_1563-1811/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=14&z=b",
				"Vreemdelingenregisters 1849-1922"	=> "vreemdelingenregisters_1849-1922/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=21&z=b",
				"Waterloo Gratificaties 1817-1819"	=> "waterloo_gratificaties_1817-1819/zoek/query.nl.pl?i1=1&v1=" . $givn . "&a1=" . $surname . "&x=24&z=b",
				"Woningkaarten 1924-1989"			=> "woningkaarten_1924-1989/zoek/query.nl.pl?i1=3&u1=e&F1=add adress&x=15&z=b",
			);
		foreach($collection as $x => $x_value) {
				$link[] = array(
					'title' => KT_I18N::translate($x),
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
