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

if (!defined('KT_KIWITREES') || !defined('KT_SCRIPT_NAME') || KT_SCRIPT_NAME!='help_text.php') {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

switch ($help) {
case 'gallery_position':
	$title=KT_I18N::translate('Gallery position');
	$text=KT_I18N::translate('This field controls the order in which the galleries are displayed.').'<br><br>'.KT_I18N::translate('You do not have to enter the numbers sequentially. If you leave holes in the numbering scheme, you can insert other albums later. For example, if you use the numbers 1, 6, 11, 16, you can later insert albums with the missing sequence numbers. Negative numbers and zero are allowed, and can be used to insert albums in front of the first one.').'<br><br>'.KT_I18N::translate('When more than one gallery has the same position number, only one of these albums will be visible.');
	break;

case 'gallery_visibility':
	$title=KT_I18N::translate('Gallery visibility');
	$text=KT_I18N::translate('You can determine whether this album will be visible regardless of family tree, or whether it will be visible only to the current family tree.').
	'<br><ul><li><b>'.KT_I18N::translate('All').'</b>&nbsp;&nbsp;&nbsp;'.KT_I18N::translate('The album will appear in all galleries, regardless of family tree.').'</li><li><b>'.get_gedcom_setting(KT_GED_ID, 'title').'</b>&nbsp;&nbsp;&nbsp;'.KT_I18N::translate('The album will appear only in the currently active family trees\'s gallery.').'</li></ul>';
	break;

case 'gallery_language':
	$title=KT_I18N::translate('Gallery language');
	$text=KT_I18N::translate('Either leave all languages un-ticked to display the gallery texts in every language, or tick the specific languages you want to display it for.<br><br>To create translated texts for different languages create multiple copies setting the appropriate language only for each version.');
	break;

case 'gallery_title':
	$title=KT_I18N::translate('Summary page and Menu title');
	$text=KT_I18N::translate('This is a brief title. It is displayed in two places.<ol><li> It is used as the main menu item name if your theme uses names, and you have more than one gallery. If you only have one gallery, then the title of that gallery is used. It should be kept short or it might break the menu display.</li><li>It is used as the main title on the display page, above the tabbed list of galleries.</li></ol>');
	break;

case 'gallery_description':
	$title=KT_I18N::translate('Gallery description');
	$text=KT_I18N::translate('This is a sub-heading that will display below the Summary page title, above the tabbed list of galleries. It can contain HTML elements including an image if you wish. Simply ensure there is no content if you do not want to display it.');
	break;

case 'gallery_source':
	$title=KT_I18N::translate('Gallery source');
	$text=KT_I18N::translate('Here you can either select the kiwitrees media folder to display in this gallery page, or you can set a link to a Flickr or Picasa location for your group of images.'.
	'<br>'.
	'<em>[Such external sources must be public or they will not be viewable in kiwitrees.]</em>'.
	'<br><br>'.
	'For Flickr (www.flickr.com), enter the <strong>Set</strong> number of your images, usually a long number like <strong>72157633272831222</strong>. Nothing else is required in this field.'.
	'<br><br>'.
	'The module will add these references to the correct URLs to link to your Flickr site.');
	break;

case 'gallery_theme':
	$title=KT_I18N::translate('Gallery themes');
	$text=KT_I18N::translate('There are TWO types of theme in operation for this module.'.
	'<br><br>'.
	'The first is the standard kiwitrees theme (kiwitrees, clouds, colors, xenea etc). This controls the overall container for the gallery and the menu images in each of the kiwitrees themes. These are switched automatically when you change the main kiwitrees theme.'.
	'<br><br>'.
	'In addition, there is the Galleria theme that controls the actual image display area. This module includes three default themes for this, two supplied by Galleria (called "classic" and "azur"), and an alternative "simpl_galleria" one designed to compliment the colors of the blue-based themes such as clouds and xenea. There are also other themes available <u>for purchase</u> from the Galleria website. Switch between the "Azur", "Classic" and "Simpl_galleria" by selecting them on the configuration page.'.
	'<br><br>'.
	'To add other Galleria themes, download them from their website (www.galleria.io/), and copy them to the kiwitrees /modules_vX/gallery/galleria/themes/ folder.'.
	'<br>'.
	'You should then add a 250px x 140px thumbnail image to suit in the kiwitrees /modules_vX/gallery/images folder, named to match the theme and of type png (like classic.png).');
	break;
}
