<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2020 kiwitrees.net
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

/**
 * Perform an action, as an AJAX request.
 * It is bad design to put actions in GET parameters (because
 * reloading the page will execute the action again) or POST
 * parameters (because it effectively disables the "back" button).
 *
 * It also means we must hide such links from search engines,
 * which frequently penalize sites that generate different
 * content for browsers/robots.
 *
 * Instead, use an AJAX request, such as
 *
 * <a href="#" onclick="jQuery.post('action.php',{action='foo',p1='bar'}, function(){location.reload()});">click-me!</a>
 * <a href="#" onclick="jQuery.post('action.php',{action='foo',p1='bar'}).success(location.reload()).error(alert('failed'));">click-me!</a>
 *
 * Most actions will not need separate success() and error().
 * Typically this may occur if an action has already been submitted, or
 * the login session has expired.  In these cases, reloading the page is
 * the correct response for both success/error.
*/

define('KT_SCRIPT_NAME', 'action.php');
require './includes/session.php';

header('Content-type: text/html; charset=UTF-8');

switch (safe_POST('action')) {
	case 'accept-changes':
		// Accept all the pending changes for a record
		require KT_ROOT . 'includes/functions/functions_edit.php';
		$record = KT_GedcomRecord::getInstance(safe_POST('xref', KT_REGEX_XREF));
		if ($record && KT_USER_CAN_ACCEPT && $record->canDisplayDetails() && $record->canEdit()) {
			KT_FlashMessages::addMessage(/* I18N: %s is the name of an individual, source or other record */ KT_I18N::translate('The changes to “%s” have been accepted.', $record->getFullName()));
			accept_all_changes($record->getXref(), $record->getGedId());
		} else {
			header('HTTP/1.0 406 Not Acceptable');
		}
		break;

	case 'copy-fact':
		// Copy a fact to the clipboard
		// The calling page may want to reload, to refresh its "paste" buffer
		require KT_ROOT . 'includes/functions/functions_edit.php';
		$fact = new KT_Event(rawurldecode(safe_POST('factgedcom', KT_REGEX_UNSAFE)), null, 0);
		// Where can we paste this?
		if (preg_match('/^(NOTE|SOUR|OBJE)$/', $fact->getTag())) {
			// Some facts can be pasted to any record
			$type = 'all';
		} else {
			// Other facts can only be pasted records of the same type
			$type = safe_POST('type', array('INDI','FAM','SOUR','REPO','OBJE','NOTE'));
		}
		if (!is_array($KT_SESSION->clipboard)) {
			$KT_SESSION->clipboard=array();
		}
		$KT_SESSION->clipboard[]=array(
			'type'   =>$type,
			'factrec'=>$fact->getGedcomRecord(),
			'fact'   =>$fact->getTag()
			);
		// The clipboard only holds 10 facts
		while (count($KT_SESSION->clipboard)>10) {
			array_shift($KT_SESSION->clipboard);
		}
		KT_FlashMessages::addMessage(KT_I18N::translate('Record copied to clipboard'));
		break;

	case 'delete-family':
	case 'delete-individual':
	case 'delete-media':
	case 'delete-note':
	case 'delete-repository':
	case 'delete-source':
		require KT_ROOT . 'includes/functions/functions_edit.php';
		$record = KT_GedcomRecord::getInstance(safe_POST('xref', KT_REGEX_XREF));
		if ($record && KT_USER_CAN_EDIT && $record->canDisplayDetails() && $record->canEdit()) {
			// Delete links to this record
			foreach (fetch_all_links($record->getXref(), $record->getGedId()) as $xref) {
				$linker = KT_GedcomRecord::getInstance($xref);
				$gedrec = find_gedcom_record($xref, $record->getGedId(), true);
				$gedrec = remove_links($gedrec, $record->getXref());
				// If we have removed a link from a family to an individual, and it has only one member
				if (preg_match('/^0 @' . KT_REGEX_XREF . '@ FAM/', $gedrec) && preg_match_all('/\n1 (HUSB|WIFE|CHIL) @(' . KT_REGEX_XREF . ')@/', $gedrec, $match)<2) {
					// Delete the family
					$family = KT_GedcomRecord::getInstance($xref);
					KT_FlashMessages::addMessage(/* I18N: %s is the name of a family group, e.g. “Husband name + Wife name” */ KT_I18N::translate('The family “%s” has been deleted, as it only has one member.', $family->getFullName()));
					delete_gedrec($family->getXref(), $family->getGedId());
					// Delete any remaining link to this family
					if ($match) {
						$relict = KT_GedcomRecord::getInstance($match[2][0]);
						$gedrec = find_gedcom_record($relict->getXref(), $relict->getGedId(), true);
						$gedrec = remove_links($gedrec, $linker->getXref());
						replace_gedrec($relict->getXref(), $relict->getGedId(), $gedrec, false);
						KT_FlashMessages::addMessage(/* I18N: %s are names of records, such as sources, repositories or individuals */ KT_I18N::translate('The link from “%1$s” to “%2$s” has been deleted.', $relict->getFullName(), $family->getFullName()));
					}
				} else {
					// Remove links from $linker to $record
					KT_FlashMessages::addMessage(/* I18N: %s are names of records, such as sources, repositories or individuals */ KT_I18N::translate('The link from “%1$s” to “%2$s” has been deleted.', $linker->getFullName(), $record->getFullName()));
					replace_gedrec($linker->getXref(), $linker->getGedId(), $gedrec, false);
				}
			}
			// Delete the record itself
			delete_gedrec($record->getXref(), $record->getGedId());
		} else {
			header('HTTP/1.0 406 Not Acceptable');
		}
		break;

	case 'delete-user':
		$user_id = safe_POST('user_id');

		if (KT_USER_IS_ADMIN && KT_USER_ID != $user_id && KT_Filter::checkCsrf()) {
			AddToLog('deleted user ->' . get_user_name($user_id) . '<-', 'auth');
			delete_user($user_id);
		}
		break;

	case 'reject-changes':
		// Reject all the pending changes for a record
		require KT_ROOT . 'includes/functions/functions_edit.php';
		$record = KT_GedcomRecord::getInstance(safe_POST('xref', KT_REGEX_XREF));
		if ($record && KT_USER_CAN_ACCEPT && $record->canDisplayDetails() && $record->canEdit()) {
			KT_FlashMessages::addMessage(/* I18N: %s is the name of an individual, source or other record */ KT_I18N::translate('The changes to “%s” have been rejected.', $record->getFullName()));
			reject_all_changes($record->getXref(), $record->getGedId());
		} else {
			header('HTTP/1.0 406 Not Acceptable');
		}
		break;

	case 'theme':
		// Change the current theme
		$theme_dir = safe_POST('theme');
		if (in_array($theme_dir, get_theme_names())) {
			set_gedcom_setting(KT_GED_ID, 'THEME_DIR', $theme_dir);
			$KT_SESSION->theme_dir = $theme_dir;
		} else {
			// Request for a non-existant theme.
			header('HTTP/1.0 406 Not Acceptable');
		}
		break;

	case 'lookup_name':
		// look up record name from id for media linking
		$iid	= safe_POST('iid');
		if ($iid instanceof KT_Person) {
			$iname	= strip_tags(KT_Person::getInstance($iid)->getFullName());
		} elseif ($iid instanceof KT_Family) {
			$iname	= strip_tags(KT_Family::getInstance($iid)->getFullName());
		} else {
			$iname	= strip_tags(KT_GedcomRecord::getInstance($iid)->getFullName());
		}

		header('Content-Type: application/json');
		echo json_encode($iname);
		break;

	case 'delete-dna':
		$dna_id = KT_Filter::post('dna_id');
		$sql = "DELETE FROM `##dna` WHERE dna_id IN ('$dna_id')";
		KT_DB::prepare($sql)->execute();
		KT_FlashMessages::addMessage(KT_I18N::translate('DNA data deleted'));
		break;

    case 'deleteBookmark':
        $bookMark       = KT_Filter::post('mark');
        $user           = KT_Filter::post('user');
        $setting_value  = get_user_setting($user, 'bookmarks');

        $update   = str_replace($bookMark, '', $setting_value);
        $update   = str_replace('||', '|', $update);
        $update   = preg_replace('/^\|/',  '', $update);
        $update   = preg_replace('/\|$/',  '', $update);

        set_user_setting($user, 'bookmarks', $update);
        break;

}
Zend_Session::writeClose();
