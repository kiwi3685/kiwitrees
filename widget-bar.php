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

//get the widgets list
$widgets = WT_Module::getActiveWidgets();

echo '<div id="widget-bar">';
	foreach ($widgets as $module_name=>$module) {
		$class_name = $module_name.'_WT_Module';
		$module = new $class_name;
		$widget = WT_DB::prepare(
			"SELECT SQL_CACHE * FROM `##block` WHERE module_name = ?"
		)->execute(array($module_name))->fetchOneRow();
		if (!$widget) {
			WT_DB::prepare("INSERT INTO `##block` (module_name, block_order) VALUES (?, 0)")
				->execute(array($module_name));
			$widget = WT_DB::prepare("SELECT SQL_CACHE * FROM `##block` WHERE module_name = ?")
				->execute(array($module_name))->fetchOneRow();
		}

		echo '<div class="widget">' , $module->getWidget($widget->block_id) , '</div>';
	}
echo '</div>';
