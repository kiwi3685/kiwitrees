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

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

?>
<div id="<?php echo $id; ?>" class="block" >
	<table class="blockheader">
		<tr>
			<td class="blockh1" ></td>
			<td class="blockh2" >
				<div class="blockhc"><b><?php echo $title; ?></b></div>
			</td>
			<td class="blockh3"></td>
		</tr>
	</table>
	<div class="blockcontent <?php echo $class; ?>">
		<div class="small_inner_block">
		<?php echo $content; ?>
		</div>
	</div>
</div>
