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
?>
<!--   ====== The proposed Census Text ========= -->
<div class="cens_text wrap">
	<div class="cens_text_header">
		<h3>
			<?php echo WT_I18N::translate('Proposed Census Text'); ?>
		</h3>
	</div>
	<div>
		<textarea wrap="off" name="NOTE" id="NOTE"></textarea>
		<p class="center">
			<?php echo print_specialchar_link('NOTE'); ?>
		</p>
		<button class="btn btn-primary" type="button" onclick="preview();">
			<i class="fa fa-eye"></i>
			<?php echo WT_I18N::translate('preview'); ?>
		</button>
		<button class="btn btn-primary" type="submit" onclick="caSave();" >
			<i class="fa fa-save"></i>
			<?php echo WT_I18N::translate('save'); ?>
		</button>
		<button class="btn btn-primary" type="button" onclick="window.close();">
			<i class="fa fa-times"></i>
			<?php echo WT_I18N::translate('close'); ?>
		</button>
	</div>
</div>
