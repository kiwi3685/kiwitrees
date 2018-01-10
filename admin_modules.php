<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2018 kiwitrees.net
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

define('KT_SCRIPT_NAME', 'admin_modules.php');
require 'includes/session.php';
require KT_ROOT.'includes/functions/functions_edit.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('Module administration'));

$modules = KT_Module::getInstalledModules('disabled');

$module_status = KT_DB::prepare("SELECT module_name, status FROM `##module`")->fetchAssoc();

switch (KT_Filter::post('action')) {
case 'update_mods':
	if (KT_Filter::checkCsrf()) {
		foreach ($modules as $module_name=>$status) {
			$new_status = KT_Filter::post("status-{$module_name}", '[01]');
			if ($new_status !== null) {
				$new_status = $new_status ? 'enabled' : 'disabled';
				if ($new_status != $status) {
					KT_DB::prepare("UPDATE `##module` SET status=? WHERE module_name=?")->execute(array($new_status, $module_name));
					$module_status[$module_name] = $new_status;
				}
			}
		}
	}
	header('Location: admin_modules.php');
	break;
}

switch (KT_Filter::get('action')) {
case 'delete_module':
	$module_name = KT_Filter::get('module_name');
	KT_DB::prepare(
		"DELETE `##block_setting`".
		" FROM `##block_setting`".
		" JOIN `##block` USING (block_id)".
		" JOIN `##module` USING (module_name)".
		" WHERE module_name=?"
	)->execute(array($module_name));
	KT_DB::prepare(
		"DELETE `##block`".
		" FROM `##block`".
		" JOIN `##module` USING (module_name)".
		" WHERE module_name=?"
	)->execute(array($module_name));
	KT_DB::prepare("DELETE FROM `##module_setting` WHERE module_name=?")->execute(array($module_name));
	KT_DB::prepare("DELETE FROM `##module_privacy` WHERE module_name=?")->execute(array($module_name));
	KT_DB::prepare("DELETE FROM `##module`         WHERE module_name=?")->execute(array($module_name));
	unset($modules[$module_name]);
	unset($module_status[$module_name]);
	break;
}

$controller
	->pageHeader()
	->addExternalJavascript(KT_JQUERY_DATATABLES_URL)
	->addExternalJavascript(KT_JQUERY_DT_HTML5)
	->addExternalJavascript(KT_JQUERY_DT_BUTTONS)
	->addInlineJavascript('
	  function reindexMods(id) {
			jQuery("#"+id+" input").each(
				function (index, value) {
					value.value = index+1;
				});
	  }

		var oTable = jQuery("#installed_table").dataTable( {
			"sDom": \'<"H"pf<"dt-clear">irl>t<"F"pl>\',
			'.KT_I18N::datatablesI18N().',
			buttons: [{extend: "csv", exportOptions: {columns: [0,6,9,12,15,17] }}],
			jQueryUI: true,
			autoWidth: false,
			processing: true,
			retrieve: true,
			sorting: [[ 2, "asc" ]],
			displayLength: 20,
			pagingType: "full_numbers",
			stateSave: true,
			stateDuration: -1,
			columns : [
				{ dataSort: 1, sClass: "center" },
				{ type: "unicode", visible: false },
				{ sType: "html"},
				null,
				{ sClass: "center" },
				{ sClass: "center" },
				{ sClass: "center" },
				{ sClass: "center" },
				{ sClass: "center" },
				{ sClass: "center" },
				{ sClass: "center" },
				{ sClass: "center" }
			]
		});
	');

?>
<div id="module-admin-page">
	<div id="tabs">
		<form method="post" action="<?php echo KT_SCRIPT_NAME; ?>">
			<input type="hidden" name="action" value="update_mods">
			<?php echo KT_Filter::getCsrf(); ?>
			<table id="installed_table" border="0" cellpadding="0" cellspacing="1">
				<thead>
					<tr>
						<th><?php echo KT_I18N::translate('Enabled'); ?></th>
						<th>STATUS</th>
						<th style="width: 120px;"><?php echo KT_I18N::translate('Module'); ?></th>
						<th style="width: 400px;"><?php echo KT_I18N::translate('Description'); ?></th>
						<th><?php echo KT_I18N::translate('Block'); ?></th>
						<th><?php echo KT_I18N::translate('Chart'); ?></th>
						<th><?php echo KT_I18N::translate('List'); ?></th>
						<th><?php echo KT_I18N::translate('Menu'); ?></th>
						<th><?php echo KT_I18N::translate('Report'); ?></th>
						<th><?php echo KT_I18N::translate('Sidebar'); ?></th>
						<th><?php echo KT_I18N::translate('Tab'); ?></th>
						<th><?php echo KT_I18N::translate('Widget'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($module_status as $module_name => $status) {
						if (array_key_exists($module_name, $modules)) {
							$module = $modules[$module_name];
							echo
								'<tr>
									<td>', two_state_checkbox('status-' . $module_name, $status == 'enabled'), '</td>
									<td>', $status, '</td>
									<td>';
										if ( $module instanceof KT_Module_Config ) {
											echo '<a href="', $module->getConfigLink(), '">';
										}
										echo $module->getTitle();
										if ( $module instanceof KT_Module_Config && array_key_exists( $module_name, KT_Module::getActiveModules() ) ) {
											echo ' <i class="fa fa-cogs"></i></a>';
										}
									echo '</td>
									<td>', $module->getDescription(), '</td>
									<td>', $module instanceof KT_Module_Block   	? ($module->isGedcomBlock() ? KT_I18N::translate('Home') : KT_I18N::translate('Other')) : '-', '</td>
									<td>', $module instanceof KT_Module_Chart   	? KT_I18N::translate('Chart') : '-', '</td>
									<td>', $module instanceof KT_Module_List   		? KT_I18N::translate('List') : '-', '</td>
									<td>', $module instanceof KT_Module_Menu    	? KT_I18N::translate('Menu') : '-', '</td>
									<td>', $module instanceof KT_Module_Report  	? KT_I18N::translate('Report') : '-', '</td>
									<td>', $module instanceof KT_Module_Sidebar 	? KT_I18N::translate('Sidebar') : '-', '</td>
									<td>', $module instanceof KT_Module_Tab     	? KT_I18N::translate('Tab') : '-', '</td>
									<td>', $module instanceof KT_Module_Widget  	? KT_I18N::translate('Widget') : '-', '</td>
								</tr>
							';
						} else {
							// Module can't be found on disk?
							// Don't delete it automatically.  It may be temporarily missing, after a re-installation, etc.
							echo
								'<tr>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td class="error">', $module_name, '</td>
									<td>
										<a class="error" href="'.KT_SCRIPT_NAME.'?action=delete_module&amp;module_name='.$module_name.'">',
											KT_I18N::translate('This module cannot be found.  Delete its configuration settings.'),
										'</a>
									</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
									<td>&nbsp;</td>
								</tr>';
						}
					}
					?>
				</tbody>
			</table>
			<button class="btn btn-primary show" type="submit">
				<i class="fa fa-floppy-o"></i>
				<?php echo KT_I18N::translate('Save'); ?>
			</button>
		</form>
	</div>
</div>
