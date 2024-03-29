<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net
 * 
 * Derived from webtrees (www.webtrees.net)
 * Copyright (C) 2010 to 2012 webtrees development team
 * 
 * Derived from PhpGedView (phpgedview.sourceforge.net)
 * Copyright (C) 2002 to 2010 PGV Development Team
 * 
 * Kiwitrees is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class KT_Date_Jewish extends KT_Date_Calendar {
	static function CALENDAR_ESCAPE() {
		return '@#DHEBREW@';
	}

	static function calendarName() {
		return /* I18N: The Hebrew/Jewish calendar */ KT_I18N::translate('Jewish');
	}

	const GERSHAYIM="״";
	const GERSH="׳";
	const ALAFIM="אלפים";

	function FormatDayZeros() {
		if (KT_LOCALE=='he') {
			return $this->NumToHebrew($this->d);
		} else {
			return $this->d;
		}
	}

	function FormatDay() {
		if (KT_LOCALE=='he') {
			return $this->NumToHebrew($this->d);
		} else {
			return $this->d;
		}
	}

	function FormatShortYear() {
		if (KT_LOCALE=='he') {
			return $this->NumToHebrew($this->y%1000);
		} else {
			return $this->y;
		}
	}

	function FormatLongYear() {
		if (KT_LOCALE=='he') {
			return $this->NumToHebrew($this->y);
		} else {
			return $this->y;
		}
	}

	static function MONTH_TO_NUM($m) {
		static $months=array(''=>0, 'TSH'=>1, 'CSH'=>2, 'KSL'=>3, 'TVT'=>4, 'SHV'=>5, 'ADR'=>6, 'ADS'=>7, 'NSN'=>8, 'IYR'=>9, 'SVN'=>10, 'TMZ'=>11, 'AAV'=>12, 'ELL'=>13);
		if (isset($months[$m])) {
			return $months[$m];
		} else {
			return null;
		}
	}
	static function NUM_TO_MONTH_NOMINATIVE($n, $leap_year) {
		switch ($n) {
		case 1:  return KT_I18N::translate_c('NOMINATIVE', 'Tishrei');
		case 2:  return KT_I18N::translate_c('NOMINATIVE', 'Heshvan');
		case 3:  return KT_I18N::translate_c('NOMINATIVE', 'Kislev');
		case 4:  return KT_I18N::translate_c('NOMINATIVE', 'Tevet');
		case 5:  return KT_I18N::translate_c('NOMINATIVE', 'Shevat');
		case 6:  if ($leap_year) return KT_I18N::translate_c('NOMINATIVE', 'Adar I'); else return KT_I18N::translate_c('NOMINATIVE', 'Adar');
		case 7:  return KT_I18N::translate_c('NOMINATIVE', 'Adar II');
		case 8:  return KT_I18N::translate_c('NOMINATIVE', 'Nissan');
		case 9:  return KT_I18N::translate_c('NOMINATIVE', 'Iyar');
		case 10: return KT_I18N::translate_c('NOMINATIVE', 'Sivan');
		case 11: return KT_I18N::translate_c('NOMINATIVE', 'Tamuz');
		case 12: return KT_I18N::translate_c('NOMINATIVE', 'Av');
		case 13: return KT_I18N::translate_c('NOMINATIVE', 'Elul');
		default: return '';
		}
	}
	static function NUM_TO_MONTH_GENITIVE($n, $leap_year) {
		switch ($n) {
		case 1:  return KT_I18N::translate_c('GENITIVE', 'Tishrei');
		case 2:  return KT_I18N::translate_c('GENITIVE', 'Heshvan');
		case 3:  return KT_I18N::translate_c('GENITIVE', 'Kislev');
		case 4:  return KT_I18N::translate_c('GENITIVE', 'Tevet');
		case 5:  return KT_I18N::translate_c('GENITIVE', 'Shevat');
		case 6:  if ($leap_year) return KT_I18N::translate_c('GENITIVE', 'Adar I'); else return KT_I18N::translate_c('GENITIVE', 'Adar');
		case 7:  return KT_I18N::translate_c('GENITIVE', 'Adar II');
		case 8:  return KT_I18N::translate_c('GENITIVE', 'Nissan');
		case 9:  return KT_I18N::translate_c('GENITIVE', 'Iyar');
		case 10: return KT_I18N::translate_c('GENITIVE', 'Sivan');
		case 11: return KT_I18N::translate_c('GENITIVE', 'Tamuz');
		case 12: return KT_I18N::translate_c('GENITIVE', 'Av');
		case 13: return KT_I18N::translate_c('GENITIVE', 'Elul');
		default: return '';
		}
	}
	static function NUM_TO_MONTH_LOCATIVE($n, $leap_year) {
		switch ($n) {
		case 1:  return KT_I18N::translate_c('LOCATIVE', 'Tishrei');
		case 2:  return KT_I18N::translate_c('LOCATIVE', 'Heshvan');
		case 3:  return KT_I18N::translate_c('LOCATIVE', 'Kislev');
		case 4:  return KT_I18N::translate_c('LOCATIVE', 'Tevet');
		case 5:  return KT_I18N::translate_c('LOCATIVE', 'Shevat');
		case 6:  if ($leap_year) return KT_I18N::translate_c('LOCATIVE', 'Adar I'); else return KT_I18N::translate_c('LOCATIVE', 'Adar');
		case 7:  return KT_I18N::translate_c('LOCATIVE', 'Adar II');
		case 8:  return KT_I18N::translate_c('LOCATIVE', 'Nissan');
		case 9:  return KT_I18N::translate_c('LOCATIVE', 'Iyar');
		case 10: return KT_I18N::translate_c('LOCATIVE', 'Sivan');
		case 11: return KT_I18N::translate_c('LOCATIVE', 'Tamuz');
		case 12: return KT_I18N::translate_c('LOCATIVE', 'Av');
		case 13: return KT_I18N::translate_c('LOCATIVE', 'Elul');
		default: return '';
		}
	}
	static function NUM_TO_MONTH_INSTRUMENTAL($n, $leap_year) {
		switch ($n) {
		case 1:  return KT_I18N::translate_c('INSTRUMENTAL', 'Tishrei');
		case 2:  return KT_I18N::translate_c('INSTRUMENTAL', 'Heshvan');
		case 3:  return KT_I18N::translate_c('INSTRUMENTAL', 'Kislev');
		case 4:  return KT_I18N::translate_c('INSTRUMENTAL', 'Tevet');
		case 5:  return KT_I18N::translate_c('INSTRUMENTAL', 'Shevat');
		case 6:  if ($leap_year) return KT_I18N::translate_c('INSTRUMENTAL', 'Adar I'); else return KT_I18N::translate_c('INSTRUMENTAL', 'Adar');
		case 7:  return KT_I18N::translate_c('INSTRUMENTAL', 'Adar II');
		case 8:  return KT_I18N::translate_c('INSTRUMENTAL', 'Nissan');
		case 9:  return KT_I18N::translate_c('INSTRUMENTAL', 'Iyar');
		case 10: return KT_I18N::translate_c('INSTRUMENTAL', 'Sivan');
		case 11: return KT_I18N::translate_c('INSTRUMENTAL', 'Tamuz');
		case 12: return KT_I18N::translate_c('INSTRUMENTAL', 'Av');
		case 13: return KT_I18N::translate_c('INSTRUMENTAL', 'Elul');
		default: return '';
		}
	}
	static function NUM_TO_SHORT_MONTH($n, $leap_year) {
		// TODO: Do these have short names?
		return self::NUM_TO_MONTH_NOMINATIVE($n, $leap_year);
	}
	static function NUM_TO_GEDCOM_MONTH($n, $leap_year) {
		switch ($n) {
		case 1:  return 'TSH';
		case 2:  return 'CSH';
		case 3:  return 'KSL';
		case 4:  return 'TVT';
		case 5:  return 'SHV';
		case 6:  return 'ADR';
		case 7:  return 'ADS';
		case 8:  return 'NSN';
		case 9:  return 'IYR';
		case 10: return 'SVN';
		case 11: return 'TMZ';
		case 12: return 'AAV';
		case 13: return 'ELL';
		default: return '';
		}
	}
	static function NUM_MONTHS() {
		return 13;
	}
	static function CAL_START_JD() {
		return 347998; // 01 TSH 0001 = @#JULIAN@ 7 OCT 3761B.C.
	}

	function NextMonth() {
		if ($this->m==6 && !$this->IsLeapYear())
			return array($this->y, 8);
		else
			return array($this->y+($this->m==13?1:0), ($this->m%13)+1);
	}

	function IsLeapYear() {
		return ((7*$this->y+1)%19)<7;
	}

	// TODO implement this function locally
	static function YMDtoJD($y, $mh, $d) {
		if (function_exists('JewishToJD'))
			return JewishToJD($mh, $d, $y);
		else
			return 0;
	}

	// TODO implement this function locally
	static function JDtoYMD($j) {
		if (function_exists('JdToJewish'))
			list($m, $d, $y)=explode('/', JDToJewish($j));
		else
			list($m, $d, $y)=array(0, 0, 0);
		return array($y, $m, $d);
	}

	// Convert a decimal number to hebrew - like roman numerals, but with extra punctuation
	// and special rules.
	static function NumToHebrew($num) {
		$DISPLAY_JEWISH_THOUSANDS=false;

		static $jHundreds = array("", "ק", "ר", "ש", "ת", "תק", "תר","תש", "תת", "תתק");
		static $jTens     = array("", "י", "כ", "ל", "מ", "נ", "ס", "ע", "פ", "צ");
		static $jTenEnds  = array("", "י", "ך", "ל", "ם", "ן", "ס", "ע", "ף", "ץ");
		static $tavTaz    = array("ט״ו", "ט״ז");
		static $jOnes     = array("", "א", "ב", "ג", "ד", "ה", "ו", "ז", "ח", "ט");

		$shortYear = $num %1000; //discard thousands
		//next check for all possible single Hebrew digit years
		$singleDigitYear=($shortYear < 11 || ($shortYear <100 && $shortYear % 10 == 0)  || ($shortYear <= 400 && $shortYear % 100 ==0));
		$thousands = $num / 1000; //get # thousands
		$sb = "";
		//append thousands to String
		if ($num % 1000 == 0) { // in year is 5000, 4000 etc
			$sb .= $jOnes[$thousands];
			$sb .= self::GERSH;
			$sb .= " ";
			$sb .= self::ALAFIM; //add # of thousands plus word thousand (overide alafim boolean)
		} else if ($DISPLAY_JEWISH_THOUSANDS) { // if alafim boolean display thousands
			$sb .= $jOnes[$thousands];
			$sb .= self::GERSH; //append thousands quote
			$sb .= " ";
		}
		$num = $num % 1000; //remove 1000s
		$hundreds = $num / 100; // # of hundreds
		$sb .= $jHundreds[$hundreds]; //add hundreds to String
		$num = $num % 100; //remove 100s
		if ($num == 15) { //special case 15
			$sb .= $tavTaz[0];
		} else if ($num == 16) { //special case 16
			$sb .= $tavTaz[1];
		} else {
			$tens = $num / 10;
			if ($num % 10 == 0) {                                    // if evenly divisable by 10
				if ($singleDigitYear == false) {
					$sb .= $jTenEnds[$tens]; // use end letters so that for example 5750 will end with an end nun
				} else {
					$sb .= $jTens[$tens]; // use standard letters so that for example 5050 will end with a regular nun
				}
			} else {
				$sb .= $jTens[$tens];
				$num = $num % 10;
				$sb .= $jOnes[$num];
			}
		}
		if ($singleDigitYear == true) {
			$sb .= self::GERSH; //append single quote
		} else { // append double quote before last digit
			$pos1 = strlen($sb)-2;
			$sb = substr($sb, 0, $pos1) . self::GERSHAYIM . substr($sb, $pos1);
			$sb = str_replace(self::GERSHAYIM . self::GERSHAYIM, self::GERSHAYIM, $sb); //replace double gershayim with single instance
		}
		return $sb;
	}
}
