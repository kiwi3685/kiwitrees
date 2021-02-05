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

define('KT_SCRIPT_NAME', 'admin_site_info.php');
require './includes/session.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('Server information'))
	->pageHeader();

$variables = KT_DB::prepare("SHOW VARIABLES")->fetchAssoc();
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
						<h2><?php echo KT_I18N::translate('MySQL variables'); ?></h2>
					</td>
				</tr>
			</tbody>
		</table>
		<table>
			<tbody>
				<?php foreach ($variables as $variable => $value): ?>
					<tr>
						<td class="e"><?php echo KT_Filter::escapeHtml($variable); ?></td>
						<td class="v"><?php echo KT_Filter::escapeHtml($value); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>
