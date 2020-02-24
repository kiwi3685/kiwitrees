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

if (!defined('KT_KIWITREES') || !defined('KT_SCRIPT_NAME') || KT_SCRIPT_NAME!='help_text.php') {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

switch ($help) {
case 'add_faq_item':
	$title=KT_I18N::translate('Frequently asked questions');
	$text=
		KT_I18N::translate('FAQs are lists of questions and answers, which allow you to explain the site\'s rules, policies, and procedures to your visitors.  Questions are typically concerned with privacy, copyright, user-accounts, unsuitable content, requirement for source-citations, etc.').
		'<br/><br/>'.
		KT_I18N::translate('You may use HTML to format the answer and to add links to other websites.');
	break;

case 'add_faq_order':
	$title=KT_I18N::translate('FAQ position');
	$text=KT_I18N::translate('This field controls the order in which the FAQ items are displayed.<br /><br />You do not have to enter the numbers sequentially.  If you leave holes in the numbering scheme, you can insert other items later.  For example, if you use the numbers 1, 6, 11, 16, you can later insert items with the missing sequence numbers.  Negative numbers and zero are allowed, and can be used to insert items in front of the first one.<br /><br />When more than one FAQ item has the same position number, only one of these items will be visible.');
	break;

case 'add_faq_visibility':
	$title=KT_I18N::translate('FAQ visibility');
	$text=KT_I18N::translate('A FAQ item can be displayed on just one of the family trees, or on all the family trees.');
	break;

case 'movedown_faq_item':
	$title=KT_I18N::translate('Move FAQ item down');
	$text=KT_I18N::translate('This option will let you move an item downwards on the FAQ page.<br /><br />Each time you use this option, the FAQ Position number of this item is increased by one.  You can achieve the same effect by editing the item in question and changing the FAQ Position field.  When more than one FAQ item has the same position number, only one of these items will be visible.');
	break;

case 'moveup_faq_item':
	$title=KT_I18N::translate('Move FAQ item up');
	$text=KT_I18N::translate('This option will let you move an item upwards on the FAQ page.<br /><br />Each time you use this option, the FAQ Position number of this item is reduced by one.  You can achieve the same effect by editing the item in question and changing the FAQ Position field.  When more than one FAQ item has the same position number, only one of these items will be visible.');
	break;

}
