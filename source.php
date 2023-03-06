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

define('KT_SCRIPT_NAME', 'source.php');
require './includes/session.php';
require_once KT_ROOT.'includes/functions/functions_print_lists.php';

$controller = new KT_Controller_Source();

if ($controller->record && $controller->record->canDisplayDetails()) {
	$controller->pageHeader();
	if ($controller->record->isMarkedDeleted()) {
		if (KT_USER_CAN_ACCEPT) {
			echo
				'<p class="ui-state-highlight">',
				/* I18N: %1$s is “accept”, %2$s is “reject”.  These are links. */ KT_I18N::translate(
					'This source has been deleted.  You should review the deletion and then %1$s or %2$s it.',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'accept-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the deletion and then accept or reject it.', 'accept') . '</a>',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'reject-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the deletion and then accept or reject it.', 'reject') . '</a>'
				),
				' ', help_link('pending_changes'),
				'</p>';
		} elseif (KT_USER_CAN_EDIT) {
			echo
				'<p class="ui-state-highlight">',
				KT_I18N::translate('This source has been deleted.  The deletion will need to be reviewed by a moderator.'),
				' ', help_link('pending_changes'),
				'</p>';
		}
	} elseif (find_updated_record($controller->record->getXref(), KT_GED_ID)!==null) {
		if (KT_USER_CAN_ACCEPT) {
			echo
				'<p class="ui-state-highlight">',
				/* I18N: %1$s is “accept”, %2$s is “reject”.  These are links. */ KT_I18N::translate(
					'This source has been edited.  You should review the changes and then %1$s or %2$s them.',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'accept-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the changes and then accept or reject them.', 'accept') . '</a>',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'reject-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the changes and then accept or reject them.', 'reject') . '</a>'
				),
				' ', help_link('pending_changes'),
				'</p>';
		} elseif (KT_USER_CAN_EDIT) {
			echo
				'<p class="ui-state-highlight">',
				KT_I18N::translate('This source has been edited.  The changes need to be reviewed by a moderator.'),
				' ', help_link('pending_changes'),
				'</p>';
		}
	}
} else {
	header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
	$controller->pageHeader();
	echo '<p class="ui-state-error">', KT_I18N::translate('This source does not exist or you do not have permission to view it.'), '</p>';
	exit;
}

$linkToID = $controller->record->getXref(); // Tell addmedia.php what to link to

$controller
	->addInlineJavascript('function show_gedcom_record() {var recwin=window.open("gedrecord.php?pid=' . $controller->record->getXref() . '", "_blank", edit_window_specs);}')
	->addInlineJavascript('
		jQuery("#source-tabs")
			.tabs({
				create: function(e, ui){
					jQuery(e.target).css("visibility", "visible");  // prevent FOUC
				}
			});
	');
$linked_indi  = $controller->record->fetchLinkedIndividuals();
$linked_fam   = $controller->record->fetchLinkedFamilies();
$linked_obje  = $controller->record->fetchLinkedMedia();
$linked_note  = $controller->record->fetchLinkedNotes();
$sour_changes = KT_Module::getModuleByName('tab_changes');

echo '<div id="source-details-page">';
echo '<h2>', $controller->record->getFullName(), '</h2>';
echo '<div id="source-tabs">
	<ul>
		<li><a href="#source-edit"><span>', KT_I18N::translate('Details'), '</span></a></li>';
		if ($linked_indi) {
			echo '<li><a href="#indi-sources"><span id="indisource">', KT_I18N::translate('Individuals'), '</span></a></li>';
		}
		if ($linked_fam) {
			echo '<li><a href="#fam-sources"><span id="famsource">', KT_I18N::translate('Families'), '</span></a></li>';
		}
		if ($linked_obje) {
			echo '<li><a href="#media-sources"><span id="mediasource">', KT_I18N::translate('Media objects'), '</span></a></li>';
		}
		if ($linked_note) {
			echo '<li><a href="#note-sources"><span id="notesource">', KT_I18N::translate('Notes'), '</span></a></li>';
		}
        if ($sour_changes) {
            echo '<li><a href="#changes-sour"><span id="changes">', KT_I18N::translate('Changes'), '</span></a></li>';
        }
	echo '</ul>';
	// Edit this source
	echo '<div id="source-edit">';
		echo '<table class="facts_table">';

		$sourcefacts=$controller->record->getFacts();
		foreach ($sourcefacts as $fact) {
			print_fact($fact, $controller->record);
		}

		// Print media
		print_main_media($controller->record->getXref());

		// new fact link
		if ($controller->record->canEdit()) {
			print_add_new_fact($controller->record->getXref(), $sourcefacts, 'SOUR');
			// new media
			if (get_gedcom_setting(KT_GED_ID, 'MEDIA_UPLOAD') >= KT_USER_ACCESS_LEVEL) {
				echo '<tr><td class="descriptionbox">';
				echo KT_Gedcom_Tag::getLabel('OBJE');
				echo '</td><td class="optionbox">';
				echo '<a href="#" onclick="window.open(\'addmedia.php?action=showmediaform&amp;linktoid=', $controller->record->getXref(), '\', \'_blank\', edit_window_specs); return false;">', KT_I18N::translate('Add a media object'), '</a>';
				echo help_link('OBJE');
				echo '<br>';
				echo '<a href="inverselink.php?linktoid=' . $controller->record->getXref() . '&amp;linkto=source" target="_blank">' . KT_I18N::translate('Link to an existing media object') . '</a>';
				echo '</td></tr>';
			}
		}
		echo '</table>
	</div>'; // close "details"
	// Individuals linked to this source
	if ($linked_indi) {
		echo '<div id="indi-sources">';
		echo format_indi_table($linked_indi, $controller->record->getFullName());
		echo '</div>'; //close "indi-sources"
	}
	// Families linked to this source
	if ($linked_fam) {
		echo '<div id="fam-sources">';
		echo format_fam_table($linked_fam, $controller->record->getFullName());
		echo '</div>'; //close "fam-sources"
	}
	// Media Items linked to this source
	if ($linked_obje) {
		echo '<div id="media-sources">';
		echo format_media_table($linked_obje, $controller->record->getFullName());
		echo '</div>'; //close "media-sources"
	}
	// Shared Notes linked to this source
	if ($linked_note) {
		echo '<div id="note-sources">';
		echo format_note_table($linked_note, $controller->record->getFullName());
		echo '</div>'; //close "note-sources"
	}
    // Source changes
	if ($sour_changes) {
		echo '<div id="changes-sour">';
        echo KT_Module::getModuleByName('tab_changes')->getTabContent();
		echo '</div>'; //close "changes-sour"
	}
echo '</div>'; //close div "source-tabs"
echo '</div>'; //close div "source-details-page"
