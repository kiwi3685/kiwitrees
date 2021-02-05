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

define('KT_SCRIPT_NAME', 'save.php');
require './includes/session.php';

Zend_Session::writeClose();

// The script must always end by calling one of these two functions.
function ok() {
	global $value;
	header('Content-type: text/html; charset=UTF-8');
	echo $value;
	exit;
}
function fail() {
	// Any 4xx code should work.  jeditable recommends 406
	header('HTTP/1.0 406 Not Acceptable');
	exit;
}

// Do we have a valid CSRF token?
if (!KT_Filter::checkCsrf()) {
	fail();
}

// The data item to updated must identified with a single "id" element.
// The id must be a valid CSS identifier, so it can be used in HTML.
// We use "[A-Za-z0-9_]+" separated by "-".

$id = KT_Filter::post('id', '[a-zA-Z0-9_-]+');
list($table, $id1, $id2, $id3) = explode('-', $id . '---');

// The replacement value.
$value = KT_Filter::post('value', KT_REGEX_UNSAFE);

// Every switch must have a default case, and every case must end in ok() or fail()

switch ($table) {
case 'site_setting':
	//////////////////////////////////////////////////////////////////////////////
	// Table name: KT_SITE_SETTING
	// ID format:  site_setting-{setting_name}
	//////////////////////////////////////////////////////////////////////////////

	// Authorisation
	if (!KT_USER_IS_ADMIN) {
		fail();
	}

	// Validation
	switch ($id1) {
	case 'MAX_EXECUTION_TIME':
		if ($value == '') {
			// Delete the existing value
			$value = null;
		} elseif (!is_numeric($value)) {
			fail();
		}
		break;
	case 'SESSION_TIME':
	case 'SMTP_PORT':
		if (!is_numeric($value)) {
			fail();
		}
		break;
	case 'INDEX_DIRECTORY':
		if (!is_dir($value) || substr($value, -1)!='/') {
			fail();
		}
		break;
	case 'MEMORY_LIMIT':
		if ($value=='') {
			// Delete the existing value
			$value = null;
		} elseif (!preg_match('/^[0-9]+[KMG]$/', $value)) {
			// A number must be followed by K, M or G.
			fail();
		}
		break;
	case 'USE_REGISTRATION_MODULE':
	case 'ALLOW_CHANGE_GEDCOM':
	case 'SMTP_AUTH':
	case 'MAIL_FORMAT':
	case 'SHOW_REGISTER_CAUTION':
	case 'MAINTENANCE':
		$value=(int)$value;
		break;
	case 'WELCOME_TEXT_AUTH_MODE_4':
		// Save a different version of this for each language.
		$id1 = 'WELCOME_TEXT_AUTH_MODE_' . KT_LOCALE;
		break;
	case 'LOGIN_URL':
		if ($value && !preg_match('/^https?:\/\//', $value)) {
			fail();
		}
		break;
	case 'THEME_DIR':
	case 'SERVER_URL':
	case 'SMTP_ACTIVE':
	case 'SMTP_AUTH_USER':
	case 'SMTP_FROM_NAME':
	case 'SMTP_HELO':
	case 'SMTP_HOST':
	case 'SMTP_SSL':
	case 'WELCOME_TEXT_AUTH_MODE':
		break;
	case 'SMTP_AUTH_PASS':
		// The password will be displayed as "click to edit" on screen.
		// Accept the update, but pretend to fail.  This will leave the "click to edit" on screen
		if ($value) {
			KT_Site::preference($id1, $value);
		}
		fail();
	default:
		// An unrecognised setting
		fail();
	}

	// Authorised and valid - make update
	KT_Site::preference($id1, $value);
	ok();

case 'site_access_rule':
	//////////////////////////////////////////////////////////////////////////////
	// Table name: KT_SITE_ACCESS_RULE
	// ID format:  site_access_rule-{column_name}-{user_id}
	//////////////////////////////////////////////////////////////////////////////

	if (!KT_USER_IS_ADMIN) {
		fail();
	}
	switch ($id1) {
	case 'ip_address_start':
	case 'ip_address_end':
		KT_DB::prepare("UPDATE `##site_access_rule` SET {$id1}=INET_ATON(?) WHERE site_access_rule_id=?")
			->execute(array($value, $id2));
		$value=KT_DB::prepare(
			"SELECT INET_NTOA({$id1}) FROM `##site_access_rule` WHERE site_access_rule_id=?"
		)->execute(array($id2))->fetchOne();
		ok();
		break;
	case 'user_agent_pattern':
	case 'rule':
	case 'comment':
		KT_DB::prepare("UPDATE `##site_access_rule` SET {$id1}=? WHERE site_access_rule_id=?")
			->execute(array($value, $id2));
		ok();
	}
	fail();

case 'user':
	//////////////////////////////////////////////////////////////////////////////
	// Table name: KT_USER
	// ID format:  user-{column_name}-{user_id}
	//////////////////////////////////////////////////////////////////////////////

	// Authorisation
	if (!(KT_USER_IS_ADMIN || KT_USER_ID && KT_USER_ID==$id2)) {
		fail();
	}

	// Validation
	switch ($id1) {
	case 'password':
		// The password will be displayed as "click to edit" on screen.
		// Accept the update, but pretend to fail.  This will leave the "click to edit" on screen
		if ($value) {
			set_user_password($id2, $value);
		}
		fail();
	case 'user_name':
	case 'real_name':
	case 'email':
		break;
	default:
		// An unrecognised setting
		fail();
	}

	// Authorised and valid - make update
	try {
		KT_DB::prepare("UPDATE `##user` SET {$id1}=? WHERE user_id=?")
			->execute(array($value, $id2));
		AddToLog('User ID: '.$id2. ' changed '.$id1.' to '.$value, 'auth');
		ok();
	} catch (PDOException $ex) {
		// Duplicate email or username?
		fail();
	}

case 'user_gedcom_setting':
	//////////////////////////////////////////////////////////////////////////////
	// Table name: KT_USER_GEDCOM_SETTING
	// ID format:  user_gedcom_setting-{user_id}-{gedcom_id}-{setting_name}
	//////////////////////////////////////////////////////////////////////////////

	// Authorisation
	if (!(KT_USER_IS_ADMIN || userGedcomAdmin($id2, $id3))) {
		fail();
	}

	// Validation
	switch($id3) {
	case 'rootid':
	case 'gedcomid':
	case 'canedit':
	case 'RELATIONSHIP_PATH_LENGTH':
		break;
	default:
		// An unrecognised setting
		fail();
	}

	// Authorised and valid - make update
	KT_Tree::get($id2)->userPreference($id1, $id3, $value);
	ok();

case 'user_setting':
	//////////////////////////////////////////////////////////////////////////////
	// Table name: KT_USER_SETTING
	// ID format:  user_setting-{user_id}-{setting_name}
	//////////////////////////////////////////////////////////////////////////////

	// Authorisation
	if (!(KT_USER_IS_ADMIN || KT_USER_ID) && in_array($id2, array('language','visible_online','contact_method'))) {
		fail();
	}

	// Validation
	switch ($id2) {
	case 'canadmin':
		// Cannot change our own admin status - either to add it or remove it
		if (KT_USER_ID == $id1) {
			fail();
		}
		break;
	case 'verified_by_admin':
		// Approving for the first time?  Send a confirmation email
		if ($value && get_user_setting($id1, $id2) != $value && get_user_setting($id1, 'sessiontime') == 0) {
			KT_I18N::init(get_user_setting($id1, 'language'));
			KT_Mail::systemMessage(
				KT_TREE,
				$id1,
				KT_I18N::translate(strip_tags(KT_TREE_TITLE) . ' Clippings cart'),
				KT_I18N::translate('User %s has just downloaded a clippings cart file', KT_USER_NAME)
			);
		}
		break;
	case 'auto_accept':
	case 'verified':
	case 'visibleonline':
	case 'max_relation_path':
		$value=(int)$value;
		break;
	case 'contactmethod':
	case 'comment':
	case 'language':
	case 'theme':
		break;
	default:
		// An unrecognised setting
		fail();
	}

	// Authorised and valid - make update
	set_user_setting($id1, $id2, $value);
	ok();

case 'module':
	//////////////////////////////////////////////////////////////////////////////
	// Table name: KT_MODULE
	// ID format:  module-{column}-{module_name}
	//////////////////////////////////////////////////////////////////////////////

	// Authorisation
	if (!KT_USER_IS_ADMIN) {
		fail();
	}

	switch($id1) {
	case 'status':
	case 'tab_order':
	case 'menu_order':
	case 'sidebar_order':
		KT_DB::prepare("UPDATE `##module` SET {$id1}=? WHERE module_name=?")
			->execute(array($value, $id2));
		ok();
	default:
		fail();
	}

default:
	// An unrecognised table
	fail();
}
