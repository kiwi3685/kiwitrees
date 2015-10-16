<?php
// Census Assistant Control module for webtrees
//
// Census Proposed Text Area File
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2015 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2007 to 2010  PGV Development Team.  All rights reserved.
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

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}
?>
<!--   ====== The proposed Census Text ========= -->
<div class="cens_text wrap">
	<div class="cens_text_header">
		<button class="btn btn-primary" type="button" onclick="preview();">
			<i class="fa fa-eye"></i>
			<?php echo WT_I18N::translate('Preview'); ?>
		</button>
		<button class="btn btn-primary" type="submit" onclick="caSave();" >
			<i class="fa fa-floppy-o"></i>
			<?php echo WT_I18N::translate('Save'); ?>
		</button>
		<h3>
			<?php echo WT_I18N::translate('Proposed Census Text'); ?>
		</h3>
	</div>
	<div>
		<textarea wrap="off" name="NOTE" id="NOTE"></textarea>
		<p class="center">
			<?php echo print_specialchar_link('NOTE'); ?>
		</p>
	</div>
</div>
