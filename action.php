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
 * along with Kiwitrees.  If not, see <http://www.gnu.org/licenses/>.
 */

define('WT_SCRIPT_NAME', 'action.php');
require './includes/session.php';

header('Content-type: text/html; charset=UTF-8');

switch (safe_POST('action')) {
case 'accept-changes':
	// Accept all the pending changes for a record
	require WT_ROOT.'includes/functions/functions_edit.php';
	$record=WT_GedcomRecord::getInstance(safe_POST_xref('xref'));
	if ($record && WT_USER_CAN_ACCEPT && $record->canDisplayDetails() && $record->canEdit()) {
		WT_FlashMessages::addMessage(/* I18N: %s is the name of an individual, source or other record */ WT_I18N::translate('The changes to “%s” have been accepted.', $record->getFullName()));
		accept_all_changes($record->getXref(), $record->getGedId());
	} else {
		header('HTTP/1.0 406 Not Acceptable');
	}
	break;

case 'copy-fact':
	// Copy a fact to the clipboard
	// The calling page may want to reload, to refresh its "paste" buffer
	require WT_ROOT.'includes/functions/functions_edit.php';
	$fact=new WT_Event(rawurldecode(safe_POST('factgedcom', WT_REGEX_UNSAFE)), null, 0);
	// Where can we paste this?
	if (preg_match('/^(NOTE|SOUR|OBJE)$/', $fact->getTag())) {
		// Some facts can be pasted to any record
		$type='all';
	} else {
		// Other facts can only be pasted records of the same type
		$type=safe_POST('type', array('INDI','FAM','SOUR','REPO','OBJE','NOTE'));
	}
	if (!is_array($WT_SESSION->clipboard)) {
		$WT_SESSION->clipboard=array();
	}
	$WT_SESSION->clipboard[]=array(
		'type'   =>$type,
		'factrec'=>$fact->getGedcomRecord(),
		'fact'   =>$fact->getTag()
		);
	// The clipboard only holds 10 facts
	while (count($WT_SESSION->clipboard)>10) {
		array_shift($WT_SESSION->clipboard);
	}
	WT_FlashMessages::addMessage(WT_I18N::translate('Record copied to clipboard'));
	break;

case 'delete-family':
case 'delete-individual':
case 'delete-media':
case 'delete-note':
case 'delete-repository':
case 'delete-source':
	require WT_ROOT.'includes/functions/functions_edit.php';
	$record=WT_GedcomRecord::getInstance(safe_POST_xref('xref'));
	if ($record && WT_USER_CAN_EDIT && $record->canDisplayDetails() && $record->canEdit()) {
		// Delete links to this record
		foreach (fetch_all_links($record->getXref(), $record->getGedId()) as $xref) {
			$linker = WT_GedcomRecord::getInstance($xref);
			$gedrec = find_gedcom_record($xref, $record->getGedId(), true);
			$gedrec = remove_links($gedrec, $record->getXref());
			// If we have removed a link from a family to an individual, and it has only one member
			if (preg_match('/^0 @'.WT_REGEX_XREF.'@ FAM/', $gedrec) && preg_match_all('/\n1 (HUSB|WIFE|CHIL) @(' . WT_REGEX_XREF . ')@/', $gedrec, $match)<2) {
				// Delete the family
				$family = WT_GedcomRecord::getInstance($xref);
				WT_FlashMessages::addMessage(/* I18N: %s is the name of a family group, e.g. “Husband name + Wife name” */ WT_I18N::translate('The family “%s” has been deleted, as it only has one member.', $family->getFullName()));
				delete_gedrec($family->getXref(), $family->getGedId());
				// Delete any remaining link to this family
				if ($match) {
					$relict = WT_GedcomRecord::getInstance($match[2][0]);
					$gedrec = find_gedcom_record($relict->getXref(), $relict->getGedId(), true);
					$gedrec = remove_links($gedrec, $linker->getXref());
					replace_gedrec($relict->getXref(), $relict->getGedId(), $gedrec, false);
					WT_FlashMessages::addMessage(/* I18N: %s are names of records, such as sources, repositories or individuals */ WT_I18N::translate('The link from “%1$s” to “%2$s” has been deleted.', $relict->getFullName(), $family->getFullName()));
				}
			} else {
				// Remove links from $linker to $record
				WT_FlashMessages::addMessage(/* I18N: %s are names of records, such as sources, repositories or individuals */ WT_I18N::translate('The link from “%1$s” to “%2$s” has been deleted.', $linker->getFullName(), $record->getFullName()));
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
	$user_id = WT_Filter::post('user_id');

	if (WT_USER_IS_ADMIN && WT_USER_ID != $user_id && WT_Filter::checkCsrf()) {
		AddToLog('deleted user ->' . get_user_name($user_id) . '<-', 'auth');
		delete_user($user_id);
	}
	break;

case 'reject-changes':
	// Reject all the pending changes for a record
	require WT_ROOT.'includes/functions/functions_edit.php';
	$record = WT_GedcomRecord::getInstance(safe_POST_xref('xref'));
	if ($record && WT_USER_CAN_ACCEPT && $record->canDisplayDetails() && $record->canEdit()) {
		WT_FlashMessages::addMessage(/* I18N: %s is the name of an individual, source or other record */ WT_I18N::translate('The changes to “%s” have been rejected.', $record->getFullName()));
		reject_all_changes($record->getXref(), $record->getGedId());
	} else {
		header('HTTP/1.0 406 Not Acceptable');
	}
	break;

case 'theme':
	// Change the current theme
	$theme_dir = safe_POST('theme');
	if (in_array($theme_dir, get_theme_names())) {
		$WT_SESSION->theme_dir=$theme_dir;
	} else {
		// Request for a non-existant theme.
		header('HTTP/1.0 406 Not Acceptable');
	}
	break;

case 'lookup_name':
	// look up record name from id for media linking
	$iid	= WT_Filter::post('iid');
	if ($iid instanceof WT_Person) {
		$iname	= strip_tags(WT_Person::getInstance($iid)->getFullName());
	} elseif ($iid instanceof WT_Family) {
		$iname	= strip_tags(WT_Family::getInstance($iid)->getFullName());
	} else {
		$iname	= strip_tags(WT_GedcomRecord::getInstance($iid)->getFullName());
	}

	header('Content-Type: application/json');
	echo json_encode($iname);
	break;

case 'deleteNews':
	// delete news item from widget_journal
	$news_id	= WT_Filter::post('newsId');
	WT_DB::prepare("DELETE FROM `##news` WHERE news_id=?")->execute(array($news_id));
	break;

}
Zend_Session::writeClose();
