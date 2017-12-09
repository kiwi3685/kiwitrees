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

define('KT_SCRIPT_NAME', 'admin_trees_places.php');

require './includes/session.php';
require KT_ROOT . 'includes/functions/functions_import.php';
require KT_ROOT . 'includes/functions/functions_edit.php';

$search  = KT_Filter::post('search', null, KT_Filter::get('search'));
$replace = KT_Filter::post('replace');
$confirm = KT_Filter::post('confirm');

$changes = array();

if ($search && $replace) {
	$rows = KT_DB::prepare(
		"SELECT i_id AS xref, i_file AS gedcom_id, i_gedcom AS gedcom" .
		" FROM `##individuals`" .
		" LEFT JOIN `##change` ON (i_id = xref AND i_file=gedcom_id AND status='pending')".
		" WHERE i_file = ?" .
		" AND COALESCE(new_gedcom, i_gedcom) REGEXP CONCAT('\n2 PLAC ([^\n]*, )*', ?, '(\n|$)')"
	)->execute(array(KT_GED_ID, preg_quote($search)))->fetchAll();
	foreach ($rows as $row) {
		$record = KT_Person::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
		if ($record) {
			foreach ($record->getFacts() as $fact) {
				$old_place = $fact->getPlace();
				if (preg_match('/(^|, )' . preg_quote($search, '/') . '$/i', $old_place)) {
					$new_place = preg_replace('/(^|, )' . preg_quote($search, '/') . '$/i', '$1' . $replace, $old_place);
					$changes[$old_place] = $new_place;
					if ($confirm == 'update') {
						$gedcom = preg_replace('/(\n2 PLAC (?:.*, )*)' . preg_quote($search, '/') . '(\n|$)/i', '$1' . $replace . '$2', $row->gedcom);
						replace_gedrec($row->xref, KT_GED_ID, $gedcom, false);
					}
				}
			}
		}
	}
	$rows = KT_DB::prepare(
		"SELECT f_id AS xref, f_file AS gedcom_id, f_gedcom AS gedcom".
		" FROM `##families`".
		" LEFT JOIN `##change` ON (f_id = xref AND f_file=gedcom_id AND status='pending')".
		" WHERE f_file = ?" .
		" AND COALESCE(new_gedcom, f_gedcom) REGEXP CONCAT('\n2 PLAC ([^\n]*, )*', ?, '(\n|$)')"
	)->execute(array(KT_GED_ID, preg_quote($search)))->fetchAll();
	foreach ($rows as $row) {
		$record = KT_Family::getInstance($row->xref, $row->gedcom_id, $row->gedcom);
		if ($record) {
			foreach ($record->getFacts() as $fact) {
				$old_place = $fact->getPlace();
				if (preg_match('/(^|, )' . preg_quote($search, '/') . '$/i', $old_place)) {
					$new_place = preg_replace('/(^|, )' . preg_quote($search, '/') . '$/i', '$1' . $replace, $old_place);
					$changes[$old_place] = $new_place;
					if ($confirm == 'update') {
						$gedcom = preg_replace('/(\n2 PLAC (?:.*, )*)' . preg_quote($search, '/') . '(\n|$)/i', '$1' . $replace . '$2', $row->gedcom);
						replace_gedrec($row->xref, KT_GED_ID, $gedcom, false);
					}
				}
			}
		}
	}
}

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Administration - place edit'))
	->pageHeader()
	->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
	->addInlineJavascript('autocomplete();');
?>
<div id="places">
	<h2>
		<?php echo KT_I18N::translate('Update all the place names in a family tree'); ?>
	</h2>
	<p>
		<?php echo KT_I18N::translate('This will update the highest-level part or parts of the place name.  For example, “Mexico” will match “Quintana Roo, Mexico”, but not “Santa Fe, New Mexico”.'); ?>
	</p>
	<form method="post">
		<div id="admin_options">
			<div class="input">
				<label><?php echo KT_I18N::translate('Family tree'); ?></label>
				<?php echo select_edit_control('ged', KT_Tree::getNameList(), null, KT_GEDCOM); ?>
			</div>
			<div class="input">
				<label for="search"><?php echo KT_I18N::translate('Search for'); ?></label>
				<input name="search" id="search" type="text" data-autocomplete-type="PLAC" value="<?php echo KT_Filter::escapeHtml($search); ?>" required><?php echo print_specialchar_link('search'); ?>
			</div>
			<div class="input">
				<label for="replace"><?php echo KT_I18N::translate('Replace with'); ?></label>
				<input name="replace" id="replace" type="text" data-autocomplete-type="PLAC" value="<?php echo KT_Filter::escapeHtml($replace); ?>" required><?php echo print_specialchar_link('replace'); ?>
			</div>
			<p>
				<button type="submit" value="preview"><?php echo /* I18N: button label */ KT_I18N::translate('preview'); ?></button>
				<button type="submit" value="update" name="confirm"><?php echo /* I18N: button label */ KT_I18N::translate('update'); ?></button>
			</p>
		</div>
	</form>

	<p class="error clearfloat">
		<?php echo KT_I18N::translate('Caution! This may take a long time. Be patient.'); ?>
	</p>

	<?php if ($search && $replace) { ?>
		<?php if ($changes) { ?>
		<p>
			<?php echo ($confirm) ? KT_I18N::translate('The following places were changed:') : KT_I18N::translate('The following places would be changed:'); ?>
		</p>
		<ul>
			<?php foreach ($changes as $old_place => $new_place) { ?>
			<li>
				<?php echo KT_Filter::escapeHtml($old_place); ?>
				&nbsp;&rarr;&nbsp;
				<?php echo KT_Filter::escapeHtml($new_place); ?>
			</li>
			<?php } ?>
		</ul>
		<?php } else { ?>
		<p>
			<?php echo KT_I18N::translate('No places were found.'); ?>
		</p>
		<?php } ?>
	<?php } ?>
</div>
