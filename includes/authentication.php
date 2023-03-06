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

// authenticate a username and password
//
// On success, store the user-id in the session and return it
// On failure, return an error code
function authenticateUser($user_name, $password) {
	global $KT_SESSION;

	// If no cookies are available, then we cannot log in.
	if (!isset($_COOKIE[KT_SESSION_NAME])) {
		return -5;
	}

	if ($user_id = get_user_id($user_name)) {
		if (check_user_password($user_id, $password)) {
			$is_admin = get_user_setting($user_id, 'canadmin');
			$verified = get_user_setting($user_id, 'verified');
			$approved = get_user_setting($user_id, 'verified_by_admin');
			if ($verified && $approved || $is_admin) {
				// Whenever we change our authorisation level change the session ID
				Zend_Session::regenerateId();
				$KT_SESSION->kt_user = $user_id;
				AddToLog('Login successful ->'.$user_name.'<-', 'auth');
				return $user_id;
			} elseif (!$is_admin && !$verified) {
				AddToLog('Login failed ->'.$user_name.'<- not verified', 'auth');
				return -1;
			} elseif (!$is_admin && !$approved) {
				AddToLog('Login failed ->'.$user_name.'<- not approved', 'auth');
				return -2;
			}
		} else {
			AddToLog('Login failed ->'.$user_name.'<- bad password', 'auth');
			return -3;
		}
	}
	AddToLog('Login failed ->'.$user_name.'<- bad username', 'auth');
	return -4;
}

/**
 * logs a user out of the system
 * @param string $user_id logout a specific user
 */
function userLogout($user_id) {
	AddToLog('Logout '.getUserName($user_id), 'auth');
	// If we are logging ourself out, then end our session too.
	if (KT_USER_ID == $user_id) {
		Zend_Session::destroy();
	}
}

/**
 * get the current user's ID and Name
 *
 * Returns 0 and NULL if we are not logged in.
 *
 * If you want to embed kiwitrees within a content management system, you would probably
 * rewrite these functions to extract the data from the parent system, and then
 * populate kiwitrees' user/user_setting/user_gedcom_setting tables as appropriate.
 *
 */

function getUserId() {
	global $KT_SESSION;

	return (int) $KT_SESSION->kt_user;
}

function getUserName() {
	if (getUserId()) {
		return get_user_name(getUserId());
	} else {
		return null;
	}
}

/**
 * check if given username is an admin
 */
function userIsAdmin($user_id = KT_USER_ID) {
	if ($user_id) {
		return get_user_setting($user_id, 'canadmin');
	} else {
		return false;
	}
}

/**
 * check if given username is an admin for the given gedcom
 */
function userGedcomAdmin($user_id = KT_USER_ID, $ged_id = KT_GED_ID) {
	if ($user_id) {
		return KT_Tree::get($ged_id)->userPreference($user_id, 'canedit') == 'admin' || userIsAdmin($user_id);
	} else {
		return false;
	}
}

/**
 * check if the given user has access privileges on this gedcom
 */
function userCanAccess($user_id = KT_USER_ID, $ged_id = KT_GED_ID) {
	if ($user_id) {
		if (userIsAdmin($user_id)) {
			return true;
		} else {
			$tmp = KT_Tree::get($ged_id)->userPreference($user_id, 'canedit');
			return $tmp == 'admin' || $tmp == 'accept' || $tmp == 'edit' || $tmp == 'access';
		}
	} else {
		return false;
	}
}

/**
 * check if the given user has write privileges for the given gedcom
 */
function userCanEdit($user_id = KT_USER_ID, $ged_id = KT_GED_ID) {

	if ($user_id) {
		if (userIsAdmin($user_id)) {
			return true;
		} else {
			$tmp = KT_Tree::get($ged_id)->userPreference($user_id, 'canedit');
			return $tmp == 'admin' || $tmp == 'accept' || $tmp == 'edit';
		}
	} else {
		return false;
	}
}

// Get the full name for a user
function getUserFullName($user_id) {
	return KT_DB::prepare("SELECT real_name FROM `##user` WHERE user_id=?")->execute(array($user_id))->fetchOne();
}

// Set the full name for a user
function setUserFullName($user_id, $real_name) {
	return KT_DB::prepare("UPDATE `##user` SET real_name=? WHERE user_id=?")->execute(array($real_name, $user_id));
}

// Get the email for a user
function getUserEmail($user_id) {
	return KT_DB::prepare("SELECT email FROM `##user` WHERE user_id=?")->execute(array($user_id))->fetchOne();
}

// Set the email for a user
function setUserEmail($user_id, $email) {
	return KT_DB::prepare("UPDATE `##user` SET email=? WHERE user_id=?")->execute(array($email, $user_id));
}

// set user_name for a user
function SetUserName($user_id, $username) {
	return KT_DB::prepare("UPDATE `##user` SET user_name = ? WHERE user_id = ?")->execute(array($username, $user_id));
}

// add a message into the log-file
function AddToLog($log_message, $log_type = 'error') {
	global $KT_REQUEST;
	KT_DB::prepare(
		"INSERT INTO `##log` (log_type, log_message, ip_address, user_id, gedcom_id) VALUES (?, ?, ?, ?, ?)"
	)->execute(array(
		$log_type,
		$log_message,
		$KT_REQUEST->getClientIp(),
		defined('KT_USER_ID') && KT_USER_ID ? KT_USER_ID : null,
		defined('KT_GED_ID') ? KT_GED_ID : null
	));
}

//----------------------------------- AddToSearchLog
//-- requires a string to add into the searchlog-file
function AddToSearchLog($log_message, $geds) {
	global $KT_REQUEST;
	foreach (KT_Tree::getAll() as $tree) {
		KT_DB::prepare(
			"INSERT INTO `##log` (log_type, log_message, ip_address, user_id, gedcom_id) VALUES ('search', ?, ?, ?, ?)"
		)->execute(array(
			(count(KT_Tree::getAll()) == count($geds) ? 'Global search: ' : 'Gedcom search: ').$log_message,
			$KT_REQUEST->getClientIp(),
			KT_USER_ID ? KT_USER_ID : null,
			$tree->tree_id
		));
	}
}

/**
 * Adds a news item to the database
 *
 * This function adds a news item represented by the $news array to the database.
 * If the $news array has an ['id'] field then the function assumes that it is
 * as update of an older news item.
 *
 * @author John Finlay
 * @param array $news a news item array
 */
function addNews($news) {
	if (array_key_exists('id', $news)) {
		KT_DB::prepare("UPDATE `##news` SET subject=?, body=?, updated=FROM_UNIXTIME(?) WHERE news_id=?")
		->execute(array($news['title'], $news['text'], $news['date'], $news['id']));
	} else {
		KT_DB::prepare("INSERT INTO `##news` (user_id, gedcom_id, subject, body) VALUES (NULLIF(?, ''), NULLIF(?, '') ,? ,?)")
		->execute(array($news['user_id'], $news['gedcom_id'],  $news['title'], $news['text']));
	}
}

// Gets the news items for the given user or gedcom
function getUserNews($user_id) {
	$rows=
		KT_DB::prepare("SELECT news_id, user_id, gedcom_id, UNIX_TIMESTAMP(updated) AS updated, subject, body FROM `##news` WHERE user_id=? ORDER BY updated DESC")
		->execute(array($user_id))
		->fetchAll();

	$news = array();
	foreach ($rows as $row) {
		$news[$row->news_id] = array(
			'id'		=> $row->news_id,
			'user_id'	=> $row->user_id,
			'gedcom_id'	=> $row->gedcom_id,
			'date'		=> $row->updated,
			'title'		=> $row->subject,
			'text'		=> $row->body,
		);
	}
	return $news;
}

function getGedcomNews($gedcom_id) {
	$rows=
		KT_DB::prepare("SELECT news_id, user_id, gedcom_id, UNIX_TIMESTAMP(updated) AS updated, subject, body FROM `##news` WHERE gedcom_id=? ORDER BY updated DESC")
		->execute(array($gedcom_id))
		->fetchAll();

	$news = array();
	foreach ($rows as $row) {
		$news[$row->news_id]=array(
			'id'		=> $row->news_id,
			'user_id'	=> $row->user_id,
			'gedcom_id'	=> $row->gedcom_id,
			'date'		=> $row->updated,
			'title'		=> $row->subject,
			'text'		=> $row->body,
		);
	}
	return $news;
}

/**
 * Gets the news item for the given news id
 *
 * @param int $news_id the id of the news entry to get
 */
function getNewsItem($news_id) {
	$row=
		KT_DB::prepare("SELECT news_id, user_id, gedcom_id, UNIX_TIMESTAMP(updated) AS updated, subject, body FROM `##news` WHERE news_id=?")
		->execute(array($news_id))
		->fetchOneRow();

	if ($row) {
		return array(
			'id'		=> $row->news_id,
			'user_id'	=> $row->user_id,
			'gedcom_id'	=> $row->gedcom_id,
			'date'		=> $row->updated,
			'title'		=> $row->subject,
			'text'		=> $row->body,
		);
	} else {
		return null;
	}
}
