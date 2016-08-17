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
		return WT_I18N::translate('Allows you to backup your media and other files to a Dropbox account');
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
			set_module_setting($this->getName(), 'DB_EXCLUDE', str_replace(' ', '', WT_Filter::post('NEW_DB_EXCLUDE')));

			AddToLog('Backup to Dropbox settings updated', 'config');
		}

		$DB_FOLDER	= get_module_setting($this->getName(), 'DB_FOLDER', '');
		$DB_TOKEN	= get_module_setting($this->getName(), 'DB_TOKEN', '');
		$DB_EXCLUDE	= get_module_setting($this->getName(), 'DB_EXCLUDE', '');
		$db_exclude = get_module_setting($this->getName(), 'DB_EXCLUDE', '');
		$DB_EXCLUDE = explode(',', $db_exclude);

		if ($DB_FOLDER && $DB_FOLDER) {
			$bk = new Backup($DB_TOKEN, $this->getName(), $DB_FOLDER);
		}

		// List of folders
		if ($DB_FOLDER && $DB_FOLDER) {
			$dirtocopy	= WT_DATA_DIR;
			$exclude	= array_merge($bk->ignoreList(), $DB_EXCLUDE);
			$filter		= function ($file, $key, $iterator) use ($exclude) {
				if (!in_array($file->getFilename(), $exclude)) {
					return true;
				}
			};
			$innerIterator = new RecursiveDirectoryIterator(
				$dirtocopy,
				RecursiveDirectoryIterator::SKIP_DOTS
			);
			$iterator = new RecursiveIteratorIterator(
				new RecursiveCallbackFilterIterator($innerIterator, $filter),
				\RecursiveIteratorIterator::SELF_FIRST,
				\RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
			);
		}

		if(WT_Filter::post('action') == 'backup') {
			$dir = str_replace(WT_ROOT, "", WT_DATA_DIR);
			$bk->upload($dir, $db_exclude);
		}

		?>
		<div id="backup_to_dropbox-page">
			<a class="current faq_link" href="http://kiwitrees.net/faqs/modules-faqs/pages/" target="_blank" title="<?php echo WT_I18N::translate('View FAQ for this page.'); ?>"><?php echo WT_I18N::translate('View FAQ for this page.'); ?><i class="fa fa-comments-o"></i></a>
			<h2><?php echo $this->getTitle(); ?></h2>
			<div class="help_text">
				<span class="help_content">
					<?php echo /* I18N: Help text for the “Backup to Dropbox” configuration setting */ WT_I18N::translate('If you have a Dropbox account you can use this tool to copy the content of your kiwitrees data folder to Dropbox. The data folder includes any GEDCOM files you have created and your media files. More details on setting this up are available at the kiwitrees.net FAQ page.'); ?>
				</span>
			</div>
			<hr>
			<div class="backup_settings" style="margin: 20px auto;">
				<h3><?php echo WT_I18N::translate('Settings'); ?></h3>
				<form method="post">
					<input type="hidden" name="action" value="update">
					<div class="tree_config odd">
						<label><?php echo /* I18N: Dropbox secure access token */ WT_I18N::translate('Dropbox folder'); ?></label>
						<div class="input_group">
							<input type="text" name="NEW_DB_FOLDER" value="<?php echo $DB_FOLDER; ?>">
						</div>
					</div>
					<div class="tree_config even">
						<label><?php echo /* I18N: Dropbox secure access token */ WT_I18N::translate('Dropbox token'); ?></label>
						<div class="input_group">
							<input type="password" name="NEW_DB_TOKEN" value="<?php echo $DB_TOKEN; ?>">
						</div>
					</div>
					<?php if ($DB_FOLDER && $DB_FOLDER) { ?>
						<div class="tree_config odd">
							<label><?php echo WT_I18N::translate('These files and folders are always excluded from the backup.'); ?></label>
							<div class="input_group">
								<?php
								$html = '';
								foreach ($bk->ignoreList() as $file) {
									$html .= $file . ',';
								}
								$html = rtrim($html, ",");
								?>
								<input type="text" value="<?php echo $html; ?>">
							</div>
						</div>
					<?php } ?>
					<div class="tree_config even">
						<label><?php echo WT_I18N::translate('Also exclude these files and folders'); ?></label>
						<div class="input_group">
							<input type="text" name="NEW_DB_EXCLUDE" value="<?php echo $db_exclude; ?>">
							<span class="help_content">
								<?php echo WT_I18N::translate('Separate each item by a comma'); ?>
							</span>
						</div>
					</div>
					<button class="btn btn-primary update" type="submit">
						<i class="fa fa-save"></i>
						<?php echo WT_I18N::translate('update'); ?>
					</button>
				</form>
			</div>
			<hr class="clearfloat">
			<?php if ($DB_FOLDER && $DB_FOLDER) { ?>
				<div id="backup_list" style="margin: 20px auto;">
					<h3><?php echo WT_I18N::translate('These are the files and folders that will be sent to Dropbox.'); ?></h3>
					<h4><?php echo /* I18N: Explanation of files included in backup to Dropbox */ WT_I18N::translate('Where a folder is shown, the entire contents will be sent.'); ?></h4>
					<ul style="list-style:none;">
						<?php
						foreach ($iterator as $pathname => $fileInfo) {
							$file	= str_replace(WT_DATA_DIR, "", $fileInfo);
							$file	= str_replace("\\", "/", $file);
							$facts	= preg_split('/\//', $file);
							if (count($facts) < 2) {
								echo '
									<li>
										<i class="fa ' . (is_dir($fileInfo) ? 'fa-folder-open-o' : 'fa fa-file-o') . '"></i>' . $fileInfo->getFilename() . '
									</li>
								';
							}
						}
						?>
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
			<?php } ?>
		</div>
	<?php }
}
