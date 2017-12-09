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

if (!defined('KT_KIWITREES') || !defined('KT_SCRIPT_NAME') || KT_SCRIPT_NAME!='help_text.php') {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

switch ($help) {
case 'add_by_id':
	$title=KT_I18N::translate('Add by ID');
	$text=KT_I18N::translate('This input box lets you enter an individual\'s ID number so he can be added to the Clippings Cart.  Once added you\'ll be offered options to link that individual\'s relations to your Clippings Cart.<br /><br />If you do not know an individual\'s ID number, you can perform a search by name by pressing the Person icon next to the Add button.');
	break;

case 'empty_cart':
	$title=KT_I18N::translate('Empty Cart');
	$text=KT_I18N::translate('When you click this link your Clippings Cart will be totally emptied.<br /><br />If you don\'t want to remove all persons, families, etc. from the Clippings Cart, you can remove items individually by clicking the <b>Remove</b> link in the Name boxes.  There is <u>no</u> confirmation dialog when you click either of these links;  the requested deletion takes place immediately.');
	break;
}
