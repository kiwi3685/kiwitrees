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

class KT_Date_Hijri extends KT_Date_Calendar {
	static function CALENDAR_ESCAPE() {
		return '@#DHIJRI@';
	}

	static function calendarName() {
		return /* I18N: The Arabic/Hijri calendar */ KT_I18N::translate('Hijri');
	}

	static function MONTH_TO_NUM($m) {
		static $months=array(''=>0, 'MUHAR'=>1, 'SAFAR'=>2, 'RABIA'=>3, 'RABIT'=>4, 'JUMAA'=>5, 'JUMAT'=>6, 'RAJAB'=>7, 'SHAAB'=>8, 'RAMAD'=>9, 'SHAWW'=>10, 'DHUAQ'=>11, 'DHUAH'=>12);
		if (isset($months[$m])) {
			return $months[$m];
		} else {
			return null;
		}
	}
	static function NUM_TO_MONTH_NOMINATIVE($n, $leap_year) {
		switch ($n) {
		case 1:  return /* I18N: http://en.wikipedia.org/wiki/Muharram                     */ KT_I18N::translate_c('NOMINATIVE', 'Muharram'       );
		case 2:  return /* I18N: http://en.wikipedia.org/wiki/Safar                        */ KT_I18N::translate_c('NOMINATIVE', 'Safar'          );
		case 3:  return /* I18N: http://en.wikipedia.org/wiki/Rabi%27_al-awwal             */ KT_I18N::translate_c('NOMINATIVE', 'Rabi\' al-awwal');
		case 4:  return /* I18N: http://en.wikipedia.org/wiki/Rabi%27_al-thani             */ KT_I18N::translate_c('NOMINATIVE', 'Rabi\' al-thani');
		case 5:  return /* I18N: http://en.wikipedia.org/wiki/Jumada_al-awwal              */ KT_I18N::translate_c('NOMINATIVE', 'Jumada al-awwal');
		case 6:  return /* I18N: http://en.wikipedia.org/wiki/Jumada_al-thani              */ KT_I18N::translate_c('NOMINATIVE', 'Jumada al-thani');
		case 7:  return /* I18N: http://en.wikipedia.org/wiki/Rajab                        */ KT_I18N::translate_c('NOMINATIVE', 'Rajab'          );
		case 8:  return /* I18N: http://en.wikipedia.org/wiki/Sha%27aban                   */ KT_I18N::translate_c('NOMINATIVE', 'Sha\'aban'      );
		case 9:  return /* I18N: http://en.wikipedia.org/wiki/Ramadan_%28calendar_month%29 */ KT_I18N::translate_c('NOMINATIVE', 'Ramadan'        );
		case 10: return /* I18N: http://en.wikipedia.org/wiki/Shawwal                      */ KT_I18N::translate_c('NOMINATIVE', 'Shawwal'        );
		case 11: return /* I18N: http://en.wikipedia.org/wiki/Dhu_al-Qi%27dah              */ KT_I18N::translate_c('NOMINATIVE', 'Dhu al-Qi\'dah' );
		case 12: return /* I18N: http://en.wikipedia.org/wiki/Dhu_al-Hijjah                */ KT_I18N::translate_c('NOMINATIVE', 'Dhu al-Hijjah'  );
		default: return '';
		}
	}
	static function NUM_TO_MONTH_GENITIVE($n, $leap_year) {
		switch ($n) {
		case 1:  return /* I18N: http://en.wikipedia.org/wiki/Muharram                     */ KT_I18N::translate_c('GENITIVE', 'Muharram'       );
		case 2:  return /* I18N: http://en.wikipedia.org/wiki/Safar                        */ KT_I18N::translate_c('GENITIVE', 'Safar'          );
		case 3:  return /* I18N: http://en.wikipedia.org/wiki/Rabi%27_al-awwal             */ KT_I18N::translate_c('GENITIVE', 'Rabi\' al-awwal');
		case 4:  return /* I18N: http://en.wikipedia.org/wiki/Rabi%27_al-thani             */ KT_I18N::translate_c('GENITIVE', 'Rabi\' al-thani');
		case 5:  return /* I18N: http://en.wikipedia.org/wiki/Jumada_al-awwal              */ KT_I18N::translate_c('GENITIVE', 'Jumada al-awwal');
		case 6:  return /* I18N: http://en.wikipedia.org/wiki/Jumada_al-thani              */ KT_I18N::translate_c('GENITIVE', 'Jumada al-thani');
		case 7:  return /* I18N: http://en.wikipedia.org/wiki/Rajab                        */ KT_I18N::translate_c('GENITIVE', 'Rajab'          );
		case 8:  return /* I18N: http://en.wikipedia.org/wiki/Sha%27aban                   */ KT_I18N::translate_c('GENITIVE', 'Sha\'aban'      );
		case 9:  return /* I18N: http://en.wikipedia.org/wiki/Ramadan_%28calendar_month%29 */ KT_I18N::translate_c('GENITIVE', 'Ramadan'        );
		case 10: return /* I18N: http://en.wikipedia.org/wiki/Shawwal                      */ KT_I18N::translate_c('GENITIVE', 'Shawwal'        );
		case 11: return /* I18N: http://en.wikipedia.org/wiki/Dhu_al-Qi%27dah              */ KT_I18N::translate_c('GENITIVE', 'Dhu al-Qi\'dah' );
		case 12: return /* I18N: http://en.wikipedia.org/wiki/Dhu_al-Hijjah                */ KT_I18N::translate_c('GENITIVE', 'Dhu al-Hijjah'  );
		default: return '';
		}
	}
	static function NUM_TO_MONTH_LOCATIVE($n, $leap_year) {
		switch ($n) {
		case 1:  return /* I18N: http://en.wikipedia.org/wiki/Muharram                     */ KT_I18N::translate_c('LOCATIVE', 'Muharram'       );
		case 2:  return /* I18N: http://en.wikipedia.org/wiki/Safar                        */ KT_I18N::translate_c('LOCATIVE', 'Safar'          );
		case 3:  return /* I18N: http://en.wikipedia.org/wiki/Rabi%27_al-awwal             */ KT_I18N::translate_c('LOCATIVE', 'Rabi\' al-awwal');
		case 4:  return /* I18N: http://en.wikipedia.org/wiki/Rabi%27_al-thani             */ KT_I18N::translate_c('LOCATIVE', 'Rabi\' al-thani');
		case 5:  return /* I18N: http://en.wikipedia.org/wiki/Jumada_al-awwal              */ KT_I18N::translate_c('LOCATIVE', 'Jumada al-awwal');
		case 6:  return /* I18N: http://en.wikipedia.org/wiki/Jumada_al-thani              */ KT_I18N::translate_c('LOCATIVE', 'Jumada al-thani');
		case 7:  return /* I18N: http://en.wikipedia.org/wiki/Rajab                        */ KT_I18N::translate_c('LOCATIVE', 'Rajab'          );
		case 8:  return /* I18N: http://en.wikipedia.org/wiki/Sha%27aban                   */ KT_I18N::translate_c('LOCATIVE', 'Sha\'aban'      );
		case 9:  return /* I18N: http://en.wikipedia.org/wiki/Ramadan_%28calendar_month%29 */ KT_I18N::translate_c('LOCATIVE', 'Ramadan'        );
		case 10: return /* I18N: http://en.wikipedia.org/wiki/Shawwal                      */ KT_I18N::translate_c('LOCATIVE', 'Shawwal'        );
		case 11: return /* I18N: http://en.wikipedia.org/wiki/Dhu_al-Qi%27dah              */ KT_I18N::translate_c('LOCATIVE', 'Dhu al-Qi\'dah' );
		case 12: return /* I18N: http://en.wikipedia.org/wiki/Dhu_al-Hijjah                */ KT_I18N::translate_c('LOCATIVE', 'Dhu al-Hijjah'  );
		default: return '';
		}
	}
	static function NUM_TO_MONTH_INSTRUMENTAL($n, $leap_year) {
		switch ($n) {
		case 1:  return /* I18N: http://en.wikipedia.org/wiki/Muharram                     */ KT_I18N::translate_c('INSTRUMENTAL', 'Muharram'       );
		case 2:  return /* I18N: http://en.wikipedia.org/wiki/Safar                        */ KT_I18N::translate_c('INSTRUMENTAL', 'Safar'          );
		case 3:  return /* I18N: http://en.wikipedia.org/wiki/Rabi%27_al-awwal             */ KT_I18N::translate_c('INSTRUMENTAL', 'Rabi\' al-awwal');
		case 4:  return /* I18N: http://en.wikipedia.org/wiki/Rabi%27_al-thani             */ KT_I18N::translate_c('INSTRUMENTAL', 'Rabi\' al-thani');
		case 5:  return /* I18N: http://en.wikipedia.org/wiki/Jumada_al-awwal              */ KT_I18N::translate_c('INSTRUMENTAL', 'Jumada al-awwal');
		case 6:  return /* I18N: http://en.wikipedia.org/wiki/Jumada_al-thani              */ KT_I18N::translate_c('INSTRUMENTAL', 'Jumada al-thani');
		case 7:  return /* I18N: http://en.wikipedia.org/wiki/Rajab                        */ KT_I18N::translate_c('INSTRUMENTAL', 'Rajab'          );
		case 8:  return /* I18N: http://en.wikipedia.org/wiki/Sha%27aban                   */ KT_I18N::translate_c('INSTRUMENTAL', 'Sha\'aban'      );
		case 9:  return /* I18N: http://en.wikipedia.org/wiki/Ramadan_%28calendar_month%29 */ KT_I18N::translate_c('INSTRUMENTAL', 'Ramadan'        );
		case 10: return /* I18N: http://en.wikipedia.org/wiki/Shawwal                      */ KT_I18N::translate_c('INSTRUMENTAL', 'Shawwal'        );
		case 11: return /* I18N: http://en.wikipedia.org/wiki/Dhu_al-Qi%27dah              */ KT_I18N::translate_c('INSTRUMENTAL', 'Dhu al-Qi\'dah' );
		case 12: return /* I18N: http://en.wikipedia.org/wiki/Dhu_al-Hijjah                */ KT_I18N::translate_c('INSTRUMENTAL', 'Dhu al-Hijjah'  );
		default: return '';
		}
	}
	static function NUM_TO_SHORT_MONTH($n, $leap_year) {
		// TODO: Do these have short names?
		return self::NUM_TO_MONTH_NOMINATIVE($n, $leap_year);
	}
	static function NUM_TO_GEDCOM_MONTH($n, $leap_year) {
		switch ($n) {
		case 1:  return 'MUHAR';
		case 2:  return 'SAFAR';
		case 3:  return 'RABIA';
		case 4:  return 'RABIT';
		case 5:  return 'JUMAA';
		case 6:  return 'JUMAT';
		case 7:  return 'RAJAB';
		case 8:  return 'SHAAB';
		case 9:  return 'RAMAD';
		case 10: return 'SHAWW';
		case 11: return 'DHUAQ';
		case 12: return 'DHUAH';
		default: return '';
		}
	}
	static function CAL_START_JD() {
		return 1948440; // @#DHIJRI@ 1 MUHAR 0001 = @#JULIAN@ 16 JUL 0622
	}

	function IsLeapYear() {
		return ((11*$this->y+14)%30)<11;
	}

	static function YMDtoJD($y, $m, $d) {
		return $d+29*($m-1)+(int)((6*$m-1)/11)+$y*354+(int)((3+11*$y)/30)+1948084;
	}

	static function JDtoYMD($j) {
		$y=(int)((30*($j-1948439)+10646)/10631);
		$m=(int)((11*($j-$y*354-(int)((3+11*$y)/30)-1948085)+330)/325);
		$d=$j-29*($m-1)-(int)((6*$m-1)/11)-$y*354-(int)((3+11*$y)/30)-1948084;
		return array($y, $m, $d);
	}
}
