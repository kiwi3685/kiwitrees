<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2021 kiwitrees.net
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

class KT_Gedcom_Code_Rela {

	private static $TYPES = array(
		'attendant', 'attending', 'beneficiary', 'best_man', 'bridesmaid', 'buyer',
		'circumciser', 'civil_registrar', 'employee', 'employer', 'foster_child',
		'foster_father', 'foster_mother', 'friend', 'godfather', 'godmother',
		'godparent', 'godson', 'goddaughter', 'godchild', 'guardian',
		'informant', 'lodger', 'nanny', 'nurse', 'owner',
		'priest', 'rabbi', 'registry_officer', 'religious_witness', 'seller', 'servant',
		'slave', 'twin', 'ward', 'witness',
	);

	// Translate a code, for an (optional) record
	public static function getValue($type, $record=null) {
		if ($record instanceof KT_Person) {
			$sex=$record->getSex();
		} else {
			$sex='U';
		}

		switch ($type) {
		case 'attendant':
			switch ($sex) {
			case 'M': return KT_I18N::translate_c('MALE',   'Attendant');
			case 'F': return KT_I18N::translate_c('FEMALE', 'Attendant');
			default:  return KT_I18N::translate  (          'Attendant');
			}
		case 'attending':
			switch ($sex) {
			case 'M': return KT_I18N::translate_c('MALE',   'Attending');
			case 'F': return KT_I18N::translate_c('FEMALE', 'Attending');
			default:  return KT_I18N::translate  (          'Attending');
			}
		case 'beneficiary':
			switch ($sex) {
			case 'M': return KT_I18N::translate_c('MALE',   'Beneficiary');
			case 'F': return KT_I18N::translate_c('FEMALE', 'Beneficiary');
			default:  return KT_I18N::translate  (          'Beneficiary');
			}
		case 'best_man':
			// always male
			return KT_I18N::translate('Best Man');
		case 'bridesmaid':
			// always female
			return KT_I18N::translate('Bridesmaid');
		case 'buyer':
			switch ($sex) {
			case 'M': return KT_I18N::translate_c('MALE',   'Buyer');
			case 'F': return KT_I18N::translate_c('FEMALE', 'Buyer');
			default:  return KT_I18N::translate  (          'Buyer');
			}
		case 'circumciser':
			// always male
			return KT_I18N::translate('Circumciser');
		case 'civil_registrar':
			switch ($sex) {
			case 'M': return KT_I18N::translate_c('MALE',   'Civil Registrar');
			case 'F': return KT_I18N::translate_c('FEMALE', 'Civil Registrar');
			default:  return KT_I18N::translate  (          'Civil Registrar');
			}
		case 'employee':
			switch ($sex) {
			case 'M': return KT_I18N::translate_c('MALE',   'Employee');
			case 'F': return KT_I18N::translate_c('FEMALE', 'Employee');
			default:  return KT_I18N::translate  (          'Employee');
			}
		case 'employer':
			switch ($sex) {
			case 'M': return KT_I18N::translate_c('MALE',   'Employer');
			case 'F': return KT_I18N::translate_c('FEMALE', 'Employer');
			default:  return KT_I18N::translate  (          'Employer');
			}
		case 'foster_child':
			// no sex implied
			return KT_I18N::translate('Foster Child');
		case 'foster_father':
			// always male
			return KT_I18N::translate('Foster Father');
		case 'foster_mother':
			// always female
			return KT_I18N::translate('Foster Mother');
		case 'friend':
			switch ($sex) {
			case 'M': return KT_I18N::translate_c('MALE',   'Friend');
			case 'F': return KT_I18N::translate_c('FEMALE', 'Friend');
			default:  return KT_I18N::translate  (          'Friend');
			}
		case 'godfather':
			// always male
			return KT_I18N::translate('Godfather');
		case 'godmother':
			// always female
			return KT_I18N::translate('Godmother');
		case 'godparent':
			switch ($sex) {
			case 'M':
				return KT_I18N::translate('Godfather');
			case 'F':
				return KT_I18N::translate('Godmother');
			default:
				return KT_I18N::translate('Godparent');
			}
		case 'godson':
			// always male
			return KT_I18N::translate('Godson');
		case 'goddaughter':
			// always female
			return KT_I18N::translate('Goddaughter');
		case 'godchild':
			switch ($sex) {
			case 'M': return KT_I18N::translate('Godson');
			case 'F': return KT_I18N::translate('Goddaughter');
			default:  return KT_I18N::translate('Godchild');
			}
		case 'guardian':
			switch ($sex) {
			case 'M': return KT_I18N::translate_c('MALE',   'Guardian');
			case 'F': return KT_I18N::translate_c('FEMALE', 'Guardian');
			default:  return KT_I18N::translate  (          'Guardian');
			}
		case 'informant':
			switch ($sex) {
			case 'M': return KT_I18N::translate_c('MALE',   'Informant');
			case 'F': return KT_I18N::translate_c('FEMALE', 'Informant');
			default:  return KT_I18N::translate  (          'Informant');
			}
		case 'lodger':
			switch ($sex) {
			case 'M': return KT_I18N::translate_c('MALE',   'Lodger');
			case 'F': return KT_I18N::translate_c('FEMALE', 'Lodger');
			default:  return KT_I18N::translate  (          'Lodger');
			}
		case 'nanny':
			// no sex implied
			return KT_I18N::translate('Nanny');
		case 'nurse':
			switch ($sex) {
			case 'M': return KT_I18N::translate_c('MALE',   'Nurse');
			case 'F': return KT_I18N::translate_c('FEMALE', 'Nurse');
			default:  return KT_I18N::translate  (          'Nurse');
			}
		case 'owner':
			switch ($sex) {
			case 'M': return KT_I18N::translate_c('MALE',   'Owner');
			case 'F': return KT_I18N::translate_c('FEMALE', 'Owner');
			default:  return KT_I18N::translate  (          'Owner');
			}
		case 'priest':
			// no sex implied
			return KT_I18N::translate('Priest');
		case 'rabbi':
			// always male
			return KT_I18N::translate('Rabbi');
		case 'registry_officer':
			switch ($sex) {
			case 'M': return KT_I18N::translate_c('MALE',   'Registry Officer');
			case 'F': return KT_I18N::translate_c('FEMALE', 'Registry Officer');
			default:  return KT_I18N::translate  (          'Registry Officer');
			}
		case 'religious_witness':
		// Special form of witness used in some countries - notably Denmark`s `fadder`
			switch ($sex) {
			case 'M': return KT_I18N::translate_c('MALE',   'Religious witness');
			case 'F': return KT_I18N::translate_c('FEMALE', 'Religious witness');
			default:  return KT_I18N::translate  (          'Religious witness');
			}
		case 'seller':
			switch ($sex) {
			case 'M': return KT_I18N::translate_c('MALE',   'Seller');
			case 'F': return KT_I18N::translate_c('FEMALE', 'Seller');
			default:  return KT_I18N::translate  (          'Seller');
			}
		case 'servant':
			switch ($sex) {
			case 'M': return KT_I18N::translate_c('MALE',   'Servant');
			case 'F': return KT_I18N::translate_c('FEMALE', 'Servant');
			default:  return KT_I18N::translate  (          'Servant');
			}
		case 'slave':
			switch ($sex) {
			case 'M': return KT_I18N::translate_c('MALE',   'Slave');
			case 'F': return KT_I18N::translate_c('FEMALE', 'Slave');
			default:  return KT_I18N::translate  (          'Slave');
			}
		case 'twin':
			switch ($sex) {
			case 'M': return KT_I18N::translate('Twin brother');
			case 'F': return KT_I18N::translate('Twin sister');
			default:  return KT_I18N::translate('Twin');
			}
		case 'ward':
			switch ($sex) {
			case 'M': return KT_I18N::translate_c('MALE',   'Ward');
			case 'F': return KT_I18N::translate_c('FEMALE', 'Ward');
			default:  return KT_I18N::translate  (          'Ward');
			}
		case 'witness':
			// Do we need separate male/female translations for this?
			return KT_I18N::translate('Witness');
		default:
			return $type;
		}
	}

	// A list of all possible values for RELA
	public static function getValues($record=null) {
		$values=array();
		foreach (self::$TYPES as $type) {
			$values[$type]=self::getValue($type, $record);
		}
		uasort($values, 'utf8_strcasecmp');
		return $values;
	}
}
