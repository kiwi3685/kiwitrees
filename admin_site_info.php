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

define('WT_SCRIPT_NAME', 'admin_site_info.php');
require './includes/session.php';

$controller = new WT_Controller_Page();
$controller
	->requireAdminLogin()
	->setPageTitle(WT_I18N::translate('Server information'))
	->pageHeader();

$variables = WT_DB::prepare("SHOW VARIABLES")->fetchAssoc();
array_walk($variables, function (&$x) { $x = str_replace(',', ', ', $x); });

ob_start();
phpinfo(INFO_ALL & ~INFO_CREDITS & ~INFO_LICENSE);
preg_match('%<body>(.*)</body>%s', ob_get_clean(), $matches);
$html = $matches[1];
?>
<div id="server-info">
	<h2><?php echo $controller->getPageTitle(); ?></h2>
	<div class="php-info"><?php echo $html; ?></div>
	<div class="php-info">
		<table>
			<tbody>
				<tr class="h">
					<td>
						<h2><?php echo WT_I18N::translate('MySQL variables'); ?></h2>
					</td>
				</tr>
			</tbody>
		</table>
		<table>
			<tbody>
				<?php foreach ($variables as $variable => $value): ?>
					<tr>
						<td class="e"><?php echo WT_Filter::escapeHtml($variable); ?></td>
						<td class="v"><?php echo WT_Filter::escapeHtml($value); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
