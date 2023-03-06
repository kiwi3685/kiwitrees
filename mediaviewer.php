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

define('KT_SCRIPT_NAME', 'mediaviewer.php');
require './includes/session.php';
require_once KT_ROOT.'includes/functions/functions_print_lists.php';

$controller = new KT_Controller_Media();

if ($controller->record && $controller->record->canDisplayDetails()) {
	$controller->pageHeader();
	if ($controller->record->isMarkedDeleted()) {
		if (KT_USER_CAN_ACCEPT) {
			echo
				'<p class="ui-state-highlight">',
				/* I18N: %1$s is “accept”, %2$s is “reject”.  These are links. */ KT_I18N::translate(
					'This media object has been deleted.  You should review the deletion and then %1$s or %2$s it.',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'accept-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the deletion and then accept or reject it.', 'accept') . '</a>',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'reject-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the deletion and then accept or reject it.', 'reject') . '</a>'
				),
				' ', help_link('pending_changes'),
				'</p>';
		} elseif (KT_USER_CAN_EDIT) {
			echo
				'<p class="ui-state-highlight">',
				KT_I18N::translate('This media object has been deleted.  The deletion will need to be reviewed by a moderator.'),
				' ', help_link('pending_changes'),
				'</p>';
		}
	} elseif (find_updated_record($controller->record->getXref(), KT_GED_ID)!==null) {
		if (KT_USER_CAN_ACCEPT) {
			echo
				'<p class="ui-state-highlight">',
				/* I18N: %1$s is “accept”, %2$s is “reject”.  These are links. */ KT_I18N::translate(
					'This media object has been edited.  You should review the changes and then %1$s or %2$s them.',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'accept-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the changes and then accept or reject them.', 'accept') . '</a>',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'reject-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the changes and then accept or reject them.', 'reject') . '</a>'
				),
				' ', help_link('pending_changes'),
				'</p>';
		} elseif (KT_USER_CAN_EDIT) {
			echo
				'<p class="ui-state-highlight">',
				KT_I18N::translate('This media object has been edited.  The changes need to be reviewed by a moderator.'),
				' ', help_link('pending_changes'),
				'</p>';
		}
	}
} else {
	header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
	$controller->pageHeader();
	echo '<p class="ui-state-error">', KT_I18N::translate('This media object does not exist or you do not have permission to view it.'), '</p>';
	exit;
}

$controller
	->addInlineJavascript('function show_gedcom_record() {var recwin=window.open("gedrecord.php?pid=' . $controller->record->getXref() . '", "_blank", edit_window_specs);}')
	->addInlineJavascript('
		jQuery("#media-tabs")
			.tabs({
				create: function(e, ui){
					jQuery(e.target).css("visibility", "visible");
				}
			});
	');

$linked_indi   = $controller->record->fetchLinkedIndividuals();
$linked_fam    = $controller->record->fetchLinkedFamilies();
$linked_sour   = $controller->record->fetchLinkedSources();
$linked_repo   = $controller->record->fetchLinkedRepositories();
$linked_note   = $controller->record->fetchLinkedNotes();
$media_changes = KT_Module::getModuleByName('tab_changes');


echo '<div id="media-details-page">';
echo '<h2>', $controller->record->getFullName(), ' ', $controller->record->getAddName(), '</h2>';
echo '<div id="media-tabs">';
	echo '<div id="media-edit">';
		echo '<table class="facts_table">
			<tr>
				<td align="center" width="150">';
					// When we have a pending edit, $controller->record shows the *old* data.
					// As a temporary kludge, fetch a "normal" version of the record - which includes pending changes
					// TODO - check both, and use RED/BLUE boxes.
					$tmp = KT_Media::getInstance($controller->record->getXref());
					echo $tmp->displayImage();
					if (!$tmp->isExternal()) {
						if ($tmp->fileExists('main')) {
							if ($SHOW_MEDIA_DOWNLOAD >= KT_USER_ACCESS_LEVEL) {
								echo '<p><a href="' . $tmp->getHtmlUrlDirect('main', true).'">' . KT_I18N::translate('Download File') . '</a></p>';
							}
						} else {
							echo '<p class="ui-state-error">' . KT_I18N::translate('The file “%s” does not exist.', $tmp->getFilename()) . '</p>';
						}
					}
				echo '</td>
				<td valign="top">
					<table width="100%">
						<tr>
							<td>
								<table class="facts_table">';
										$facts = $controller->getFacts(KT_USER_CAN_EDIT || KT_USER_CAN_ACCEPT);
										foreach ($facts as $f=>$fact) {
											print_fact($fact, $controller->record);
										}
								echo '</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</div>'; // close "media-edit"
	echo '<ul>';
		if ($linked_indi) {
			echo '<li><a href="#indi-media"><span id="indimedia">', KT_I18N::translate('Individuals'), '</span></a></li>';
		}
		if ($linked_fam) {
			echo '<li><a href="#fam-media"><span id="fammedia">', KT_I18N::translate('Families'), '</span></a></li>';
		}
		if ($linked_sour) {
			echo '<li><a href="#sources-media"><span id="sourcemedia">', KT_I18N::translate('Sources'), '</span></a></li>';
		}
		if ($linked_repo) {
			echo '<li><a href="#repo-media"><span id="repomedia">', KT_I18N::translate('Repositories'), '</span></a></li>';
		}
		if ($linked_note) {
			echo '<li><a href="#notes-media"><span id="notemedia">', KT_I18N::translate('Notes'), '</span></a></li>';
		}
        if ($media_changes) {
            echo '<li><a href="#changes-media"><span id="changes">', KT_I18N::translate('Changes'), '</span></a></li>';
        }
echo '</ul>';

	// Individuals linked to this media object
	if ($linked_indi) {
		echo '<div id="indi-media">';
		echo format_indi_table($controller->record->fetchLinkedIndividuals(), $controller->record->getFullName());
		echo '</div>'; //close "indi-media"
	}
	// Families linked to this media object
	if ($linked_fam) {
		echo '<div id="fam-media">';
		echo format_fam_table($controller->record->fetchLinkedFamilies(), $controller->record->getFullName());
		echo '</div>'; //close "fam-media"
	}
	// Sources linked to this media object
	if ($linked_sour) {
		echo '<div id="sources-media">';
		echo format_sour_table($controller->record->fetchLinkedSources(), $controller->record->getFullName());
		echo '</div>'; //close "source-media"
	}
	// Repositories linked to this media object
	if ($linked_repo) {
		echo '<div id="repo-media">';
		echo format_repo_table($controller->record->fetchLinkedRepositories(), $controller->record->getFullName());
		echo '</div>'; //close "repo-media"
	}
	// medias linked to this media object
	if ($linked_note) {
		echo '<div id="notes-media">';
		echo format_note_table($controller->record->fetchLinkedNotes(), $controller->record->getFullName());
		echo '</div>'; //close "notes-media"
	}
    // Media changes
	if ($media_changes) {
		echo '<div id="changes-media">';
        echo KT_Module::getModuleByName('tab_changes')->getTabContent();
		echo '</div>'; //close "changes-media"
	}

echo '</div>'; //close div "media-tabs"
echo '</div>'; //close div "media-details"
