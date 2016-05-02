<?php
// Module Administration User Interface.
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
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

define('WT_SCRIPT_NAME', 'admin_modules.php');
require 'includes/session.php';
require WT_ROOT.'includes/functions/functions_edit.php';

$controller = new WT_Controller_Page();
$controller
	->requireAdminLogin()
	->setPageTitle(WT_I18N::translate('Module administration'));

$modules = WT_Module::getInstalledModules('disabled');

$module_status = WT_DB::prepare("SELECT module_name, status FROM `##module`")->fetchAssoc();

switch (WT_Filter::post('action')) {
case 'update_mods':
	if (WT_Filter::checkCsrf()) {
		foreach ($modules as $module_name=>$status) {
			$new_status = WT_Filter::post("status-{$module_name}", '[01]');
			if ($new_status !== null) {
				$new_status = $new_status ? 'enabled' : 'disabled';
				if ($new_status != $status) {
					WT_DB::prepare("UPDATE `##module` SET status=? WHERE module_name=?")->execute(array($new_status, $module_name));
					$module_status[$module_name] = $new_status;
				}
			}
		}
	}
	header('Location: admin_modules.php');
	break;
}

switch (WT_Filter::get('action')) {
case 'delete_module':
	$module_name = WT_Filter::get('module_name');
	WT_DB::prepare(
		"DELETE `##block_setting`".
		" FROM `##block_setting`".
		" JOIN `##block` USING (block_id)".
		" JOIN `##module` USING (module_name)".
		" WHERE module_name=?"
	)->execute(array($module_name));
	WT_DB::prepare(
		"DELETE `##block`".
		" FROM `##block`".
		" JOIN `##module` USING (module_name)".
		" WHERE module_name=?"
	)->execute(array($module_name));
	WT_DB::prepare("DELETE FROM `##module_setting` WHERE module_name=?")->execute(array($module_name));
	WT_DB::prepare("DELETE FROM `##module_privacy` WHERE module_name=?")->execute(array($module_name));
	WT_DB::prepare("DELETE FROM `##module`         WHERE module_name=?")->execute(array($module_name));
	unset($modules[$module_name]);
	unset($module_status[$module_name]);
	break;
}

$controller
	->pageHeader()
	->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
	->addInlineJavascript('
	  function reindexMods(id) {
			jQuery("#"+id+" input").each(
				function (index, value) {
					value.value = index+1;
				});
	  }

		var oTable = jQuery("#installed_table").dataTable( {
			"sDom": \'<"H"pf<"dt-clear">irl>t<"F"pl>\',
			'.WT_I18N::datatablesI18N().',
			"bJQueryUI": true,
			"bAutoWidth":false,
			"aaSorting": [[ 1, "asc" ]],
			"iDisplayLength": 20,
			"sPaginationType": "full_numbers",
			"bStateSave": true,
			"iCookieDuration": 180,
			"aoColumns" : [
				{ bSortable: false, sClass: "center" },
				{ sType: "html"},
				null,
				{ sClass: "center" },
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
		<form method="post" action="<?php echo WT_SCRIPT_NAME; ?>">
			<input type="hidden" name="action" value="update_mods">
			<?php echo WT_Filter::getCsrf(); ?>
			<table id="installed_table" border="0" cellpadding="0" cellspacing="1">
				<thead>
					<tr>
					<th><?php echo WT_I18N::translate('Enabled'); ?></th>
					<th width="120px"><?php echo WT_I18N::translate('Module'); ?></th>
					<th><?php echo WT_I18N::translate('Description'); ?></th>
					<th><?php echo WT_I18N::translate('Block'); ?></th>
					<th><?php echo WT_I18N::translate('Chart'); ?></th>
					<th><?php echo WT_I18N::translate('List'); ?></th>
					<th><?php echo WT_I18N::translate('Menu'); ?></th>
					<th><?php echo WT_I18N::translate('Report'); ?></th>
					<th><?php echo WT_I18N::translate('Resource'); ?></th>
					<th><?php echo WT_I18N::translate('Sidebar'); ?></th>
					<th><?php echo WT_I18N::translate('Tab'); ?></th>
					<th><?php echo WT_I18N::translate('Widget'); ?></th>
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
									<td>';
										if ( $module instanceof WT_Module_Config ) {
											echo '<a href="', $module->getConfigLink(), '">';
										}
										echo $module->getTitle();
										if ( $module instanceof WT_Module_Config && array_key_exists( $module_name, WT_Module::getActiveModules() ) ) {
											echo ' <i class="fa fa-cogs"></i></a>';
										}
									echo '</td>
									<td>', $module->getDescription(), '</td>
									<td>', $module instanceof WT_Module_Block   	? WT_I18N::translate('Home page') : '-', '</td>
									<td>', $module instanceof WT_Module_Chart   	? WT_I18N::translate('Chart') : '-', '</td>
									<td>', $module instanceof WT_Module_List   		? WT_I18N::translate('List') : '-', '</td>
									<td>', $module instanceof WT_Module_Menu    	? WT_I18N::translate('Menu') : '-', '</td>
									<td>', $module instanceof WT_Module_Report  	? WT_I18N::translate('Report') : '-', '</td>
									<td>', $module instanceof WT_Module_Resources   ? WT_I18N::translate('Resource') : '-', '</td>
									<td>', $module instanceof WT_Module_Sidebar 	? WT_I18N::translate('Sidebar') : '-', '</td>
									<td>', $module instanceof WT_Module_Tab     	? WT_I18N::translate('Tab') : '-', '</td>
									<td>', $module instanceof WT_Module_Widget  	? WT_I18N::translate('Widget') : '-', '</td>
								</tr>
							';
						} else {
							// Module can't be found on disk?
							// Don't delete it automatically.  It may be temporarily missing, after a re-installation, etc.
							echo
								'<tr>
									<td>&nbsp;</td>
									<td class="error">', $module_name, '</td>
									<td>
										<a class="error" href="'.WT_SCRIPT_NAME.'?action=delete_module&amp;module_name='.$module_name.'">',
											WT_I18N::translate('This module cannot be found.  Delete its configuration settings.'),
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
									<td>&nbsp;</td>
								</tr>';
						}
					}
					?>
				</tbody>
			</table>
			<button class="btn btn-primary show" type="submit">
				<i class="fa fa-floppy-o"></i>
				<?php echo WT_I18N::translate('save'); ?>
			</button>
		</form>
	</div>
</div>
