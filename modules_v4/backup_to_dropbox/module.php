<?php
/*
 * webtrees - simpl_menu module
 * Version 1.1
 * Copyright (C) 2010-2011 Nigel Osborne and kiwitrees.net. All rights reserved.
 *
 * webtrees: Web based Family History software
 * Copyright (C) 2011 webtrees development team.
 *
 * Derived from PhpGedView
 * Copyright (C) 2002 to 2010 PGV Development Team
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class backup_to_dropbox_KT_Module extends KT_Module implements KT_Module_Config {
	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: The name of a module. Dropbox is a trademark.  Do not translate it. */KT_I18N::translate('Backup to Dropbox'); //CHANGE THIS
	}

	// Extend class KT_Module
	public function getDescription() {
		return KT_I18N::translate('Allows you to backup your media and other files to a Dropbox account');
	}

	// Implement KT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_dropbox';
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'admin_dropbox':
			$this->config();
			break;
		}
	}

	private function config() {

		$controller	= new KT_Controller_Page();
		$controller
			->restrictAccess(KT_USER_IS_ADMIN)
			->setPageTitle($this->getTitle())
			->pageHeader();

		if (KT_Filter::post('action') == 'update') {
			set_module_setting($this->getName(), 'DB_FOLDER', KT_Filter::post('NEW_DB_FOLDER'));
			set_module_setting($this->getName(), 'DB_TOKEN', KT_Filter::post('NEW_DB_TOKEN'));
			set_module_setting($this->getName(), 'DB_EXCLUDE', str_replace(' ', '', KT_Filter::post('NEW_DB_EXCLUDE')));

			AddToLog('Backup to Dropbox settings updated', 'config');
		}

		if (KT_Filter::post('action') == 'backup') {
			set_module_setting($this->getName(), 'DB_DATE', KT_Filter::post('NEW_DB_DATE'));
		}

		$DB_FOLDER	= get_module_setting($this->getName(), 'DB_FOLDER', '');
		$DB_TOKEN	= get_module_setting($this->getName(), 'DB_TOKEN', '');
		$DB_DATE	= get_module_setting($this->getName(), 'DB_DATE', '');
		$db_exclude = get_module_setting($this->getName(), 'DB_EXCLUDE', '');
		$DB_EXCLUDE = explode(',', $db_exclude);
		$response	= array();

		// List of folders
		if ($DB_TOKEN && $DB_FOLDER) {
			$dirtocopy	= KT_DATA_DIR;
			$exclude	= array_merge($this->ignoreList(), $DB_EXCLUDE);
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

		?>
		<div id="backup_to_dropbox-page">
			<a class="current faq_link" href="http://kiwitrees.net/faqs/modules/backup-dropbox/" target="_blank" rel="noopener noreferrer" title="<?php echo KT_I18N::translate('View FAQ for this page.'); ?>"><?php echo KT_I18N::translate('View FAQ for this page.'); ?><i class="fa fa-comments-o"></i></a>
			<h2><?php echo $this->getTitle(); ?></h2>
			<div class="help_text">
				<span class="help_content">
					<?php echo /* I18N: Help text for the “Backup to Dropbox” configuration setting */ KT_I18N::translate('If you have a Dropbox account you can use this tool to copy the content of your kiwitrees data folder to Dropbox. The data folder includes any GEDCOM files you have created and your media files. More details on setting this up are available at the kiwitrees.net FAQ page.'); ?>
				</span>
			</div>
			<hr>
			<div class="backup_settings" style="margin: 20px auto;">
				<h3><?php echo KT_I18N::translate('Settings'); ?></h3>
				<form method="post">
					<input type="hidden" name="action" value="update">
					<div class="config_options">
						<label><?php echo /* I18N: Dropbox App folder name */ KT_I18N::translate('Dropbox App folder'); ?></label>
						<div class="input_group">
							<input type="text" name="NEW_DB_FOLDER" value="<?php echo $DB_FOLDER; ?>" required autocomplete="off">
						</div>
					</div>
					<div class="config_options">
						<label><?php echo /* I18N: Dropbox App secure access token */ KT_I18N::translate('Dropbox App token'); ?></label>
						<div class="input_group">
							<input type="text" name="NEW_DB_TOKEN" value="<?php echo $DB_TOKEN; ?>" pattern=".{56,}"   required title="<?php echo KT_I18N::translate('Not a valid Dropbox token'); ?>" autocomplete="off">
						</div>
					</div>
					<?php if ($DB_FOLDER) { ?>
						<div class="config_options">
							<label><?php echo KT_I18N::translate('These files and folders are always excluded from the backup.'); ?></label>
							<div class="input_group">
								<?php
								$html = '';
								foreach ($this->ignoreList() as $file) {
									$html .= $file . ',';
								}
								$html = rtrim($html, ",");
								?>
								<input type="text" value="<?php echo $html; ?>">
							</div>
						</div>
						<div class="config_options">
							<label><?php echo KT_I18N::translate('Also exclude these files and folders'); ?></label>
							<div class="input_group">
								<input type="text" name="NEW_DB_EXCLUDE" value="<?php echo $db_exclude; ?>">
								<span class="help_content">
									<?php echo KT_I18N::translate('Separate each item by a comma'); ?>
								</span>
							</div>
						</div>
						<div class="config_options">
							<label><?php echo KT_I18N::translate('Date of last backup'); ?></label>
							<div class="input_group">
								<input type="text" value="<?php echo $DB_DATE; ?>">
							</div>
						</div>
					<?php } ?>
					<button class="btn btn-primary update" type="submit">
						<i class="fa fa-save"></i>
						<?php echo KT_I18N::translate('update'); ?>
					</button>
				</form>
			</div>
			<hr class="clearfloat">
			<?php
			if ($DB_TOKEN && $DB_FOLDER) { ?>
				<style>
					td, th {padding: 5px 10px;}
				</style>
				<div id="backup_list" style="margin: 20px; float: left;">
					<h3><?php echo KT_I18N::translate('These are the files and folders that will be sent to Dropbox.'); ?></h3>
					<h4><?php echo /* I18N: Explanation of files included in backup to Dropbox */ KT_I18N::translate('Where a folder is shown, the entire contents will be sent.'); ?></h4>
					<ul style="list-style:none;">
						<table>
							<tr>
								<th><?php echo KT_I18N::translate('File or folder name'); ?></th>
								<th><?php echo KT_I18N::translate('Date last modified'); ?></th>
							</tr>
							<?php foreach ($iterator as $pathname => $fileInfo) {
								$file	= str_replace(KT_DATA_DIR, "", $fileInfo);
								$file	= str_replace("\\", "/", $file);
								$facts	= preg_split('/\//', $file);
								if (count($facts) < 2) { ?>
									<tr>
										<td>
											<i class="fa <?php echo (is_dir($fileInfo) ? 'fa-folder-open-o' : 'fa fa-file-o'); ?>"></i>
											<?php echo $fileInfo->getFilename(); ?>
										</td>
										<td>
											<?php if (!is_dir($fileInfo)){
												echo date ("F d Y H:i:s.", filemtime($fileInfo));
											} ?>
										</td>
									</tr>
								<?php }
							} ?>
						</table>
					</ul>
				</div>
				<?php if(KT_Filter::post('action') == 'backup') { ?>
					<div id="results_list" style="margin: 20px 50px; float: right;">
						<h3><?php echo KT_I18N::translate('Files uploaded to Dropbox'); ?></h3>
						<h4></h4>
						<table id="dropbox" style="margin-top: 40px;">
							<tr>
								<th><?php echo KT_I18N::translate('File name'); ?></th>
								<th><?php echo KT_I18N::translate('Size'); ?></th>
								<th><?php echo KT_I18N::translate('Last modified date'); ?></th>
							</tr>
							<?php
								$dir = str_replace(KT_ROOT, "", KT_DATA_DIR);
								$this->upload($dir, $db_exclude, $DB_TOKEN);
							?>
						</table>
					</div>
				<?php } ?>
				<form  class="clearfloat" method="post" action="<?php echo $this->getConfigLink(); ?>">
					<input type="hidden" name="action" value="backup">
					<input type="hidden" name="NEW_DB_DATE" value="<?php echo isset($response['error_summary']) ? $DB_DATE : date('Y/m/d H:i:s'); ?>">
					<button class="btn btn-primary delete" type="submit">
						<i class="fa fa-dropbox"></i>
						<?php echo KT_I18N::translate('backup'); ?>
					</button>
				</form>
				<div class="clearfloat" style="font-size: 90%; font-style: italic;">
					<?php echo /* I18N: Dropbox copyright statement */ KT_I18N::translate('Dropbox and the Dropbox logo are trademarks of Dropbox, Inc.'); ?>
				</div>
			<?php } ?>
		</div>
	<?php }

	/**
     * upload set the file or directory to upload
     * @param  [type] $dirtocopy [description]
     * @return [type]            [description]
     */
    public function upload($dirtocopy, $db_exclude, $db_token){
		$DB_EXCLUDE = explode(',', $db_exclude);

        if(!file_exists($dirtocopy)){
            exit("File $dirtocopy does not exist");
        } else {
            //if dealing with a file upload it
            if(is_file($dirtocopy)){
                $this->uploadFile($dirtocopy, $db_token);
            } else { //otherwise collect all files and folders
				$exclude	= array_merge($this->ignoreList(), $DB_EXCLUDE);
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
                //loop through all entries
				foreach ($iterator as $pathname => $fileInfo) {
					$file = str_replace(KT_DATA_DIR, "", $fileInfo);
					if (is_file($file)) {
						$this->uploadFile($file, $db_token);
					}
				}
            }
        }
	}

	/**
     * uploadFile upload file to dropbox using the Dropbox API
     * @param  string $file path to file
    */
    public function uploadFile($file, $db_token){
        $path		= $file;
        $fp			= fopen($path, 'rb');
        $filesize	= filesize($path);

		$api_url	= 'https://content.dropboxapi.com/2/files/upload'; //dropbox api url
        $headers	= array(
			'Authorization: Bearer '. $db_token,
            'Content-Type: application/octet-stream',
            'Dropbox-API-Arg: '.
            json_encode(
                array(
                    "path"			=> '/'. $file,
                    "mode"			=> "overwrite",
                    "autorename"	=> false,
                    "mute"			=> false
                )
            )

        );

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, fread($fp, $filesize));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response	= json_decode(curl_exec($ch), true);
        $http_code	= curl_getinfo($ch, CURLINFO_HTTP_CODE);
		fclose($fp);

		if (isset($response['error_summary'])) {
			echo '
				<tr>
					<td colspan="3">' . /* I18N Dropbox error message */ KT_I18N::translate('No files uploaded') . '</td>
				</tr>
				<tr>
					<td colspan="3">' . /* I18N Dropbox error message */ KT_I18N::translate('Dropbox error message: <span class="error">%s</span>', $response['error_summary']) . '</td>
				</tr>
			';
		} else {
			echo '
				<tr>
					<td>' . $response['name'] . '</td>
					<td>' . $response['size'] . '</td>
					<td>' . $response['client_modified'] . '</td>
				</tr>
			';
		}

		curl_close($ch);

    }

	/**
     * ignoreList array of filenames or directories to ignore
     * @return array
    */
    public function ignoreList(){
        return array(
            '.gitignore',
			'.DS_Store',
            '.htaccess',
            'config.ini.php',
            'cache',
			'index.php'
        );
    }



}
