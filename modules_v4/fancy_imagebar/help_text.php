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
case 'choose_images':
	$title=KT_I18N::translate('Showing images on the Fancy Imagebar');
	$text=KT_I18N::translate('Here you can choose which images should be shown in the Fancy Imagebar. Uncheck the images you do not want to show.<br><br>If there are less images choosen then needed to fill up the entire Fancy Imagebar, the images will be repeated.<br><br>Note: Only local ’jpg’ or ’png’ images which are set as type = ’photo’ are supported by this module. External images are not supported. It is not possible to keep transparency for png thumbnails in the Fancy Imagebar. Transparent png-thumbnails will get a black background in the Fancy Imagebar. The images shown in this table have the right specifications already.<br><br>The Fancy Imagebar module respects privacy settings!');
	break;
}
