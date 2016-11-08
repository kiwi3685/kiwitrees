<?php
// Application configuration data.  Data here has no GUI to edit it,
// although most of it can be altered to customise local installations.
//
// NOTE: The one-item-per-line and extra-comma-after-last-item approach
// is used to allow SVN to reliably merge changes for users that have
// customised their local copy of this file.
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2010 PGV Development Team
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

// Unknown surname
$UNKNOWN_NN = WT_I18N::translate_c('Unknown surname', '…');

// Unknown given name
$UNKNOWN_PN = WT_I18N::translate_c('Unknown given name', '…');

// NPFX tags - name prefixes
$NPFX_accept = array(
	'Adm',
	'Amb',
	'Brig',
	'Can',
	'Capt',
	'Chan',
	'Chapln',
	'Cmdr',
	'Col',
	'Cpl',
	'Cpt',
	'Dr',
	'Gen',
	'Gov',
	'Hon',
	'Lady',
	'Lt',
	'Mr',
	'Mrs',
	'Ms',
	'Msgr',
	'Pfc',
	'Pres',
	'Prof',
	'Pvt',
	'Rabbi',
	'Rep',
	'Rev',
	'Sen',
	'Sgt',
	'Sir',
	'Sr',
	'Sra',
	'Srta',
	'Ven',
);

// SPFX tags - surname prefixes
$SPFX_accept = array(
	'al',
	'da',
	'de',
	'dem',
	'den',
	'der',
	'di',
	'du',
	'el',
	'la',
	'van',
	'von',
);

// NSFX tags - name suffixes
$NSFX_accept = array(
	'I',
	'II',
	'III',
	'IV',
	'Jr',
	'Junior',
	'MD',
	'PhD',
	'Senior',
	'Sr',
	'V',
	'VI',
);

// FILE:FORM tags - file formats
$FILE_FORM_accept = array(
	'avi',
	'bmp',
	'gif',
	'jpeg',
	'mp3',
	'ole',
	'pcx',
	'png',
	'tiff',
	'wav',
);

// Fact tags (as opposed to event tags), that don't normally have a value
$emptyfacts = array(
	'ADOP',
	'ANUL',
	'BAPL',
	'BAPM',
	'BARM',
	'BASM',
	'BIRT',
	'BLES',
	'BURI',
	'CENS',
	'CHAN',
	'CHR',
	'CHRA',
	'CONF',
	'CONL',
	'CREM',
	'DATA',
	'DEAT',
	'DIV',
	'DIVF',
	'EMIG',
	'ENDL',
	'ENGA',
	'FCOM',
	'GRAD',
	'HUSB',
	'IMMI',
	'MAP',
	'MARB',
	'MARC',
	'MARL',
	'MARR',
	'MARS',
	'NATU',
	'ORDN',
	'PROB',
	'RESI',
	'RETI',
	'SLGC',
	'SLGS',
	'WIFE',
	'WILL',
	'_HOL',
	'_NMR',
	'_NMAR',
	'_SEPR',
);

// Tags that don't require a PLAC subtag
$nonplacfacts = array(
	'ENDL',
	'NCHI',
	'SLGC',
	'SLGS',
);

// Tags that don't require a DATE subtag
$nondatefacts = array(
	'ABBR',
	'ADDR',
	'AFN',
	'AUTH',
	'CHIL',
	'EMAIL',
	'FAX',
	'FILE',
	'HUSB',
	'NAME',
	'NCHI',
	'NOTE',
	'OBJE',
	'PHON',
	'PUBL',
	'REFN',
	'REPO',
	'RESN',
	'SEX',
	'SOUR',
	'SSN',
	'TEXT',
	'WIFE',
	'WWW',
	'_EMAIL',
);

// Tags that require a DATE:TIME as well as a DATE
$date_and_time=array(
	'BIRT',
	'DEAT',
);

// Level 2 tags that apply to specific Level 1 tags
// Tags are applied in the order they appear here.
$level2_tags = array(
	'_HEB' =>array(
		'NAME',
		'TITL',
	),
	'ROMN' =>array(
		'NAME',
		'TITL',
	),
	'TYPE' =>array(
		'EVEN',
		'FACT',
		'GRAD',
		'IDNO',
		'MARR',
		'ORDN',
		'SSN',
	),
	'AGNC' =>array(
		'EDUC',
		'GRAD',
		'OCCU',
		'ORDN',
		'RETI',
		'TITL',
	),
	'CALN' =>array(
		'REPO',
	),
//	'CEME' =>array( // CEME is NOT a valid 5.5.1 tag
//		'BURI',
//	),
	'RELA' =>array(
		'ASSO',
		'_ASSO',
	),
	'DATE' =>array(
		'ADOP',
		'ANUL',
		'BAPL',
		'BAPM',
		'BARM',
		'BASM',
		'BIRT',
		'BLES',
		'BURI',
		'CENS',
		'CENS',
		'CHR',
		'CHRA',
		'CONF',
		'CONL',
		'CREM',
		'DEAT',
		'DIV',
		'DIVF',
		'DSCR',
		'EDUC',
		'EMIG',
		'ENDL',
		'ENGA',
		'EVEN',
		'FCOM',
		'GRAD',
		'IMMI',
		'MARB',
		'MARC',
		'MARL',
		'MARR',
		'MARS',
		'NATU',
		'OCCU',
		'ORDN',
		'PROB',
		'PROP',
		'RELI',
		'RESI',
		'RETI',
		'SLGC',
		'SLGS',
		'TITL',
		'WILL',
		'_TODO',
	),
	'AGE' =>array(
		'CENS',
		'DEAT',
	),
	'TEMP' =>array(
		'BAPL',
		'CONL',
		'ENDL',
		'SLGC',
		'SLGS',
	),
	'PLAC' =>array(
		'ADOP',
		'ANUL',
		'BAPL',
		'BAPM',
		'BARM',
		'BASM',
		'BIRT',
		'BLES',
		'BURI',
		'CENS',
		'CHR',
		'CHRA',
		'CONF',
		'CONL',
		'CREM',
		'DEAT',
		'DIV',
		'DIVF',
		'EDUC',
		'EMIG',
		'ENDL',
		'ENGA',
		'EVEN',
		'FCOM',
		'GRAD',
		'IMMI',
		'MARB',
		'MARC',
		'MARL',
		'MARR',
		'MARS',
		'NATU',
		'OCCU',
		'ORDN',
		'PROB',
		'PROP',
		'RELI',
		'RESI',
		'RETI',
		'SLGC',
		'SLGS',
		'SSN',
		'TITL',
		'WILL',
	),
	'STAT' =>array(
		'BAPL',
		'CONL',
		'ENDL',
		'SLGC',
		'SLGS',
	),
	'ADDR' =>array(
		'BAPM',
		'BIRT',
		'BURI',
		'CENS',
		'CHR',
		'CHRA',
		'CONF',
		'CREM',
		'DEAT',
		'EDUC',
		'EVEN',
		'GRAD',
		'MARR',
		'OCCU',
		'ORDN',
		'PROP',
		'RESI',
	),
	'CAUS' =>array(
		'DEAT',
	),
	'PHON' =>array(
		'OCCU',
		'RESI',
	),
	'FAX' =>array(
		'OCCU',
		'RESI',
	),
	'URL' =>array(
		'OCCU',
		'RESI',
	),
	'EMAIL' =>array(
		'OCCU',
		'RESI',
	),
	'HUSB' =>array(
		'MARR',
	),
	'WIFE' =>array(
		'MARR',
	),
	'FAMC' =>array(
		'ADOP',
		'SLGC',
	),
	'FILE' =>array(
		'OBJE',
	),
	'_PRIM' =>array(
		'OBJE',
	),
	'EVEN' =>array(
		'DATA',
	),
	'_WT_USER' =>array(
		'_TODO',
	),
	'RELI' =>array(
		//'CHR',
		//'CHRA',
		//'BAPM',
		//'MARR',
		//'BURI',
	),
);

// The order of name parts, when generating names
$STANDARD_NAME_FACTS = array('NAME', 'NPFX', 'GIVN', 'SPFX', 'SURN', 'NSFX');
$REVERSED_NAME_FACTS = array('NAME', 'NPFX', 'SPFX', 'SURN', 'GIVN', 'NSFX');

//Make language names translatable (see http://www.omniglot.com/language/names.htm)
/* I18N: language name - Afrikaans */			WT_I18N::translate('Afrikaans');
/* I18N: language name - Arabic */				WT_I18N::translate('العربية');
/* I18N: language name - Bulgarian */			WT_I18N::translate('български');
/* I18N: language name - Bosnian */				WT_I18N::translate('bosanski');
/* I18N: language name - Catalan */				WT_I18N::translate('català');
/* I18N: language name - Czech */				WT_I18N::translate('čeština');
/* I18N: language name - Danish */				WT_I18N::translate('dansk');
/* I18N: language name - German */				WT_I18N::translate('Deutsch');
/* I18N: language name - Maldivian */			WT_I18N::translate('ދިވެހިބަސް');
/* I18N: language name - Greek */				WT_I18N::translate('Ελληνικά');
/* I18N: language name - English */				WT_I18N::translate('English');
/* I18N: language name - Australian English */	WT_I18N::translate('Australian English');
/* I18N: language name - British English */		WT_I18N::translate('British English');
/* I18N: language name - U.S. English */		WT_I18N::translate('U.S. English');
/* I18N: language name - Spanish */				WT_I18N::translate('español');
/* I18N: language name - Estonian */			WT_I18N::translate('eesti');
/* I18N: language name - Farsi */				WT_I18N::translate('فارسی');
/* I18N: language name - Finnish */				WT_I18N::translate('suomi');
/* I18N: language name - Faroese */				WT_I18N::translate('føroyskt');
/* I18N: language name - French */				WT_I18N::translate('français');
/* I18N: language name - French Canadian */		WT_I18N::translate('français canadien');
/* I18N: language name - Galician */			WT_I18N::translate('galego');
/* I18N: language name - Hawaiian */			WT_I18N::translate('ʻŌlelo Hawaiʻi');
/* I18N: language name - Hebrew */				WT_I18N::translate('עברית');
/* I18N: language name - Croatian */			WT_I18N::translate('hrvatski');
/* I18N: language name - Hungarian */			WT_I18N::translate('magyar');
/* I18N: language name - Indonesian */			WT_I18N::translate('Bahasa Indonesia');
/* I18N: language name - Icelandic */			WT_I18N::translate('íslenska');
/* I18N: language name - Italian */				WT_I18N::translate('italiano');
/* I18N: language name - Japanese */			WT_I18N::translate('日本語');
/* I18N: language name - Georgian */			WT_I18N::translate('ქართული');
/* I18N: language name - Korean */				WT_I18N::translate('한국어');
/* I18N: language name - Lithuanian */			WT_I18N::translate('lietuvių');
/* I18N: language name - Latvian */				WT_I18N::translate('latviešu');
/* I18N: language name - Maori */				WT_I18N::translate('Māori');
/* I18N: language name - Marathi */				WT_I18N::translate('मराठी');
/* I18N: language name - Malay */				WT_I18N::translate('Bahasa Melayu');
/* I18N: language name - Norwegian Bokmal */	WT_I18N::translate('norsk bokmål');
/* I18N: language name - Nepali */				WT_I18N::translate('नेपाली');
/* I18N: language name - Dutch */				WT_I18N::translate('Nederlands');
/* I18N: language name - Norwegian Nynorsk */	WT_I18N::translate('nynorsk');
/* I18N: language name - Occitan */				WT_I18N::translate('occitan');
/* I18N: language name - Polish */				WT_I18N::translate('polski');
/* I18N: language name - Portuguese */			WT_I18N::translate('português');
/* I18N: language name - Portugese Brazilian*/	WT_I18N::translate('português do Brasil');
/* I18N: language name - Romanian */			WT_I18N::translate('română');
/* I18N: language name - Russian */				WT_I18N::translate('русский');
/* I18N: language name - Slovak */				WT_I18N::translate('slovenčina');
/* I18N: language name - Slovenian */			WT_I18N::translate('slovenščina');
/* I18N: language name - Serbian */				WT_I18N::translate('Српски');
/* I18N: language name - Serbian */				WT_I18N::translate('srpski');
/* I18N: language name - Swedish */				WT_I18N::translate('svenska');
/* I18N: language name - Tamil */				WT_I18N::translate('தமிழ்');
/* I18N: language name - Tatar */				WT_I18N::translate('Татар');
/* I18N: language name - Thai */				WT_I18N::translate('تايلند');
/* I18N: language name - Turkish */				WT_I18N::translate('Türkçe');
/* I18N: language name - Ukrainian */			WT_I18N::translate('українська');
/* I18N: language name - Vietnamese */			WT_I18N::translate('Tiếng Việt');
/* I18N: language name - Yiddish */				WT_I18N::translate('ייִדיש');
/* I18N: language name - Chinese */				WT_I18N::translate('中文');
/* I18N: language name - Chinese (Simplified) */  WT_I18N::translate('简体中文'); // Simplified Chinese
/* I18N: language name - Chinese (Traditional) */ WT_I18N::translate('繁體中文'); // Traditional Chinese
