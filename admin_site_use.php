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

define('KT_SCRIPT_NAME', 'admin_site_use.php');
require './includes/session.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('Server usage'))
	->pageHeader();

function siteIndividuals() {
	$count = KT_DB::prepare("SELECT SQL_CACHE COUNT(*) FROM `##individuals`")
		->execute()
		->fetchOne();
	return	KT_I18N::number($count);
}

function siteMedia() {
	$count = KT_DB::prepare("SELECT SQL_CACHE COUNT(*) FROM `##media` WHERE (m_filename NOT LIKE 'http://%' AND m_filename NOT LIKE 'https://%')")
		->execute()
		->fetchOne();
	return	KT_I18N::number($count);
}

// functions_db.php
$db_size		= format_size(db_size());
$directory_size	= format_size(directory_size());
$total_size		= format_size(db_size() + directory_size());

?>

<div id="size">
	<h3><?php echo KT_I18N::translate('All trees'); ?></h3>
	<ul class="server_stats">
		<li>
			<?php echo KT_I18N::translate('%s Individuals', siteIndividuals()); ?>
		</li>
		<li>
			<?php echo KT_I18N::translate('%s Media objects', siteMedia()); ?>
		</li>
		<li>
			<?php echo KT_I18N::translate('Your database size is currently %s', $db_size); ?>
		</li>
		<li>
			<?php echo KT_I18N::translate('Your files including media items are currently using %s', $directory_size); ?>
		</li>
		<li>
			<?php echo KT_I18N::translate('Total server space used is therefore %s', $total_size); ?>
		</li>
	</ul>
</div>
