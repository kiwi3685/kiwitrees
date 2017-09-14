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

define('KT_SCRIPT_NAME', 'block_edit.php');
require './includes/session.php';

$controller = new KT_Controller_Ajax();
$controller->pageHeader();

if (array_key_exists('ckeditor', KT_Module::getActiveModules())) {
	ckeditor_KT_Module::enableEditor($controller);
}

$block_id	= KT_Filter::getInteger('block_id');
$block		= KT_DB::prepare("SELECT SQL_CACHE * FROM `##block` WHERE block_id=?")->execute(array($block_id))->fetchOneRow();

// Check access.  (1) the block must exist, (2) gedcom blocks require
// managers, (3) user blocks require the user or an admin
$blocks = array_merge(KT_Module::getActiveBlocks(KT_GED_ID), KT_Module::getActiveWidgets(KT_GED_ID));
if (
	!$block ||
	!array_key_exists($block->module_name, $blocks) ||
	$block->gedcom_id && !userGedcomAdmin(KT_USER_ID, $block->gedcom_id) ||
	$block->user_id && $block->user_id != KT_USER_ID && !KT_USER_IS_ADMIN
) {
	exit;
}

$block = $blocks[$block->module_name];

?>
<form name="block" method="post" action="block_edit.php?block_id=<?php echo $block_id; ?>" onsubmit="return modalDialogSubmitAjax(this);" >
	<input type="hidden" name="save" value="1">
	<?php echo KT_Filter::getCsrf(); ?>
	<p>
		<?php echo $block->getDescription(); ?>
	</p>
	<table class="facts_table">
		<?php echo $block->configureBlock($block_id); ?>
		<tr>
			<td colspan="2" class="topbottombar">
				<button class="btn btn-primary show" type="submit">
					<i class="fa fa-floppy-o"></i>
					<?php echo KT_I18N::translate('save'); ?>
				</button>
			</td>
		</tr>
	</table>
</form>
