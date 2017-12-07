<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class nederlandsekranten_plugin extends research_base_plugin {
	static function getName() {
		return 'Nederlandse kranten';
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
		$base_url = 'http://';

		$collection = array(
		"Alkmaar RA"	 	            => "kranten.archiefalkmaar.nl/search?query=%22$fullname%22&period=>$birth_year&sort=relevance&submit=zoeken",
		"Blaricum, Eemnes & Laren &"    => "bel.courant.nu/index.php?page=0&mod=krantresultaat&q=%22$fullname%22&datering=%3E$birth_year&krant=&qt=paragraaf&pagina=&sort=score+desc",
		"Eemland archief"	            => "archiefeemland.nl/collectie/kranten//bekijk?query=%22$fullname%22&period=>$birth_year&sort=relevance&submit=zoeken",
		"Gorinchem GA"                  => "gorinchem.courant.nu/index.php?page=0&mod=krantresultaat&q=%22$fullname%22&datering=%3E$birth_year&krant=&qt=paragraaf&pagina=&sort=datum+asc%2Ckrant+asc%2Cpagina+asc",
		"Krant van toen"                => "www.dekrantvantoen.nl/srch/query.do?q=%22$givn+$surname%22&qOR=&qNOT=&qpubcode=DVHN&qpubcode=AMB+OR+BCH+OR+BIJ+OR+BNH+OR+BRU+OR+DAM+OR+DRC+OR+EMS+OR+FEA+OR+FKC+OR+FRC+OR+FRD+OR+FRI+OR+GBS+OR+GZB+OR+HAH+OR+HEC+OR+HGL+OR+HOH+OR+HSK+OR+HWB+OR+JCH+OR+KHN+OR+KLA+OR+KSK+OR+KVC+OR+KVF+OR+KVH+OR+KVJ+OR+MCK+OR+MDW+OR+MFH+OR+MOH+OR+NDC+OR+NOF+OR+NOK+OR+NOL+OR+NPD+OR+NVP+OR+PEK+OR+ROJ+OR+SBO+OR+SNB+OR+SSN+OR+SSP+OR+TAC+OR+VEE+OR+WEZ+OR+WKL+OR+WKW+OR+ZFH+OR+ZOH&qpubcode=NVHN&qpubcode=LC&alt=on&qSI=20060101&startDate=dd-mm-yyyy&endDate=dd-mm-yyyy&qSD=29&qSM=07+&qSY=1752&qED=20&qEM=03&qEY=2017&x=104&y=20",
		"Langstraat Heusden Altena SA"	=> "kranten.salha.nl/search?query=%22$fullname%22&period=>$birth_year&sort=relevance&submit=zoeken",
		"Leiden en omstreken"	        => "leiden.courant.nu/search?query=%22$fullname%22&period=>$birth_year&sort=relevance&submit=zoeken",
		"Meppeler Courant"              => "www.175jaarmc.nl/archief?text=%22$fullname%22",
		"Midden-Holland SA"	            => "www.samh.nl/hs_search/?q=%22$fullname%22&selected_facets=object_type_exact:Krant",
		"Noord-Hollands Archief"	    => "nha.courant.nu/search?query=%22$fullname%22&period=%3E+$birth_year&submit=zoeken",
		"Noord-West Veluwe SA"		    => "snv.courant.nu/search?query=%22$fullname%22&period=%3E$birth_year&submit=zoeken",
		"Rijnstreek Lopikerwaard RHC"	=> "woerden.courant.nu/search?query=%22$fullname%22&period=>$birth_year&submit=zoeken",
		"Rivierenland RA"	            => "regionaalarchiefrivierenland.nl/kranten?mivast=102&miadt=102&mizig=91&miview=ldt&milang=nl&micols=1&mires=0&mizk_alle=%22$fullname%22&mibj=$birth_year",
		"Schiedam GA"	                => "schiedam.courant.nu/search?query=%22$fullname%22&period=>$birth_year&submit=zoeken",
		"Veenendaal GA"	                => "kranten.veenendaal.nl/search?query=%22$fullname%22&period=>$birth_year&submit=zoeken",
		"Venraij GA"	                => "peelenmaas.rooynet.nl/search?query=%22$fullname%22&period=>$birth_year&submit=zoeken",
		"Voorne-Putten & Rozenburg SA"	=> "www.streekarchiefvpr.nl/pages/nl/zoeken-in-collecties/digitale-kranten.php?mivast=126&miadt=126&mizig=91&miview=ldt&milang=nl&micols=1&mires=0&mizk_alle=%22$fullname%22&mibj=$birth_year",
		"Wageningen GA"                 => "gawk.courant.nu/index.php?page=0&mod=krantresultaat&q=%22$fullname%22&datering=%3E$birth_year&qt=paragraaf&pagina=&sort=score+desc",
		"Waterlands archief"	        => "waterland.courant.nu/search?query=%22$fullname%22&period=>$birth_year&submit=zoeken",
		"West-Brabants archief"	        => "periodieken.westbrabantsarchief.nl/search?query=%22$fullname%22&period=>$birth_year&submit=zoeken",
		"Westfries archief"	            => "www.westfriesarchief.nl/onderzoek/zoeken/kranten?mivast=136&miadt=136&mizig=91&miview=ldt&milang=nl&micols=1&mires=0&mizk_alle=$fullname&mibj=$birth_year",
		"Westland historisch archief"   => "www.historischarchiefwestland.nl/zoekresultaten/?q=%22$fullname%22&id=51&L=0&tx_solr%5Bfilter%5D%5B%5D=type%3Atx_whaocr_domain_model_newspaper",
		"Zaanstad GA"                   => "archief.zaanstad.nl/boeken-en-tijdschriften?option=com_maisinternet&view=maisinternet&Itemid=146&mivast=137&miadt=137&mizig=91&miview=ldt&milang=nl&micols=1&mires=0&mizk_alle=%22$fullname%22&mibj=$birth_year",
		"Zeeland krantenbank"           => "krantenbankzeeland.nl/search?query=%22$fullname%22&period=>$birth_year&submit=zoeken",
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
