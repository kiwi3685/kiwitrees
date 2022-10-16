<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net
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

#[AllowDynamicProperties]
class KT_Gedcom_Code_Name {

	private static $TYPES=array('adopted', 'aka', 'birth', 'change', 'immigrant', 'maiden', 'married', 'religious');

	// Translate a code, for an (optional) record
	public static function getValue($type, $record=null) {
		if ($record instanceof KT_Person) {
			$sex=$record->getSex();
		} else {
			$sex='U';
		}

		switch ($type) {
		case 'adopted':
			switch ($sex) {
			case 'M':
				/* I18N: The name given to a child by its adoptive parents */
				return KT_I18N::translate_c('MALE', 'adopted name');
			case 'F':
				/* I18N: The name given to a child by its adoptive parents */
				return KT_I18N::translate_c('FEMALE', 'adopted name');
			default:
				/* I18N: The name given to a child by its adoptive parents */
				return KT_I18N::translate('adopted name');
			}
		case 'aka':
			switch ($sex) {
			case 'M':
				/* I18N: The name by which an individual is also known.  e.g. a professional name or a stage name */
				return KT_I18N::translate_c('MALE', 'also known as');
			case 'F':
				/* I18N: The name by which an individual is also known.  e.g. a professional name or a stage name */
				return KT_I18N::translate_c('FEMALE', 'also known as');
			default:
				/* I18N: The name by which an individual is also known.  e.g. a professional name or a stage name */
				return KT_I18N::translate('also known as');
			}
		case 'birth':
			switch ($sex) {
			case 'M':
				/* I18N: The name given to an individual at their birth */
				return KT_I18N::translate_c('MALE', 'birth name');
			case 'F':
				/* I18N: The name given to an individual at their birth */
				return KT_I18N::translate_c('FEMALE', 'birth name');
			default:
				/* I18N: The name given to an individual at their birth */
				return KT_I18N::translate('birth name');
			}
		case 'change':
			switch ($sex) {
			case 'M':
				/* I18N: A name chosen by an individual, to replace their existing name (whether legal or otherwise) */
				return KT_I18N::translate_c('MALE', 'change of name');
			case 'F':
				/* I18N: A name chosen by an individual, to replace their existing name (whether legal or otherwise) */
				return KT_I18N::translate_c('FEMALE', 'change of name');
			default:
				/* I18N: A name chosen by an individual, to replace their existing name (whether legal or otherwise) */
				return KT_I18N::translate('change of name');
			}
		case 'immigrant':
			switch ($sex) {
			case 'M':
				/* I18N: A name taken on immigration - e.g. migrants to the USA frequently anglicized their names */
				return KT_I18N::translate_c('MALE', 'immigration name');
			case 'F':
				/* I18N: A name taken on immigration - e.g. migrants to the USA frequently anglicized their names */
				return KT_I18N::translate_c('FEMALE', 'immigration name');
			default:
				/* I18N: A name taken on immigration - e.g. migrants to the USA frequently anglicized their names */
				return KT_I18N::translate('immigration name');
			}
		case 'maiden':
			// Only women have “maiden” names!
			return
				/* I18N: A woman’s name, before she marries (in cultures where women take their new husband’s name on marriage) */
				KT_I18N::translate('maiden name');
		case 'married':
			switch ($sex) {
			case 'M':
				/* I18N: A name taken on marriage - usually the wife takes the husband’s surname */
				return KT_I18N::translate_c('MALE', 'married name');
			case 'F':
				/* I18N: A name taken on marriage - usually the wife takes the husband’s surname */
				return KT_I18N::translate_c('FEMALE', 'married name');
			default:
				/* I18N: A name taken on marriage - usually the wife takes the husband’s surname */
				return KT_I18N::translate('married name');
			}
		case 'religious':
			switch ($sex) {
			case 'M':
				/* I18N: A name taken when entering a religion or a religious order */
				return KT_I18N::translate_c('MALE', 'religious name');
			case 'F':
				/* I18N: A name taken when entering a religion or a religious order */
				return KT_I18N::translate_c('FEMALE', 'religious name');
			default:
				/* I18N: A name taken when entering a religion or a religious order */
				return KT_I18N::translate('religious name');
			}
		default:
			return $type;
		}
	}

	// A list of all possible values for NAME types
	public static function getValues($record=null) {
		$values=array();
		foreach (self::$TYPES as $type) {
			$values[$type]=self::getValue($type, $record);
		}
		uasort($values, 'utf8_strcasecmp');
		return $values;
	}
}
