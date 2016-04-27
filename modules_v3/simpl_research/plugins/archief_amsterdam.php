<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class archief_amsterdam_plugin extends research_base_plugin {
	static function getName() {
		return 'Archief Amsterdam';
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
		$base_url = 'https://archief.amsterdam/indexen/';
		$url			= '/zoek/query.nl.pl?i1=1&v1=' . $givn . '&a1=' . $surname . '&x=25&z=a';

		$collection = array(
				"Archiefkaarten 1939-1994"				=>"archiefkaarten_1939-1994",
				"Averijgrossen 1700-1810"					=>"averijgrossen_1700-1810",
				"Begraafregisters 1553-1811"			=>"begraafregisters_1553-1811",
				"Begraafplaatsen 1660-2010"				=>"begraafplaatsen_1660-2010",
				"Bevolkingsregisters 1851-1853"		=>"bevolkingsregisters_1851-1853",
				"Bevolkingsregisters 1853-1863"		=>"bevolkingsregisters_1853-1863",
				"Bevolkingsregisters 1874-1893"		=>"bevolkingsregisters_1874-1893",
				"Overgenomen Delen 1892-1920"			=>"overgenomen_delen_1892-1920",
				"Tijdelijk Verblijf 1854-1934"		=>"tijdelijk_verblijf_1854-1934",
				"Boedelpapieren 1634-1938"				=>"boedelpapieren_1634-1938",
				"Trouw- en Begraafboete 1685-1795"=>"trouw-_en_begraafboete_1685-1795",
				"Comportementboeken 1785-1971"		=>"comportementboeken_1785-1971",
				"Confessieboeken 1535-1732"				=>"confessieboeken_1535-1732",
				"Doopregisters 1564-1811"					=>"doopregisters_1564-1811",
				"Gezinskaarten 1893-1939"					=>"gezinskaarten_1893-1939",
				"Huiszittenhuizen 1808-1870"			=>"huiszittenhuizen_1808-1870",
				"Lidmaten Doopsgezinden 1668-1829"=>"lidmaten_doopsgezinden_1668-1829",
				"Marktkaarten 1922-1954"					=>"marktkaarten_1922-1954",
				"Ondertrouwregisters 1565-1811"		=>"ondertrouwregisters_1565-1811",
				"Overledenen Gasthuis 1739-1812"	=>"overledenen_gasthuis_1739-1812",
				"Paspoorten 1940-1945"						=>"paspoorten_1940-1945",
				"Patientenregisters 1818-1899"		=>"patientenregisters_1818-1899",
				"Pensioenkaarten 1894-1915"				=>"pensioenkaarten_1894-1915",
				"Persoonskaarten 1939-1994"				=>"persoonskaarten_1939-1994",
				"Politierapporten 1940-1945"			=>"politierapporten_1940-1945",
				"Poorters 1531-1652"							=>"poorters_1531-1652",
				"Signalementenregister 1880-1917"	=>"signalementenregister_1880-1917",
				"Tewerkgestelden 1940-1945"				=>"tewerkgestelden_1940-1945",
				"Transportakten 1563-1811"				=>"transportakten_1563-1811",
				"Vreemdelingenregisters 1849-1922"=>"vreemdelingenregisters_1849-1922",
				"Waterloo Gratificaties 1817-1819"=>"waterloo_gratificaties_1817-1819",
				"Woningkaarten 1924-1989"					=>"woningkaarten_1924-1989",
			);

		foreach($collection as $x=>$x_value) {
				$link[] = array(
					'title' => WT_I18N::translate($x),
					'link'  => $base_url. $x_value . $url
				);
			}
			return $link;
	}

	static function encode_plus() {
		return false;
	}
}
