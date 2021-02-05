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

if (!defined('KT_KIWITREES') || !defined('KT_SCRIPT_NAME') || KT_SCRIPT_NAME!='help_text.php') {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

switch ($help) {
case 'todo':
	$title=KT_I18N::translate('Research tasks');
	$text=KT_I18N::translate('Research tasks are special events, added to individuals in your family tree, which identify the need for further research.  You can use them as a reminder to check facts against more reliable sources, to obtain documents or photographs, to resolve conflicting information, etc.').
		'</p><p class="ui-state-highlight">'.
		KT_I18N::translate('To create new research tasks, you must first add “research task” to the list of facts and events in the family tree’s preferences.').
		'</p><p class="ui-state-highlight">'.
		KT_I18N::translate('Research tasks are stored using the custom GEDCOM tag “_TODO”.  Other genealogy applications may not recognise this tag.').
		'</p>';
	break;
}
