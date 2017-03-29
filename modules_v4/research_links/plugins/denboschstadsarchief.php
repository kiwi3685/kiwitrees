<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class denboschstadsarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Den Bosch Stadsarchief';
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
		$base_url = 'http://denboschpubliek.hosting.deventit.net/zoeken.php?/';

		$collection = array(
		    "Begravingen"               => "zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=38089355&zoeken%5Bbeschrijvingssoorten%5D%5B11313400%5D=11313400&zoeken%5Bbeschrijvingssoorten%5D%5B177483877%5D=177483877&zoeken%5Bbeschrijvingssoorten%5D%5B177484175%5D=177484175&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=$givn+$surname&zoeken%5Bvelden%5D%5BSoort%5D%5Bwaarde%5D%5B%5D=Begravingen&zoeken%5Bvelden%5D%5BNaam%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BSoort%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BSoort%5D%5Bvoorwaarde%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bvoorwaarde%5D=GelijkAan&searchtype=new&btn-submit=Zoeken",
		    "Bidprentjes"               => "zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=38089355&zoeken%5Bbeschrijvingssoorten%5D%5B11313400%5D=11313400&zoeken%5Bbeschrijvingssoorten%5D%5B177483877%5D=177483877&zoeken%5Bbeschrijvingssoorten%5D%5B177484175%5D=177484175&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=$givn+$surname&zoeken%5Bvelden%5D%5BSoort%5D%5Bwaarde%5D%5B%5D=Bidprentjes&zoeken%5Bvelden%5D%5BNaam%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BSoort%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BSoort%5D%5Bvoorwaarde%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bvoorwaarde%5D=GelijkAan&searchtype=new&btn-submit=Zoeken",
		    "Blokboeken"                => "zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=38089355&zoeken%5Bbeschrijvingssoorten%5D%5B11313400%5D=11313400&zoeken%5Bbeschrijvingssoorten%5D%5B177483877%5D=177483877&zoeken%5Bbeschrijvingssoorten%5D%5B177484175%5D=177484175&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=$givn+$surname&zoeken%5Bvelden%5D%5BSoort%5D%5Bwaarde%5D%5B%5D=Blokboeken&zoeken%5Bvelden%5D%5BNaam%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BSoort%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BSoort%5D%5Bvoorwaarde%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bvoorwaarde%5D=GelijkAan&searchtype=new&btn-submit=Zoeken",
		    "Bosch protocol"            => "zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=38089355&zoeken%5Bbeschrijvingssoorten%5D%5B11313400%5D=11313400&zoeken%5Bbeschrijvingssoorten%5D%5B177483877%5D=177483877&zoeken%5Bbeschrijvingssoorten%5D%5B177484175%5D=177484175&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=$givn+$surname&zoeken%5Bvelden%5D%5BSoort%5D%5Bwaarde%5D%5B%5D=Bosch+Protocol&zoeken%5Bvelden%5D%5BNaam%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BSoort%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BSoort%5D%5Bvoorwaarde%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bvoorwaarde%5D=GelijkAan&searchtype=new&btn-submit=Zoeken",
		    "Data Schurk"               => "zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=38089355&zoeken%5Bbeschrijvingssoorten%5D%5B11313400%5D=11313400&zoeken%5Bbeschrijvingssoorten%5D%5B177483877%5D=177483877&zoeken%5Bbeschrijvingssoorten%5D%5B177484175%5D=177484175&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=$givn+$surname&zoeken%5Bvelden%5D%5BSoort%5D%5Bwaarde%5D%5B%5D=Dataschurk&zoeken%5Bvelden%5D%5BNaam%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BSoort%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BSoort%5D%5Bvoorwaarde%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bvoorwaarde%5D=GelijkAan&searchtype=new&btn-submit=Zoeken",
		    "Dopen"                     => "zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=38089355&zoeken%5Bbeschrijvingssoorten%5D%5B11313400%5D=11313400&zoeken%5Bbeschrijvingssoorten%5D%5B177483877%5D=177483877&zoeken%5Bbeschrijvingssoorten%5D%5B177484175%5D=177484175&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D='$givn+$surname&zoeken%5Bvelden%5D%5BSoort%5D%5Bwaarde%5D%5B%5D=Dopen&zoeken%5Bvelden%5D%5BNaam%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BSoort%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BSoort%5D%5Bvoorwaarde%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bvoorwaarde%5D=GelijkAan&searchtype=new&btn-submit=Zoeken",
		    "Geboorte"                  => "zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=38089355&zoeken%5Bbeschrijvingssoorten%5D%5B11313400%5D=11313400&zoeken%5Bbeschrijvingssoorten%5D%5B177483877%5D=177483877&zoeken%5Bbeschrijvingssoorten%5D%5B177484175%5D=177484175&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=$givn+$surname&zoeken%5Bvelden%5D%5BSoort%5D%5Bwaarde%5D%5B%5D=Geboorte&zoeken%5Bvelden%5D%5BNaam%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BSoort%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BSoort%5D%5Bvoorwaarde%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bvoorwaarde%5D=GelijkAan&searchtype=new&btn-submit=Zoeken",
		    "Huwelijken"                => "zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=38089355&zoeken%5Bbeschrijvingssoorten%5D%5B11313400%5D=11313400&zoeken%5Bbeschrijvingssoorten%5D%5B177483877%5D=177483877&zoeken%5Bbeschrijvingssoorten%5D%5B177484175%5D=177484175&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D='$givn+$surname&zoeken%5Bvelden%5D%5BSoort%5D%5Bwaarde%5D%5B%5D=Huwelijken&zoeken%5Bvelden%5D%5BNaam%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BSoort%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BSoort%5D%5Bvoorwaarde%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bvoorwaarde%5D=GelijkAan&searchtype=new&btn-submit=Zoeken",
		    "Lidmaten"                  => "zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=38089355&zoeken%5Bbeschrijvingssoorten%5D%5B11313400%5D=11313400&zoeken%5Bbeschrijvingssoorten%5D%5B177483877%5D=177483877&zoeken%5Bbeschrijvingssoorten%5D%5B177484175%5D=177484175&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=$givn+$surname&zoeken%5Bvelden%5D%5BSoort%5D%5Bwaarde%5D%5B%5D=Lidmaten&zoeken%5Bvelden%5D%5BNaam%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BSoort%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BSoort%5D%5Bvoorwaarde%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bvoorwaarde%5D=GelijkAan&searchtype=new&btn-submit=Zoeken",
		    "Notarieel"                 => "pzoeken%5Bbeschrijvingsgroepen%5D%5B%5D=38089355&zoeken%5Bbeschrijvingssoorten%5D%5B11313400%5D=11313400&zoeken%5Bbeschrijvingssoorten%5D%5B177483877%5D=177483877&zoeken%5Bbeschrijvingssoorten%5D%5B177484175%5D=177484175&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=$givn+$surname&zoeken%5Bvelden%5D%5BSoort%5D%5Bwaarde%5D%5B%5D=Notarieel&zoeken%5Bvelden%5D%5BNaam%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BSoort%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BSoort%5D%5Bvoorwaarde%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bvoorwaarde%5D=GelijkAan&searchtype=new&btn-submit=Zoeken",
		    "Overlijden"                => "zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=38089355&zoeken%5Bbeschrijvingssoorten%5D%5B11313400%5D=11313400&zoeken%5Bbeschrijvingssoorten%5D%5B177483877%5D=177483877&zoeken%5Bbeschrijvingssoorten%5D%5B177484175%5D=177484175&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=$givn+$surname&zoeken%5Bvelden%5D%5BSoort%5D%5Bwaarde%5D%5B%5D=Overlijden&zoeken%5Bvelden%5D%5BNaam%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BSoort%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BSoort%5D%5Bvoorwaarde%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bvoorwaarde%5D=GelijkAan&searchtype=new&btn-submit=Zoeken",
		    "Patienten"                 => "zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=38089355&zoeken%5Bbeschrijvingssoorten%5D%5B11313400%5D=11313400&zoeken%5Bbeschrijvingssoorten%5D%5B177483877%5D=177483877&zoeken%5Bbeschrijvingssoorten%5D%5B177484175%5D=177484175&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=$givn+$surname&zoeken%5Bvelden%5D%5BSoort%5D%5Bwaarde%5D%5B%5D=Pati%C3%ABnten&zoeken%5Bvelden%5D%5BNaam%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BSoort%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BSoort%5D%5Bvoorwaarde%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bvoorwaarde%5D=GelijkAan&searchtype=new&btn-submit=Zoeken",
		    "Vreemdelingen"             => "zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=38089355&zoeken%5Bbeschrijvingssoorten%5D%5B11313400%5D=11313400&zoeken%5Bbeschrijvingssoorten%5D%5B177483877%5D=177483877&zoeken%5Bbeschrijvingssoorten%5D%5B177484175%5D=177484175&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=$givn+$surname&zoeken%5Bvelden%5D%5BSoort%5D%5Bwaarde%5D%5B%5D=Vreemdelingenregistratie&zoeken%5Bvelden%5D%5BNaam%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BSoort%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BSoort%5D%5Bvoorwaarde%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bvoorwaarde%5D=GelijkAan&searchtype=new&btn-submit=Zoeken",
		    "Beeldbanken"               => "zoeken%5Bbeschrijvingsgroepen%5D%5B%5D=174274563&zoeken%5Bbeschrijvingssoorten%5D%5B11313357%5D=11313357&zoeken%5Bvelden%5D%5BGlobaal%5D%5Bwaarde%5D=$givn+$surname&zoeken%5Bvelden%5D%5BIdentificatienummer%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BBeschrijving%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_van%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BPeriode_tot%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BTrefwoorden%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BStraatnaam%5D%5Bwaarde%5D=&zoeken%5Bvelden%5D%5BSoort%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BSoort%5D%5Bvoorwaarde%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bfilter%5D=GelijkAan&zoeken%5Bvelden%5D%5BToegang%5D%5Bvoorwaarde%5D=GelijkAan&searchtype=new&btn-submit=Zoeken",
		    
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
