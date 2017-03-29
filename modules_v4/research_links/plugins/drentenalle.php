<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class drentenalle_plugin extends research_base_plugin {
	static function getName() {
		return 'Drenthe Alle Drenten';
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
		$base_url = 'http://alledrenten.nl/';

		$collection = array(
		"Burgelijke stand"           =>  "na-1811/burgerlijke-stand-1811-1962?f%5Bs%5D%5B36%5D=0&f%5Bs%5D%5B36%5D=1&f%5Bs%5D%5B37%5D=0&f%5Bs%5D%5B37%5D=1&f%5Bs%5D%5B38%5D=0&f%5Bs%5D%5B38%5D=1&f%5Bsf%5D%5B41%5D%5Bt%5D=$surn&f%5Bsf%5D%5B41%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B42%5D%5Bt%5D=$givn&f%5Bsf%5D%5B42%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B43%5D%5Bt%5D=&f%5Bsf%5D%5B44%5D%5Bt%5D=&f%5Bsf%5D%5B44%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B45%5D%5Bt%5D=&f%5Bsf%5D%5B45%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B46%5D%5Bt%5D=&f%5Bsf%5D%5B47%5D%5Bt%5D=&f%5Bsf%5D%5B48%5D%5Bf%5D=1811&f%5Bsf%5D%5B48%5D%5Bu%5D=1962",
		"Bevolkingsregisters"        => "na-1811/bevolkingsregisters?f%5Bs%5D%5B35%5D=0&f%5Bs%5D%5B35%5D=1&f%5Bsf%5D%5B166%5D%5Bt%5D=$surn&f%5Bsf%5D%5B166%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B167%5D%5Bt%5D=&f%5Bsf%5D%5B167%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B223%5D%5Bf%5D=&f%5Bsf%5D%5B223%5D%5Bu%5D=&f%5Bsf%5D%5B168%5D%5Bt%5D=&f%5Bsf%5D%5B169%5D%5Bf%5D=1800&f%5Bsf%5D%5B169%5D%5Bu%5D=1946prov.php?id=GR",
		"Notariele akten"            => "na-1811/notariele-akten-1810-1915?f%5Bs%5D%5B52%5D=0&f%5Bs%5D%5B52%5D=1&f%5Bsf%5D%5B217%5D%5Bt%5D=$surn&f%5Bsf%5D%5B217%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B218%5D%5Bt%5D=$givn&f%5Bsf%5D%5B218%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B219%5D%5Bt%5D=&f%5Bsf%5D%5B236%5D%5Bf%5D=1810&f%5Bsf%5D%5B236%5D%5Bu%5D=1915",
		"Successiememories"          => "na-1811/successiememories-1806-1928?f%5Bs%5D%5B48%5D=0&f%5Bs%5D%5B48%5D=1&f%5Bsf%5D%5B180%5D%5Bt%5D=$surn&f%5Bsf%5D%5B180%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B181%5D%5Bt%5D=$givn&f%5Bsf%5D%5B181%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B222%5D%5Bt%5D=&f%5Bsf%5D%5B182%5D%5Bt%5D=&f%5Bsf%5D%5B183%5D%5Bf%5D=1806&f%5Bsf%5D%5B183%5D%5Bu%5D=1928",
		"Veenhuizen/Ommerschans"     => "na-1811/veenhuizen-ommerschans?f%5Bs%5D%5B45%5D=0&f%5Bs%5D%5B45%5D=1&f%5Bs%5D%5B57%5D=0&f%5Bs%5D%5B57%5D=1&f%5Bsf%5D%5B252%5D%5Bt%5D=$surn&f%5Bsf%5D%5B252%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B253%5D%5Bt%5D=$givn&f%5Bsf%5D%5B253%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B265%5D%5Bt%5D=&f%5Bsf%5D%5B256%5D%5Bf%5D=&f%5Bsf%5D%5B256%5D%5Bu%5D=",
		"Kolonisten database"        => "na-1811/maatschappij-van-weldadigheid?f%5Bs%5D%5B53%5D=0&f%5Bs%5D%5B53%5D=1&f%5Bsf%5D%5B226%5D%5Bt%5D=$surn&f%5Bsf%5D%5B226%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B227%5D%5Bt%5D=$givn&f%5Bsf%5D%5B227%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B228%5D%5Bt%5D=&f%5Bsf%5D%5B230%5D%5Bt%5D=&f%5Bsf%5D%5B243%5D%5Bf%5D=1748&f%5Bsf%5D%5B243%5D%5Bu%5D=1904",   
		"Kerkregisters 1600-1811"    => "1600-1811/kerkregisters-1600-1811?f%5Bs%5D%5B50%5D=0&f%5Bs%5D%5B50%5D=1&f%5Bs%5D%5B41%5D=0&f%5Bs%5D%5B41%5D=1&f%5Bs%5D%5B51%5D=0&f%5Bs%5D%5B51%5D=1&f%5Bsf%5D%5B189%5D%5Bt%5D=$surn&f%5Bsf%5D%5B189%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B190%5D%5Bt%5D=&f%5Bsf%5D%5B190%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B191%5D%5Bt%5D=$givn&f%5Bsf%5D%5B191%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B198%5D%5Bt%5D=&f%5Bsf%5D%5B192%5D%5Bt%5D=&f%5Bsf%5D%5B192%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B193%5D%5Bt%5D=&f%5Bsf%5D%5B193%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B194%5D%5Bt%5D=&f%5Bsf%5D%5B194%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B199%5D%5Bt%5D=&f%5Bsf%5D%5B195%5D%5Bt%5D=&f%5Bsf%5D%5B196%5D%5Bf%5D=1600&f%5Bsf%5D%5B196%5D%5Bu%5D=1811",
		"30e/40e Penning          "  => "1600-1811/30e-40e-penning-1682-1797?f%5Bs%5D%5B49%5D=0&f%5Bs%5D%5B49%5D=1&f%5Bsf%5D%5B257%5D%5Bt%5D=$surn&f%5Bsf%5D%5B257%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B186%5D%5Bt%5D=&f%5Bsf%5D%5B186%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B187%5D%5Bt%5D=$givn&f%5Bsf%5D%5B187%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B188%5D%5Bf%5D=1679&f%5Bsf%5D%5B188%5D%5Bu%5D=1797&f%5Bsf%5D%5B204%5D%5Bt%5D=&f%5Bsf%5D%5B204%5D%5Bh%5D%5Bm%5D=default",
		"Haardstedegeld"  => "1600-1811/haardstedegeld-1672-1804?f%5Bs%5D%5B42%5D=0&f%5Bs%5D%5B42%5D=1&f%5Bsf%5D%5B207%5D%5Bt%5D=$surn&f%5Bsf%5D%5B207%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B208%5D%5Bt%5D=&f%5Bsf%5D%5B208%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B209%5D%5Bt%5D=$givn&f%5Bsf%5D%5B209%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B210%5D%5Bt%5D=&f%5Bsf%5D%5B212%5D%5Bf%5D=1672&f%5Bsf%5D%5B212%5D%5Bu%5D=1804",
		"Bezaaide landen"  => "1600-1811/bezaaide-landen-1612?f%5Bs%5D%5B47%5D=0&f%5Bs%5D%5B47%5D=1&f%5Bsf%5D%5B49%5D%5Bt%5D=$surn&f%5Bsf%5D%5B49%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B50%5D%5Bt%5D=&f%5Bsf%5D%5B50%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B51%5D%5Bt%5D=$givn&f%5Bsf%5D%5B51%5D%5Bh%5D%5Bm%5D=default&f%5Bsf%5D%5B52%5D%5Bt%5D=&f%5Bsf%5D%5B64%5D%5Bt%5D=",
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
