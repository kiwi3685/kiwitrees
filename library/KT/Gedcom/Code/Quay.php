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

#[AllowDynamicProperties]
class KT_Gedcom_Code_Quay {
	private static $TYPES=array('3', '2', '1', '0');

	// Translate a code, for an optional record
	public static function getValue($type) {
		switch ($type) {
		case '3':
			return /* I18N: Quality of source information - GEDCOM tag “QUAY 3” */ KT_I18N::translate('primary evidence');
		case '2':
			return /* I18N: Quality of source information - GEDCOM tag “QUAY 2” */ KT_I18N::translate('secondary evidence');
		case '1':
			return /* I18N: Quality of source information - GEDCOM tag “QUAY 1” */ KT_I18N::translate('questionable evidence');
		case '0':
			return /* I18N: Quality of source information - GEDCOM tag “QUAY 0” */ KT_I18N::translate('unreliable evidence');
		default:
			return $type;
		}
	}

	// A list of all possible values for QUAY
	public static function getValues() {
		$values=array();
		foreach (self::$TYPES as $type) {
			$values[$type]=self::getValue($type);
		}
		return $values;
	}
}
