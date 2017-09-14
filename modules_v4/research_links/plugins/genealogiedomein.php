<?php

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class genealogiedomein_plugin extends research_base_plugin {
	static function getName() {
		return 'Genealogiedomein';
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
		return false;
	}

	static function createLinkOnly() {
		return false;
	}

	static function createSubLinksOnly() {
		$base_url = 'http://www.genealogiedomein.nl/';

		$collection = array(
				"Aalten"			                => "index.php?option=com_docman&task=cat_view&gid=194&Itemid=27",
				"Achterhoek en Liemers"			    => "index.php?option=com_docman&task=cat_view&gid=96&Itemid=27",
				"Aerdt"			                    => "index.php?option=com_docman&task=cat_view&gid=317&Itemid=27",
				"Almen"		                        => "index.php?option=com_docman&task=cat_view&gid=42&Itemid=27",
				"Angerlo"			                => "index.php?option=com_docman&task=cat_view&gid=196&Itemid=27",
				"Baak"		                        => "index.php?option=com_docman&task=cat_view&gid=110&Itemid=27",
				"Bahr"		                        => "index.php?option=com_docman&task=cat_view&gid=1550&Itemid=27",
				"Bahr en Lathum"	                => "index.php?option=com_docman&task=cat_view&gid=63&Itemid=27",
				"Beek (Berg)"		                => "index.php?option=com_docman&task=cat_view&gid=1465&Itemid=27",
				"Beltrum"		                    => "index.php?option=com_docman&task=cat_view&gid=198&Itemid=27",
				"Bergh"			                    => "index.php?option=com_docman&task=cat_view&gid=62&Itemid=27",
				"Borculo"	                        => "index.php?option=com_docman&task=cat_view&gid=36&Itemid=27",
				"Bredevoort"		                => "index.php?option=com_docman&task=cat_view&gid=64&Itemid=27",
				"Bronkhorst"		                => "index.php?option=com_docman&task=cat_view&gid=106&Itemid=27",
				"Brummen"			                => "index.php?option=com_docman&task=cat_view&gid=113&Itemid=27",
				"Didam"			                    => "index.php?option=com_docman&task=cat_view&gid=68&Itemid=27",
				"Dinxperlo "		                => "index.php?option=com_docman&task=cat_view&gid=90&Itemid=27",
				"Doesburg"	                        => "index.php?option=com_docman&task=cat_view&gid=70&Itemid=27",
				"Doetinchem"		                => "index.php?option=com_docman&task=cat_view&gid=72&Itemid=27",
				"Dorth"		                        => "index.php?option=com_docman&task=cat_view&gid=222&Itemid=27",
				"Drempt"	                        => "index.php?option=com_docman&task=cat_view&gid=437&Itemid=27",
				"Duits grensgebied"	                => "index.php?option=com_docman&task=cat_view&gid=648&Itemid=27",
				"Duiven"	    	                => "index.php?option=com_docman&task=cat_view&gid=224&Itemid=27",
				"Eibergen"			                => "index.php?option=com_docman&task=cat_view&gid=37&Itemid=27",
				"Etten"			                    => "index.php?option=com_docman&task=cat_view&gid=227&Itemid=27",
				"Geesteren"		                    => "index.php?option=com_docman&task=cat_view&gid=38&Itemid=27",
				"Gelselaar"			                => "index.php?option=com_docman&task=cat_view&gid=39&Itemid=27",
				"Gendringen"	                    => "index.php?option=com_docman&task=cat_view&gid=94&Itemid=27",
				"Gendringen en Etten"			    => "index.php?option=com_docman&task=cat_view&gid=74&Itemid=27",
				"Giesbeek"			                => "index.php?option=com_docman&task=cat_view&gid=517&Itemid=27",
				"Gorssel"	                        => "index.php?option=com_docman&task=cat_view&gid=40&Itemid=27",
				"Groenlo"	                        => "index.php?option=com_docman&task=cat_view&gid=41&Itemid=27",
				"Groessen"			                => "index.php?option=com_docman&task=cat_view&gid=741&Itemid=27",
				"Haarlo"			                => "index.php?option=com_docman&task=cat_view&gid=43&Itemid=27",
				"Harreveld"			                => "index.php?option=com_docman&task=cat_view&gid=102&Itemid=27",
				"Heerenberg ('s)"			        => "index.php?option=com_docman&task=cat_view&gid=76&Itemid=27",
				"Hengelo (Gld)"			            => "index.php?option=com_docman&task=cat_view&gid=88&Itemid=27",
				"Hengelo/Zelhem"			        => "index.php?option=com_docman&task=cat_view&gid=655&Itemid=27",
				"Herwen"			                => "index.php?option=com_docman&task=cat_view&gid=192&Itemid=27",
				"Herwen en Aerdt"			        => "index.php?option=com_docman&task=cat_view&gid=240&Itemid=27",
				"Hoog-Keppel"			            => "index.php?option=com_docman&task=cat_view&gid=735&Itemid=27",
				"Hummelo"			                => "index.php?option=com_docman&task=cat_view&gid=115&Itemid=27",
				"Hummelo en Keppel"			        => "index.php?option=com_docman&task=cat_view&gid=2440&Itemid=27",
				"Huwelijkdispensaties"			    => "index.php?option=com_docman&task=cat_view&gid=60&Itemid=27",
				"Keppel"			                => "index.php?option=com_docman&task=cat_view&gid=246&Itemid=27",
				"Laren (Gld))"			            => "index.php?option=com_docman&task=cat_view&gid=55&Itemid=27",
				"Lathum"		  	                => "index.php?option=com_docman&task=cat_view&gid=519&Itemid=27",
				"Lichtenberg (Silvolde))"			=> "index.php?option=com_docman&task=cat_view&gid=935&Itemid=27",
				"Lichtenvoorde"			            => "index.php?option=com_docman&task=cat_view&gid=44&Itemid=27",
				"Lobith"			                => "index.php?option=com_docman&task=cat_view&gid=250&Itemid=27",
				"Lobith en Spijk"			        => "index.php?option=com_docman&task=cat_view&gid=321&Itemid=27",
				"Lochem"			                => "index.php?option=com_docman&task=cat_view&gid=45&Itemid=27",
				"Loo(Duiven)"			            => "index.php?option=com_docman&task=cat_view&gid=801&Itemid=27",
				"Loo en Westervoort"			    => "index.php?option=com_docman&task=cat_view&gid=805&Itemid=27",
				"Megchelen"			                => "index.php?option=com_docman&task=cat_view&gid=732&Itemid=27",
				"Neede"			                    => "index.php?option=com_docman&task=cat_view&gid=46&Itemid=27",
				"Netterden"			                => "index.php?option=com_docman&task=cat_view&gid=93&Itemid=27",
				"Olburgen en Drempt"			    => "index.php?option=com_docman&task=cat_view&gid=304&Itemid=27",
				"Oud-Zevenaar"			            => "index.php?option=com_docman&task=cat_view&gid=323&Itemid=27",
				"Pannerden"			                => "index.php?option=com_docman&task=cat_view&gid=186&Itemid=27",
				"Rekken"			                => "index.php?option=com_docman&task=cat_view&gid=47&Itemid=27",
				"Ruurlo"			                => "index.php?option=com_docman&task=cat_view&gid=48&Itemid=27",
				"Silvolde"			                => "index.php?option=com_docman&task=cat_view&gid=381&Itemid=27",
				"Steenderen"			            => "index.php?option=com_docman&task=cat_view&gid=86&Itemid=27",
				"Terborg"			                => "index.php?option=com_docman&task=cat_view&gid=78&Itemid=27",
				"Ulft"			                    => "index.php?option=com_docman&task=cat_view&gid=104&Itemid=27",
				"Varsseveld"			            => "index.php?option=com_docman&task=cat_view&gid=98&Itemid=27",
				"Verwolde"			                => "index.php?option=com_docman&task=cat_view&gid=56&Itemid=27",
				"Vorden"			                => "index.php?option=com_docman&task=cat_view&gid=49&Itemid=27",
				"Warnsveld"			                => "index.php?option=com_docman&task=cat_view&gid=52&Itemid=27",
				"Wehl"			                    => "index.php?option=com_docman&task=cat_view&gid=267&Itemid=27",
				"Westervoort"			            => "index.php?option=com_docman&task=cat_view&gid=82&Itemid=27",
				"Winterswijk"			            => "index.php?option=com_docman&task=cat_view&gid=271&Itemid=27",
				"Wisch"			                    => "index.php?option=com_docman&task=cat_view&gid=80&Itemid=27",
				"Zeddam"			                => "index.php?option=com_docman&task=cat_view&gid=275&Itemid=27",
				"Zelhem"			                => "index.php?option=com_docman&task=cat_view&gid=58&Itemid=27",
				"Zevenaar"			                => "index.php?option=com_docman&task=cat_view&gid=278&Itemid=27",
				"Zutphen"			                => "index.php?option=com_docman&task=cat_view&gid=84&Itemid=27",

			);
		foreach($collection as $x => $x_value) {
				$link[] = array(
					'title' => KT_I18N::translate($x),
					'link'  => $base_url. $x_value
				);
			}
			return $link;
	}

	static function encode_plus() {
		return false;
	}
}
