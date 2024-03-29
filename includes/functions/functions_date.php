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

function get_age_at_event($age_string, $show_years) {
	switch (strtoupper($age_string)) {
	case 'CHILD':
		return KT_I18N::translate('Child');
	case 'INFANT':
		return KT_I18N::translate('Infant');
	case 'STILLBORN':
		return KT_I18N::translate('Stillborn');
	default:
		return preg_replace_callback(
			array(
				'/(\d+)([ymwd])/',
			),
			function ($match) use ($age_string, $show_years) {
				switch (KT_LOCALE) {
				case 'pl':
					$show_years = true;
				}
				switch ($match[2]) {
				case 'y':
					if ($show_years || preg_match('/[dm]/', $age_string)) {
						return KT_I18N::plural('%s year', '%s years', $match[1], KT_I18N::digits($match[1]));
					} else {
						return KT_I18N::digits($match[1]);
					}
				case 'm':
					return KT_I18N::plural('%s month', '%s months', $match[1], KT_I18N::digits($match[1]));
				case 'w':
					return KT_I18N::plural('%s week', '%s weeks', $match[1], KT_I18N::digits($match[1]));
				case 'd':
					return KT_I18N::plural('%s day', '%s days', $match[1], KT_I18N::digits($match[1]));
				}
			},
			$age_string
		);
	}
}

/**
* Parse a time string into its different parts
* @param string $timestr the time as it was taken from the TIME tag
* @return array returns an array with the hour, minutes, and seconds
*/
function parse_time($timestr)
{
	$time = explode(':', $timestr.':0:0');
	$time[0] = min(((int) $time[0]), 23); // Hours: integer, 0 to 23
	$time[1] = min(((int) $time[1]), 59); // Minutes: integer, 0 to 59
	$time[2] = min(((int) $time[2]), 59); // Seconds: integer, 0 to 59
	$time["hour"] = $time[0];
	$time["minutes"] = $time[1];
	$time["seconds"] = $time[2];

	return $time;
}

////////////////////////////////////////////////////////////////////////////////
// Convert a unix timestamp into a formated date-time value, for logs, etc.
// We can't just use date("$DATE_FORMAT- $TIME_FORMAT") as this doesn't
// support internationalisation.
// Don't attempt to convert into other calendars, as not all days start at
// midnight, and we can only get it wrong.
////////////////////////////////////////////////////////////////////////////////
function format_timestamp($time) {
	global $DATE_FORMAT, $TIME_FORMAT;

	$time_fmt = $TIME_FORMAT;
	// PHP::date() doesn't do I18N.  Do it ourselves....
	preg_match_all('/%[^%]/', $time_fmt, $matches);
	foreach ($matches[0] as $match) {
		switch ($match) {
		case '%a':
			$t = gmdate('His', $time);
			if ($t == '000000') {
				$time_fmt = str_replace($match, /* I18N: time format “%a” - exactly 00:00:00 */ KT_I18N::translate('midnight'), $time_fmt);
			} elseif ($t < '120000') {
				$time_fmt = str_replace($match, /* I18N: time format “%a” - between 00:00:01 and 11:59:59 */ KT_I18N::translate('a.m.'), $time_fmt);
			} elseif ($t == '120000') {
				$time_fmt = str_replace($match, /* I18N: time format “%a” - exactly 12:00:00 */ KT_I18N::translate('noon'), $time_fmt);
			} else {
				$time_fmt = str_replace($match, /* I18N: time format “%a” - between 12:00:01 and 23:59:59 */ KT_I18N::translate('p.m.'), $time_fmt);
			}
			break;
		case '%A':
			$t=date('His', $time);
			if ($t=='000000') {
				$time_fmt = str_replace($match, /* I18N: time format “%A” - exactly 00:00:00 */ KT_I18N::translate('Midnight'), $time_fmt);
			} elseif ($t < '120000') {
				$time_fmt = str_replace($match, /* I18N: time format “%A” - between 00:00:01 and 11:59:59 */ KT_I18N::translate('A.M.'), $time_fmt);
			} elseif ($t == '120000') {
				$time_fmt = str_replace($match, /* I18N: time format “%A” - exactly 12:00:00 */ KT_I18N::translate('Noon'), $time_fmt);
			} else {
				$time_fmt = str_replace($match, /* I18N: time format “%A” - between 12:00:01 and 23:59:59 */ KT_I18N::translate('P.M.'), $time_fmt);
			}
				break;
		default:
			$time_fmt = str_replace($match, KT_I18N::digits(date(substr($match, -1), $time)), $time_fmt);
		}
	}

	return timestamp_to_gedcom_date($time)->Display(false, $DATE_FORMAT).  '<span class="date"> - '.$time_fmt.'</span>';
}

////////////////////////////////////////////////////////////////////////////////
// Convert a unix-style timestamp into a KT_Date object
////////////////////////////////////////////////////////////////////////////////
function timestamp_to_gedcom_date($time) {
	return new KT_Date(strtoupper(date('j M Y', $time)));
}
