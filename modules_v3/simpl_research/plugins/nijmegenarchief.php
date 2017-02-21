<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class nijmegenarchief_plugin extends research_base_plugin {
	static function getName() {
		return 'Nijmegen Regionaal Archief';
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
		$base_url = 'http://studiezaal.nijmegen.nl/';

		$collection = array(
			"Doopregisters"					=> "ran/_resultaat.aspx?abc_mode=1&invalshoek=22849198&invalshoeknaam=GENERIEK&aantalperpagina=20&uitgebreid_zoeken=true&locatie=0&personen=1&bsoorten_136=136&cmveldValue_VrijXXzoeken=&cmveldName_VrijXXzoeken=VrijXXzoeken&cmveldValue_Periode_van=&cmveldName_Periode_van=Periode&cmveldValue_Periode_tot=&cmveldName_Periode_zoeken=Periode&cmveldValue_Persoonsnaam=$givn+$surn&cmveldName_Persoonsnaam=Persoonsnaam&cmveldValue_Locatie=&cmveldName_Locatie=Locatie&cmveldValue_Gemeente=&cmveldValue_Sectienr=&cmveldValue_rubriekenselect=X0X&cmveldConnector_selectie=OR&cmveldFilter_selectie=gelijkaan&cmveldValue_selectie=Geboorte&cmveldName_selectie=selectie&sortering=",
			"Huwelijksregisters"			=> "ran/_resultaat.aspx?abc_mode=1&invalshoek=22849198&invalshoeknaam=GENERIEK&aantalperpagina=20&uitgebreid_zoeken=true&locatie=0&personen=1&bsoorten_136=136&cmveldValue_VrijXXzoeken=&cmveldName_VrijXXzoeken=VrijXXzoeken&cmveldValue_Periode_van=&cmveldName_Periode_van=Periode&cmveldValue_Periode_tot=&cmveldName_Periode_zoeken=Periode&cmveldValue_Persoonsnaam=$givn+$surn&cmveldName_Persoonsnaam=Persoonsnaam&cmveldValue_Locatie=&cmveldName_Locatie=Locatie&cmveldValue_Gemeente=&cmveldValue_Sectienr=&cmveldValue_rubriekenselect=X0X&cmveldConnector_selectie=OR&cmveldFilter_selectie=gelijkaan&cmveldValue_selectie=Huwelijk&cmveldName_selectie=selectie&sortering=",
			"Begraafregisters"				=> "ran/_resultaat.aspx?abc_mode=1&invalshoek=22849198&invalshoeknaam=GENERIEK&aantalperpagina=20&uitgebreid_zoeken=true&locatie=0&personen=1&bsoorten_136=136&cmveldValue_VrijXXzoeken=&cmveldName_VrijXXzoeken=VrijXXzoeken&cmveldValue_Periode_van=&cmveldName_Periode_van=Periode&cmveldValue_Periode_tot=&cmveldName_Periode_zoeken=Periode&cmveldValue_Persoonsnaam=$givn+$surn&cmveldName_Persoonsnaam=Persoonsnaam&cmveldValue_Locatie=&cmveldName_Locatie=Locatie&cmveldValue_Gemeente=&cmveldValue_Sectienr=&cmveldValue_rubriekenselect=X0X&cmveldConnector_selectie=OR&cmveldFilter_selectie=gelijkaan&cmveldValue_selectie=Overlijden&cmveldName_selectie=selectie&sortering=",
			"Lidmatenregisters"				=> "ran/_resultaat.aspx?abc_mode=1&invalshoek=22849198&invalshoeknaam=GENERIEK&aantalperpagina=20&uitgebreid_zoeken=true&locatie=0&personen=1&bsoorten_136=136&cmveldValue_VrijXXzoeken=&cmveldName_VrijXXzoeken=VrijXXzoeken&cmveldValue_Periode_van=&cmveldName_Periode_van=Periode&cmveldValue_Periode_tot=&cmveldName_Periode_zoeken=Periode&cmveldValue_Persoonsnaam=$givn+$surn&cmveldName_Persoonsnaam=Persoonsnaam&cmveldValue_Locatie=&cmveldName_Locatie=Locatie&cmveldValue_Gemeente=&cmveldValue_Sectienr=&cmveldValue_rubriekenselect=X0X&cmveldConnector_selectie=OR&cmveldFilter_selectie=gelijkaan&cmveldValue_selectie=Lidmaten&cmveldName_selectie=selectie&sortering=",
			"Notarissen/Schepenprotocol"	=> "ran/_resultaat.aspx?abc_mode=1&invalshoek=22849198&invalshoeknaam=GENERIEK&aantalperpagina=20&uitgebreid_zoeken=true&locatie=0&personen=1&bsoorten_136=136&cmveldValue_VrijXXzoeken=&cmveldName_VrijXXzoeken=VrijXXzoeken&cmveldValue_Periode_van=&cmveldName_Periode_van=Periode&cmveldValue_Periode_tot=&cmveldName_Periode_zoeken=Periode&cmveldValue_Persoonsnaam=$givn+$surn&cmveldName_Persoonsnaam=Persoonsnaam&cmveldValue_Locatie=&cmveldName_Locatie=Locatie&cmveldValue_Gemeente=&cmveldValue_Sectienr=&cmveldValue_rubriekenselect=X0X&cmveldConnector_selectie=OR&cmveldFilter_selectie=gelijkaan&cmveldValue_selectie=Notarissen+en+schepenprotocol&cmveldName_selectie=selectie&sortering=",
			"Functionarissen"				=> "ran/_resultaat.aspx?abc_mode=1&invalshoek=22849198&invalshoeknaam=GENERIEK&aantalperpagina=20&uitgebreid_zoeken=true&locatie=0&personen=1&bsoorten_136=136&cmveldValue_VrijXXzoeken=&cmveldName_VrijXXzoeken=VrijXXzoeken&cmveldValue_Periode_van=&cmveldName_Periode_van=Periode&cmveldValue_Periode_tot=&cmveldName_Periode_zoeken=Periode&cmveldValue_Persoonsnaam=$givn+$surn&cmveldName_Persoonsnaam=Persoonsnaam&cmveldValue_Locatie=&cmveldName_Locatie=Locatie&cmveldValue_Gemeente=&cmveldValue_Sectienr=&cmveldValue_rubriekenselect=X0X&cmveldConnector_selectie=OR&cmveldFilter_selectie=gelijkaan&cmveldValue_selectie=Beroepen&cmveldName_selectie=selectie&sortering=",
			"Stadsrekeningen"				=> "ran/_resultaat.aspx?abc_mode=1&invalshoek=22849198&invalshoeknaam=GENERIEK&aantalperpagina=20&uitgebreid_zoeken=true&locatie=0&personen=1&bsoorten_136=136&cmveldValue_VrijXXzoeken=&cmveldName_VrijXXzoeken=VrijXXzoeken&cmveldValue_Periode_van=&cmveldName_Periode_van=Periode&cmveldValue_Periode_tot=&cmveldName_Periode_zoeken=Periode&cmveldValue_Persoonsnaam=$givn+$surn&cmveldName_Persoonsnaam=Persoonsnaam&cmveldValue_Locatie=&cmveldName_Locatie=Locatie&cmveldValue_Gemeente=&cmveldValue_Sectienr=&cmveldValue_rubriekenselect=X0X&cmveldConnector_selectie=OR&cmveldFilter_selectie=gelijkaan&cmveldValue_selectie=Stedelijke+financien&cmveldName_selectie=selectie&sortering=",
			"Bevolkingsregister"			=> "ran/_resultaat.aspx?abc_mode=1&invalshoek=22849198&invalshoeknaam=GENERIEK&aantalperpagina=20&uitgebreid_zoeken=true&locatie=0&personen=1&bsoorten_136=136&cmveldValue_VrijXXzoeken=&cmveldName_VrijXXzoeken=VrijXXzoeken&cmveldValue_Periode_van=&cmveldName_Periode_van=Periode&cmveldValue_Periode_tot=&cmveldName_Periode_zoeken=Periode&cmveldValue_Persoonsnaam=$givn+$surn&cmveldName_Persoonsnaam=Persoonsnaam&cmveldValue_Locatie=&cmveldName_Locatie=Locatie&cmveldValue_Gemeente=&cmveldValue_Sectienr=&cmveldValue_rubriekenselect=X0X&cmveldConnector_selectie=OR&cmveldFilter_selectie=gelijkaan&cmveldValue_selectie=Bevolking&cmveldName_selectie=selectie&sortering=",
			"Miltairen"						=> "ran/_resultaat.aspx?abc_mode=1&invalshoek=22849198&invalshoeknaam=GENERIEK&aantalperpagina=20&uitgebreid_zoeken=true&locatie=0&personen=1&bsoorten_136=136&cmveldValue_VrijXXzoeken=&cmveldName_VrijXXzoeken=VrijXXzoeken&cmveldValue_Periode_van=&cmveldName_Periode_van=Periode&cmveldValue_Periode_tot=&cmveldName_Periode_zoeken=Periode&cmveldValue_Persoonsnaam=$givn+$surn&cmveldName_Persoonsnaam=Persoonsnaam&cmveldValue_Locatie=&cmveldName_Locatie=Locatie&cmveldValue_Gemeente=&cmveldValue_Sectienr=&cmveldValue_rubriekenselect=X0X&cmveldConnector_selectie=OR&cmveldFilter_selectie=gelijkaan&cmveldValue_selectie=Militairen&cmveldName_selectie=selectie&sortering=",
			"Charters"						=> "ran/_resultaat.aspx?abc_mode=1&invalshoek=22849198&invalshoeknaam=GENERIEK&aantalperpagina=20&uitgebreid_zoeken=true&locatie=0&personen=1&bsoorten_136=136&cmveldValue_VrijXXzoeken=&cmveldName_VrijXXzoeken=VrijXXzoeken&cmveldValue_Periode_van=&cmveldName_Periode_van=Periode&cmveldValue_Periode_tot=&cmveldName_Periode_zoeken=Periode&cmveldValue_Persoonsnaam=$givn+$surn&cmveldName_Persoonsnaam=Persoonsnaam&cmveldValue_Locatie=&cmveldName_Locatie=Locatie&cmveldValue_Gemeente=&cmveldValue_Sectienr=&cmveldValue_rubriekenselect=X0X&cmveldConnector_selectie=OR&cmveldFilter_selectie=gelijkaan&cmveldValue_selectie=Oorkonden&cmveldName_selectie=selectie&sortering=",
			"Huis- en grondeigenaren"		=> "ran/_resultaat.aspx?abc_mode=1&invalshoek=22849198&invalshoeknaam=GENERIEK&aantalperpagina=20&uitgebreid_zoeken=true&locatie=1&personen=0&bsoorten_136=136&cmveldValue_VrijXXzoeken=$givn+$surn&cmveldName_VrijXXzoeken=VrijXXzoeken&cmveldValue_Periode_van=&cmveldName_Periode_van=Periode&cmveldValue_Periode_tot=&cmveldName_Periode_zoeken=Periode&cmveldValue_Plaatsnaam=&cmveldName_Plaatsnaam=Plaatsnaam&cmveldValue_Straatnaam=&cmveldValue_Huisnr=&cmveldName_Straatnaam=Straatnaam&cmveldName_Huisnr=Huisnr&cmveldValue_Gemeente=&cmveldValue_Sectienr=&cmveldValue_Kadnr=&cmveldName_Gemeente=Gemeente&cmveldName_Sectienr=Sectienr&cmveldName_Kadnr=Kadnr&cmveldValue_Wijknr=&cmveldName_Wijknr=Wijknr&cmveldValue_rubriekenselect=X0X&cmveldConnector_selectie=OR&cmveldValue_selectie=Huis-+en+grondeigenaren&cmveldName_selectie=selectie&sortering=",
			"Bestektekeningen"				=> "ran/_resultaat.aspx?abc_mode=1&invalshoek=22849198&invalshoeknaam=GENERIEK&aantalperpagina=20&uitgebreid_zoeken=true&locatie=1&personen=0&bsoorten_136=136&cmveldValue_VrijXXzoeken=$surn&cmveldName_VrijXXzoeken=VrijXXzoeken&cmveldValue_Periode_van=&cmveldName_Periode_van=Periode&cmveldValue_Periode_tot=&cmveldName_Periode_zoeken=Periode&cmveldValue_Plaatsnaam=&cmveldName_Plaatsnaam=Plaatsnaam&cmveldValue_Straatnaam=&cmveldValue_Huisnr=&cmveldName_Straatnaam=Straatnaam&cmveldName_Huisnr=Huisnr&cmveldValue_Gemeente=&cmveldValue_Sectienr=&cmveldValue_Kadnr=&cmveldName_Gemeente=Gemeente&cmveldName_Sectienr=Sectienr&cmveldName_Kadnr=Kadnr&cmveldValue_Wijknr=&cmveldName_Wijknr=Wijknr&cmveldValue_rubriekenselect=X0X&cmveldConnector_selectie=OR&cmveldValue_selectie=Bestektekeningen&cmveldName_selectie=selectie&sortering=",
			"Overige bronnen"				=> "ran/_resultaat.aspx?abc_mode=1&invalshoek=22849198&invalshoeknaam=GENERIEK&aantalperpagina=20&uitgebreid_zoeken=true&locatie=0&personen=0&bsoorten_2126473497=2126473497%2C2126473695%2C2126473881%2C2126473625%2C2126473784&bsoorten_242=242&bsoorten_277=277&bsoorten_615685682=615685682&bsoorten_28785236=28785236&bsoorten_2090953492=2090953492&cmveldValue_VrijXXzoeken=$surn&cmveldName_VrijXXzoeken=VrijXXzoeken&cmveldValue_Periode_van=$birth_year&cmveldName_Periode_van=Periode&cmveldValue_Periode_tot=$death_year&cmveldName_Periode_zoeken=Periode&cmveldValue_Gemeente=&cmveldValue_Sectienr=&cmveldValue_rubriekenselect=X0X&sortering=",
		);

		foreach($collection as $key => $value) {
			$link[] = array(
				'title' => WT_I18N::translate($key),
				'link'	=> $base_url . $value
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
