<?php
/*
 * webtrees - simpl_menu module
 * Version 1.1
 * Copyright (C) 2010-2011 Nigel Osborne and kiwtrees.net. All rights reserved.
 *
 * webtrees: Web based Family History software
 * Copyright (C) 2011 webtrees development team.
 *
 * Derived from PhpGedView
 * Copyright (C) 2002 to 2010  PGV Development Team.  All rights reserved.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class backup_to_dropbox_WT_Module extends WT_Module implements WT_Module_Config {
	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: The name of a module. Dropbox is a trademark.  Do not translate it. */WT_I18N::translate('Backup to Dropbox'); //CHANGE THIS
	}

	// Extend class WT_Module
	public function getDescription() {
		return WT_I18N::translate('Allows you to backup your media files to your Dropbox account');
	}

	// Implement WT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_dropbox';
	}

	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'admin_dropbox':
			$this->config();
			break;
		}
	}

	private function config() {
		//set access token (Temporary until input of these is established)
//	    $token			= 'hDSLmS8OcwQAAAAAAAARcKz8ou1wFWBIP3STSz8AKa0NHu_1NcfXk_BWwXam4_Jv'; //'Your access token here';
//	    $projectFolder	= 'Kiwitrees file backup';

		require_once WT_MODULES_DIR . $this->getName() . '/dropbox-sdk/lib/Dropbox/autoload.php';
		require WT_MODULES_DIR . $this->getName() . '/backup.php';

		$controller	= new WT_Controller_Page();
		$controller
			->requireAdminLogin()
			->setPageTitle($this->getTitle())
			->pageHeader();

		if (WT_Filter::post('action') == 'update') {
			set_module_setting($this->getName(), 'DB_FOLDER', WT_Filter::post('NEW_DB_FOLDER'));
			set_module_setting($this->getName(), 'DB_TOKEN', WT_Filter::post('NEW_DB_TOKEN'));

			AddToLog('Backup to Dropbox settings updated', 'config');
		}

		$DB_FOLDER	= get_module_setting($this->getName(), 'DB_FOLDER', '');
		$DB_TOKEN	= get_module_setting($this->getName(), 'DB_TOKEN', '');

		$bk = new Backup($DB_TOKEN, $this->getName(), $DB_FOLDER);

		if(WT_Filter::post('action') == 'backup') {
			$bk->upload(WT_DATA_DIR);
		}

		?>
		<div id="backup_to_dropbox-page">
			<h2><?php echo $this->getTitle(); ?></h2>
			<div class="help_text">
				<span class="help_content">
					<?php echo /* I18N: Help text for the “Backup to Dropbox” configuration setting */ WT_I18N::translate('If you have a Dropbox account you can use this tool to copy the content of your kiwitrees data folder to Dropbox. The data folder includes any GEDCOM files you have created and your media files. More details on setting this up are available at the kiwitrees .net FAQ page <a href="#" target="_blank"><b>here</b></a>.'); ?>
				</span>
			</div>
			<hr>
			<div class="backup_settings" style="margin: 20px auto;">
				<h3><?php echo WT_I18N::translate('Settings'); ?></h3>
				<div class="help_text">
					<span class="help_content">
						<?php echo WT_I18N::translate('See the FAQ page for more information about these settings.'); ?>
					</span>
				</div>
				<form method="post">
					<input type="hidden" name="action" value="update">
					<div style="margin: 20px auto;">
						<div style="margin: 10px auto;">
							<label style="font-weight:600;"><?php echo /* I18N: Dropbox secure access token */ WT_I18N::translate('Dropbox folder'); ?></label>
							<input type="text" name="NEW_DB_FOLDER" value="<?php echo $DB_FOLDER; ?>" size="83">
						</div>
						<div>
							<label style="font-weight:600;"><?php echo /* I18N: Dropbox secure access token */ WT_I18N::translate('Dropbox token'); ?></label>
							<input type="password" name="NEW_DB_TOKEN" value="<?php echo $DB_TOKEN; ?>" size="83">
						</div>
					</div>
					<button class="btn btn-primary update" type="submit">
						<i class="fa fa-save"></i>
						<?php echo WT_I18N::translate('update'); ?>
					</button>
				</form>
			</div>
			<hr style="clear: both;">
			<div id="backup_list" style="margin: 20px auto;">
				<h3><?php echo WT_I18N::translate('These are the files and folders that will be sent to Dropbox.'); ?></h3>
				<h4><?php echo /* I18N: Explanation of files included in backup to Dropbox */ WT_I18N::translate('Where a folder is shown, the entire contents will be sent.'); ?></h4>
				<ul style="list-style:none;">
					<?php
					$dir	 = dir(WT_DATA_DIR);
					$entries = array();
					while (false !== ($entry = $dir->read())) {
						$entries[] = $entry;
					}
					sort($entries);
					foreach ($entries as $entry) {
						if ($entry[0] != '.' && !in_array($entry, $bk->ignoreList())) { ?>
							<li class="facts_value">
								<?php
								$file_path = WT_DATA_DIR . $entry;
								if (is_dir($file_path)) {
									echo '<i class="fa fa-folder-open-o"></i>' . $entry;
								} else {
									echo '<i class="fa fa-file-o"></i>' . $entry;
								} ?>
							</li>
						<?php } ?>
					<?php }
					$dir->close(); ?>
				</ul>
				<form method="post" action="<?php echo $this->getConfigLink(); ?>">
					<input type="hidden" name="action" value="backup">
					<button class="btn btn-primary delete" type="submit" style="color: #007ee5; font-weight: 600;">
						<i class="fa fa-dropbox" style="font-size: 2em;"></i>
						<?php echo WT_I18N::translate('backup'); ?>
					</button>
				</form>
				<div style="clear: both; font-size: 90%; font-style: italic;"><?php echo /* I18N: Dropbox copyright statement */ WT_I18N::translate('"Dropbox and the Dropbox logo are trademarks of Dropbox, Inc."'); ?></div>
			</div>
		</div>
	<?php }
}
