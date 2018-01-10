<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2018 kiwitrees.net
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

define('KT_SCRIPT_NAME', 'family.php');
require './includes/session.php';

$controller = new KT_Controller_Family();

if ($controller->record && $controller->record->canDisplayDetails()) {
	$controller->pageHeader();
	
	if ($controller->record->isMarkedDeleted()) {
		if (KT_USER_CAN_ACCEPT) {
			echo
				'<p class="ui-state-highlight">',
				/* I18N: %1$s is “accept”, %2$s is “reject”.  These are links. */ KT_I18N::translate(
					'This family has been deleted.  You should review the deletion and then %1$s or %2$s it.',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'accept-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the deletion and then accept or reject it.', 'accept') . '</a>',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'reject-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the deletion and then accept or reject it.', 'reject') . '</a>'
				),
				' ', help_link('pending_changes'),
				'</p>';
		} elseif (KT_USER_CAN_EDIT) {
			echo
				'<p class="ui-state-highlight">',
				KT_I18N::translate('This family has been deleted.  The deletion will need to be reviewed by a moderator.'),
				' ', help_link('pending_changes'),
				'</p>';
		}
	} elseif (find_updated_record($controller->record->getXref(), KT_GED_ID)!==null) {
		if (KT_USER_CAN_ACCEPT) {
			echo
				'<p class="ui-state-highlight">',
				/* I18N: %1$s is “accept”, %2$s is “reject”.  These are links. */ KT_I18N::translate(
					'This family has been edited.  You should review the changes and then %1$s or %2$s them.',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'accept-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the changes and then accept or reject them.', 'accept') . '</a>',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'reject-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . KT_I18N::translate_c('You should review the changes and then accept or reject them.', 'reject') . '</a>'
				),
				' ', help_link('pending_changes'),
				'</p>';
		} elseif (KT_USER_CAN_EDIT) {
			echo
				'<p class="ui-state-highlight">',
				KT_I18N::translate('This family has been edited.  The changes need to be reviewed by a moderator.'),
				' ', help_link('pending_changes'),
				'</p>';
		}
	}
} elseif ($controller->record && $SHOW_PRIVATE_RELATIONSHIPS) {
	$controller->pageHeader();
	// Continue - to display the children/parents/grandparents.
	// We'll check for showing the details again later
} else {
	header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
	$controller->pageHeader();
	echo '<p class="ui-state-error">', KT_I18N::translate('This family does not exist or you do not have permission to view it.'), '</p>';
	exit;
}

$PEDIGREE_FULL_DETAILS = '1'; // Override GEDCOM configuration
$show_full = '1';

echo '
	<div id="family-page">
		<h2 class="name_head" class="center">', $controller->record->getFullName(), '</h2>
		<div id="family_chart">';
				print_parents($controller->record->getXref());
				if (KT_USER_CAN_EDIT) {
					if ($controller->diff_record) {
						$husb=$controller->diff_record->getHusband();
					} else {
						$husb=$controller->record->getHusband();
					}
					if ($controller->diff_record) {
						$wife=$controller->diff_record->getWife();
					} else {
						$wife=$controller->record->getWife();
					}
				}
			echo '<div id="children">',
				print_children($controller->record->getXref()), '
			</div>
		</div>
		<div id="fam_info">
			<div class="subheaders">', KT_I18N::translate('Family Group Information'), '</div>';
				if ($controller->record->canDisplayDetails()) {
					echo '<div>';
					$controller->printFamilyFacts();
					echo '</div>';
				} else {
					echo '<p class="ui-state-highlight">', KT_I18N::translate('The details of this family are private.'), '</p>';
				}
		echo '</div>';
