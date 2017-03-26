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

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

$options = unserialize(get_module_setting('fancy_treeview_descendants', 'FTV_OPTIONS'));
if(!empty($options)) {
	foreach($options as $option) {
		$option['THUMB_SIZE'] = '60';
		$option['USE_SQUARE_THUMBS'] = '1';
		$option['SHOW_USERFORM'] = '2';
		$new_options[] = $option;
	}
	set_module_setting('fancy_treeview_descendants', 'FTV_OPTIONS',  serialize($new_options));
	unset($new_options);
}
// Update the version to indicate success
WT_Site::preference($schema_name, $next_version);
