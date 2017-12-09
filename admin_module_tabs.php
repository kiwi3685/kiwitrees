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

define('KT_SCRIPT_NAME', 'admin_module_tabs.php');
require 'includes/session.php';
require KT_ROOT.'includes/functions/functions_edit.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('Module administration'))
	->pageHeader()
	->addInlineJavascript('
    jQuery("#tabs_table").sortable({items: ".sortme", forceHelperSize: true, forcePlaceholderSize: true, opacity: 0.7, cursor: "move", axis: "y"});

    //-- update the order numbers after drag-n-drop sorting is complete
    jQuery("#tabs_table").bind("sortupdate", function(event, ui) {
			jQuery("#"+jQuery(this).attr("id")+" input").each(
				function (index, value) {
					value.value = index+1;
				}
			);
		});
	');

$modules = KT_Module::getActiveTabs(KT_GED_ID, KT_PRIV_HIDE);

$action = safe_POST('action');

if ($action == 'update_mods' && KT_Filter::checkCsrf()) {
	foreach ($modules as $module_name => $module) {
		foreach (KT_Tree::getAll() as $tree) {
			$access_level = safe_POST("access-{$module_name}-{$tree->tree_id}", KT_REGEX_INTEGER, $module->defaultAccessLevel());
			KT_DB::prepare(
				"REPLACE INTO `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, 'tab', ?)"
			)->execute(array($module_name, $tree->tree_id, $access_level));
		}
		$order = safe_POST('order-'.$module_name);
		KT_DB::prepare(
			"UPDATE `##module` SET tab_order=? WHERE module_name=?"
		)->execute(array($order, $module_name));
		$module->order = $order; // Make the new order take effect immediately
	}
	uasort($modules, create_function('$x,$y', 'return $x->order > $y->order;'));
}

?>
<div id="tabs">
	<form method="post" action="<?php echo KT_SCRIPT_NAME; ?>">
		<input type="hidden" name="action" value="update_mods">
		<?php echo KT_Filter::getCsrf(); ?>
		<table id="tabs_table" class="modules_table">
			<thead>
				<tr>
					<th><?php echo KT_I18N::translate('Tab'); ?></th>
					<th><?php echo KT_I18N::translate('Description'); ?></th>
					<th><?php echo KT_I18N::translate('Order'); ?></th>
					<th><?php echo KT_I18N::translate('Access level'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$order = 1;
				foreach ($modules as $module) {
					?>
					<tr class="sortme">
						<td>
							<?php
							if ( $module instanceof KT_Module_Config ) {
								echo '<a href="', $module->getConfigLink(), '">';
							}
							echo $module->getTitle();
							if ( $module instanceof KT_Module_Config && array_key_exists($module->getName(), KT_Module::getActiveModules() ) ) {
								echo ' <i class="fa fa-cogs"></i></a>';
							}
							?>
						</td>
						<td>
							<?php echo $module->getDescription(); ?>
						</td>
						<td>
							<input type="text" size="3" value="<?php echo $order; ?>" name="order-<?php echo $module->getName(); ?>">
						</td>
						<td>
							<table class="modules_table2">
								<?php foreach (KT_Tree::getAll() as $tree) { ?>
									<tr>
										<td>
											<?php echo $tree->tree_title_html; ?>
										</td>
										<td>
											<?php
												$access_level = KT_DB::prepare(
													"SELECT access_level FROM `##module_privacy` WHERE gedcom_id=? AND module_name=? AND component='tab'"
												)->execute(array($tree->tree_id, $module->getName()))->fetchOne();
												if ($access_level === null) {
													$access_level = $module->defaultAccessLevel();
												}
												echo edit_field_access_level('access-' . $module->getName() . '-' . $tree->tree_id, $access_level);
											?>
										</td>
									</tr>
								<?php } ?>
							</table>
						</td>
					</tr>
				<?php
				$order++;
				}
				?>
			</tbody>
		</table>
		<button class="btn btn-primary show" type="submit">
			<i class="fa fa-floppy-o"></i>
			<?php echo KT_I18N::translate('save'); ?>
		</button>
	</form>
</div>
