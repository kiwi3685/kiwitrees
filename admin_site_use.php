<?php
// Displays information on the PHP installation
//
// Provides links for administrators to get to other administrative areas of the site
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2015 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2010  PGV Development Team
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

define('WT_SCRIPT_NAME', 'admin_site_use.php');
require './includes/session.php';

$controller = new WT_Controller_Page();
$controller
	->requireAdminLogin()
	->setPageTitle(WT_I18N::translate('Server usage'))
	->pageHeader();

function siteIndividuals() {
	$count = WT_DB::prepare("SELECT SQL_CACHE COUNT(*) FROM `##individuals`")
		->execute()
		->fetchOne();
	return	WT_I18N::number($count);
}

function siteMedia() {
	$count = WT_DB::prepare("SELECT SQL_CACHE COUNT(*) FROM `##media`")
		->execute()
		->fetchOne();
	return	WT_I18N::number($count);
}

?>

<div id="size">
	<h3><?php echo WT_I18N::translate('All trees'); ?></h3>
	<ul class="server_stats">
		<li>
			<span><?php echo WT_I18N::translate('Individuals'); ?></span>
			<span class="filler">&nbsp;</span>
			<span><?php echo siteIndividuals(); ?></span>
		</li>
		<li>
			<span><?php echo WT_I18N::translate('Media objects'); ?></span>
			<span class="filler">&nbsp;</span>
			<span><?php echo siteMedia(); ?></span>
		</li>
		<li>
			<span>Your database size is currently</span>
			<span class="filler"></span>
			<span><?php echo WT_I18N::number(db_size()); ?> MB</span>
		</li>
		<li>
			<span>Your files are currently using</span>
			<span class="filler"></span>
			<span><?php echo WT_I18N::number(directory_size()); ?> MB</span>
		</li>
		<li>
			<span>Total server space used is therefore</span>
			<span class="filler"></span>
			<span><?php echo WT_I18N::number(db_Size() + directory_size()); ?> MB</span>
		</li>
	</ul>
</div>

