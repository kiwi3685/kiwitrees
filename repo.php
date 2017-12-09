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
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

define('KT_SCRIPT_NAME', 'repo.php');
require './includes/session.php';
require_once KT_ROOT.'includes/functions/functions_print_lists.php';

$controller = new KT_Controller_Repository();

if ($controller->record && $controller->record->canDisplayDetails()) {
	$controller->pageHeader();
	if ($controller->record->isMarkedDeleted()) {
		if (KT_USER_CAN_ACCEPT) {
			echo
				'<p class="ui-state-highlight">',
				/* I18N: %1$s is “accept”, %2$s is “reject”.  These are links. */ KT_I18N::translate(
					'This repository has been deleted.  You should review the deletion and then %1$s or %2$s it.',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'accept-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the deletion and then accept or reject it.', 'accept') . '</a>',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'reject-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the deletion and then accept or reject it.', 'reject') . '</a>'
				),
				' ', help_link('pending_changes'),
				'</p>';
		} elseif (KT_USER_CAN_EDIT) {
			echo
				'<p class="ui-state-highlight">',
				KT_I18N::translate('This repository has been deleted.  The deletion will need to be reviewed by a moderator.'),
				' ', help_link('pending_changes'),
				'</p>';
		}
	} elseif (find_updated_record($controller->record->getXref(), KT_GED_ID)!==null) {
		if (KT_USER_CAN_ACCEPT) {
			echo
				'<p class="ui-state-highlight">',
				/* I18N: %1$s is “accept”, %2$s is “reject”.  These are links. */ KT_I18N::translate(
					'This repository has been edited.  You should review the changes and then %1$s or %2$s them.',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'accept-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the changes and then accept or reject them.', 'accept') . '</a>',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'reject-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the changes and then accept or reject them.', 'reject') . '</a>'
				),
				' ', help_link('pending_changes'),
				'</p>';
		} elseif (KT_USER_CAN_EDIT) {
			echo
				'<p class="ui-state-highlight">',
				KT_I18N::translate('This repository has been edited.  The changes need to be reviewed by a moderator.'),
				' ', help_link('pending_changes'),
				'</p>';
		}
	}
} else {
	header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
	$controller->pageHeader();
	echo '<p class="ui-state-error">', KT_I18N::translate('This repository does not exist or you do not have permission to view it.'), '</p>';
	exit;
}

$linkToID=$controller->record->getXref(); // Tell addmedia.php what to link to

$controller
	->addInlineJavascript('function show_gedcom_record() {var recwin=window.open("gedrecord.php?pid=' . $controller->record->getXref() . '", "_blank", edit_window_specs);}')
	->addInlineJavascript('
		jQuery("#repo-tabs")
			.tabs({
				create: function(e, ui){
					jQuery(e.target).css("visibility", "visible");  // prevent FOUC
				}
			});
	');
  
$linked_sour = $controller->record->fetchLinkedSources();

echo '<div id="repo-details-page">';
echo '<h2>', $controller->record->getFullName(), '</h2>';
echo '<div id="repo-tabs">
	<ul>
		<li><a href="#repo-edit"><span>', KT_I18N::translate('Details'), '</span></a></li>';
		if ($linked_sour) {
			echo '<li><a href="#source-repo"><span id="reposource">', KT_I18N::translate('Sources'), '</span></a></li>';
		}
		echo '</ul>';

	// Shared Note details ---------------------
	echo '<div id="repo-edit">';
		echo '<table class="facts_table">';
			$repositoryfacts=$controller->record->getFacts();
			foreach ($repositoryfacts as $fact) {
				print_fact($fact, $controller->record);
			}

			// new fact link
			if ($controller->record->canEdit()) {
				print_add_new_fact($controller->record->getXref(), $repositoryfacts, 'REPO');
			}
		echo '</table>
	</div>'; // close "repo-edit"

	// Sources linked to this repository
	if ($linked_sour) {
		echo '<div id="source-repo">';
		echo format_sour_table($linked_sour, $controller->record->getFullName());
		echo '</div>'; //close "source-repo"
	}

echo '</div>'; //close div "repo-tabs"
echo '</div>'; //close div "repo-details-page"
