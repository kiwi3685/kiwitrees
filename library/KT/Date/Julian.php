<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2017 kiwitrees.net
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

class KT_Date_Julian extends KT_Date_Calendar {
	var $new_old_style = false;

	static function CALENDAR_ESCAPE() {
		return '@#DJULIAN@';
	}

	static function calendarName() {
		return /* I18N: The julian calendar */ KT_I18N::translate('Julian');
	}

	static function NextYear($year) {
		if ($year == -1) {
			return 1;
		} else {
			return $year + 1;
		}
	}

	function IsLeapYear() {
		if ($this->y > 0) {
			return $this->y%4 == 0;
		} else {
			return $this->y%4 == -1;
		}
	}

	static function YMDtoJD($y, $m, $d) {
		if ($y<0) // 0=1BC, -1=2BC, etc.
			++$y;
		$a=(int)((14-$m)/12);
		$y=$y+4800-$a;
		$m=$m+12*$a-3;
		return $d+(int)((153*$m+2)/5)+365*$y+(int)($y/4)-32083;
	}

	static function JDtoYMD($j) {
		$c	= $j + 32082;
		$d	= (int) ((4 * $c + 3) / 1461);
		$e	= $c - (int) (1461 * $d / 4);
		$m	= (int) ((5 * $e + 2) / 153);
		$day	= $e - (int) ((153 * $m + 2) / 5) + 1;
		$month	= $m + 3 - 12 * (int) ($m / 10);
		$year	= $d - 4800 + (int) ($m / 10);
		if ($year < 1) {
			// 0 is 1 BCE, -1 is 2 BCE, etc.
			$year--;
		}

		return array($year, $month, $day);
	}

	// Process new-style/old-style years and years BC
	function ExtractYear($year) {
		if (preg_match('/^(\d\d\d\d)\/\d{1,4}$/', $year, $match)) {
			// Assume the first year is correct
			$this->new_old_style = true;

			return $match[1] + 1;
		} elseif (preg_match('/^(\d+) B\.C\.$/', $year, $match)) {
			return -$match[1];
		} else {
			return (int) $year;
		}
	}

	function FormatLongYear() {
		if ($this->y < 0) {
			return /*  KT_I18N: BCE=Before the Common Era, for Julian years < 0.  See http://en.wikipedia.org/wiki/Common_Era */
				KT_I18N::translate('%s&nbsp;BCE', KT_I18N::digits(-$this->y));
		} else {
			if ($this->new_old_style) {
				return KT_I18N::translate('%s&nbsp;CE', KT_I18N::digits(sprintf('%d/%02d', $this->y - 1, $this->y % 100)));
			} else {
				return /* I18N: CE=Common Era, for Julian years > 0.  See http://en.wikipedia.org/wiki/Common_Era */
					KT_I18N::translate('%s&nbsp;CE', KT_I18N::digits($this->y));
			}
		}
	}

	function FormatGedcomYear() {
		if ($this->y < 0) {
			return sprintf('%04d B.C.', -$this->y);
		} else {
			if ($this->new_old_style) {
				return sprintf('%04d/%02d', $this->y - 1, $this->y % 100);
			} else {
				return sprintf('%04d', $this->y);
			}
		}
	}
}
