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

define('WT_SCRIPT_NAME', 'admin_site_use.php');
require './includes/session.php';

$controller = new WT_Controller_Page();
$controller
	->restrictAccess(WT_USER_IS_ADMIN)
	->setPageTitle(WT_I18N::translate('Server usage'))
	->pageHeader();

function siteIndividuals() {
	$count = WT_DB::prepare("SELECT SQL_CACHE COUNT(*) FROM `##individuals`")
		->execute()
		->fetchOne();
	return	WT_I18N::number($count);
}

function siteMedia() {
	$count = WT_DB::prepare("SELECT SQL_CACHE COUNT(*) FROM `##media` WHERE (m_filename NOT LIKE 'http://%' AND m_filename NOT LIKE 'https://%')")
		->execute()
		->fetchOne();
	return	WT_I18N::number($count);
}

$db_size = WT_I18N::number(db_size());
$dir_size = WT_I18N::number(directory_size());
$total_size = WT_I18N::number(db_Size() + directory_size());

?>

<div id="size">
	<h3><?php echo WT_I18N::translate('All trees'); ?></h3>
	<ul class="server_stats">
		<li>
			<?php echo WT_I18N::translate('%s Individuals', siteIndividuals()); ?>
		</li>
		<li>
			<?php echo WT_I18N::translate('%s Media objects', siteMedia()); ?>
		</li>
		<li>
			<?php echo WT_I18N::translate('Your database size is currently %s MB', $db_size); ?>
		</li>
		<li>
			<?php echo WT_I18N::translate('Your files including media items are currently using %s MB', $dir_size); ?>
		</li>
		<li>
			<?php echo WT_I18N::translate('Total server space used is therefore %s MB', $total_size); ?>
		</li>
	</ul>
</div>
