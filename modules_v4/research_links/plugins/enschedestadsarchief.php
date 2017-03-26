<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class enschedestadsarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Enschede Stadsarchief';
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
		$base_url = 'http://enschedepubliek.hosting.deventit.net/zoeken.php?';

		$collection = array(

		    "Bevolkingsregister"      => "zoeken.php?zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=1431998&zoeken%5Bbeschrijvingssoorten%5D%5B1431873%5D=1431873&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDocumentnummer%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BAchternaam%5D%5Bwaarde%5D=$surn&zoeken%5Bvelden%5D%5BVoornaam%5D%5Bwaarde%5D=$givn&zoeken%5Bvelden%5D%5BBron%5D%5Bwaarde%5D%5B%5D=Bevolkingsregister&zoeken%5Btransformeren%5D=&searchtype=new&btn-submit=Zoeken",
		    "Doopakten"               => "zoeken.php?zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=1431998&zoeken%5Bbeschrijvingssoorten%5D%5B1431873%5D=1431873&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDocumentnummer%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BAchternaam%5D%5Bwaarde%5D=$surn&zoeken%5Bvelden%5D%5BVoornaam%5D%5Bwaarde%5D=$givn&zoeken%5Bvelden%5D%5BBron%5D%5Bwaarde%5D%5B%5D=Doopakten+N.H.Kerk+Enschede&zoeken%5Btransformeren%5D=&searchtype=new&btn-submit=Zoeken",
		    "Geboorten Enschede"      => "zoeken.php?zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=1431998&zoeken%5Bbeschrijvingssoorten%5D%5B1431873%5D=1431873&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDocumentnummer%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BAchternaam%5D%5Bwaarde%5D=$surn&zoeken%5Bvelden%5D%5BVoornaam%5D%5Bwaarde%5D=$givn&zoeken%5Bvelden%5D%5BBron%5D%5Bwaarde%5D%5B%5D=Geboorten+Enschede&zoeken%5Btransformeren%5D=&searchtype=new&btn-submit=Zoeken",
		    "Geboorten Lonneker"      => "zoeken.php?zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=1431998&zoeken%5Bbeschrijvingssoorten%5D%5B1431873%5D=1431873&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDocumentnummer%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BAchternaam%5D%5Bwaarde%5D=$surn&zoeken%5Bvelden%5D%5BVoornaam%5D%5Bwaarde%5D=$givn&zoeken%5Bvelden%5D%5BBron%5D%5Bwaarde%5D%5B%5D=Geboorten+Lonneker&zoeken%5Btransformeren%5D=&searchtype=new&btn-submit=Zoeken",
		    "Huwelijken Enschede"     => "zoeken.php?zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=1431998&zoeken%5Bbeschrijvingssoorten%5D%5B1431873%5D=1431873&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDocumentnummer%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BAchternaam%5D%5Bwaarde%5D=$surn&zoeken%5Bvelden%5D%5BVoornaam%5D%5Bwaarde%5D=$givn&zoeken%5Bvelden%5D%5BBron%5D%5Bwaarde%5D%5B%5D=Huwelijken+Enschede&zoeken%5Btransformeren%5D=&searchtype=new&btn-submit=Zoeken",
		    "Huwelijken Lonneker"     => "zoeken.php?zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=1431998&zoeken%5Bbeschrijvingssoorten%5D%5B1431873%5D=1431873&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDocumentnummer%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BAchternaam%5D%5Bwaarde%5D=$surn&zoeken%5Bvelden%5D%5BVoornaam%5D%5Bwaarde%5D=$givn&zoeken%5Bvelden%5D%5BBron%5D%5Bwaarde%5D%5B%5D=Huwelijken+Lonneker&zoeken%5Btransformeren%5D=&searchtype=new&btn-submit=Zoeken",
		    "Landgericht"             => "zoeken.php?zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=1431998&zoeken%5Bbeschrijvingssoorten%5D%5B1431873%5D=1431873&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDocumentnummer%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BAchternaam%5D%5Bwaarde%5D=$surn&zoeken%5Bvelden%5D%5BVoornaam%5D%5Bwaarde%5D=$givn&zoeken%5Bvelden%5D%5BBron%5D%5Bwaarde%5D%5B%5D=Landgericht&zoeken%5Btransformeren%5D=&searchtype=new&btn-submit=Zoeken",
		    "Militieregister"         => "zoeken.php?zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=1431998&zoeken%5Bbeschrijvingssoorten%5D%5B1431873%5D=1431873&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDocumentnummer%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BAchternaam%5D%5Bwaarde%5D=$surn&zoeken%5Bvelden%5D%5BVoornaam%5D%5Bwaarde%5D=$givn&zoeken%5Bvelden%5D%5BBron%5D%5Bwaarde%5D%5B%5D=Militieregister+Enschede&zoeken%5Btransformeren%5D=&searchtype=new&btn-submit=Zoeken",
		    "Overlijden Enschede"     => "zoeken.php?zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=1431998&zoeken%5Bbeschrijvingssoorten%5D%5B1431873%5D=1431873&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDocumentnummer%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BAchternaam%5D%5Bwaarde%5D=$surn&zoeken%5Bvelden%5D%5BVoornaam%5D%5Bwaarde%5D=$givn&zoeken%5Bvelden%5D%5BBron%5D%5Bwaarde%5D%5B%5D=Overlijdens+Enschede&zoeken%5Btransformeren%5D=&searchtype=new&btn-submit=Zoeken",
		    "Overlijden Lonneker"     => "zoeken.php?zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=1431998&zoeken%5Bbeschrijvingssoorten%5D%5B1431873%5D=1431873&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDocumentnummer%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BAchternaam%5D%5Bwaarde%5D=$surn&zoeken%5Bvelden%5D%5BVoornaam%5D%5Bwaarde%5D=$givn&zoeken%5Bvelden%5D%5BBron%5D%5Bwaarde%5D%5B%5D=Overlijdens+Lonneker&zoeken%5Btransformeren%5D=&searchtype=new&btn-submit=Zoeken",
		    "Overlijdensverklaringen" => "zoeken.php?zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=1431998&zoeken%5Bbeschrijvingssoorten%5D%5B1431873%5D=1431873&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDocumentnummer%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BAchternaam%5D%5Bwaarde%5D=$surn&zoeken%5Bvelden%5D%5BVoornaam%5D%5Bwaarde%5D=$givn&zoeken%5Bvelden%5D%5BBron%5D%5Bwaarde%5D%5B%5D=Overlijdensverklaringen+Lonneker&zoeken%5Btransformeren%5D=&searchtype=new&btn-submit=Zoeken",
		    "Stadsgericht"            => "zoeken.php?zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=1431998&zoeken%5Bbeschrijvingssoorten%5D%5B1431873%5D=1431873&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDocumentnummer%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BDatum_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BAchternaam%5D%5Bwaarde%5D=$surn&zoeken%5Bvelden%5D%5BVoornaam%5D%5Bwaarde%5D=$givn&zoeken%5Bvelden%5D%5BBron%5D%5Bwaarde%5D%5B%5D=Stadsgericht&zoeken%5Btransformeren%5D=&searchtype=new&btn-submit=Zoeken",

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
