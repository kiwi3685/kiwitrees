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

define('WT_SCRIPT_NAME', 'admin_trees_download.php');
require './includes/session.php';
require WT_ROOT.'includes/functions/functions_export.php';

$controller = new WT_Controller_Page();
$controller
	->setPageTitle(WT_I18N::translate('Export a GEDCOM file'))
	->requireManagerLogin();

// Validate user parameters
$action           = safe_GET('action',           'download');
$convert          = safe_GET('convert',          'yes', 'no');
$zip              = safe_GET('zip',              'yes', 'no');
$conv_path        = safe_GET('conv_path',        WT_REGEX_NOSCRIPT);
$privatize_export = safe_GET('privatize_export', array('none', 'visitor', 'user', 'gedadmin'));

if ($action == 'download') {
	$exportOptions = array();
	$exportOptions['privatize'] = $privatize_export;
	$exportOptions['toANSI'] = $convert;
	$exportOptions['path'] = $conv_path;
}

$fileName = WT_GEDCOM;
if ($action == "download" && $zip == "yes") {
	require WT_ROOT.'library/pclzip.lib.php';

	$temppath = WT_Site::preference('INDEX_DIRECTORY') . "tmp/";
	$zipname = "dl" . date("YmdHis") . $fileName . ".zip";
	$zipfile = WT_Site::preference('INDEX_DIRECTORY') . $zipname;
	$gedname = $temppath . $fileName;

	$removeTempDir = false;
	if (!is_dir($temppath)) {
		$res = mkdir($temppath);
		if ($res !== true) {
			echo "Error : Could not create temporary path!";
			exit;
		}
		$removeTempDir = true;
	}
	$gedout = fopen($gedname, "w");
	export_gedcom($GEDCOM, $gedout, $exportOptions);
	fclose($gedout);
	$comment = "Created by ".WT_WEBTREES." ".WT_VERSION_TEXT." on " . date("r") . ".";
	$archive = new PclZip($zipfile);
	$v_list = $archive->create($gedname, PCLZIP_OPT_COMMENT, $comment, PCLZIP_OPT_REMOVE_PATH, $temppath);
	if ($v_list == 0) echo "Error : " . $archive->errorInfo(true);
	else {
		unlink($gedname);
		if ($removeTempDir) rmdir($temppath);
		header('Location: '. WT_SERVER_NAME . WT_SCRIPT_PATH."downloadbackup.php?fname=".$zipname);
		exit;
	}
	exit;
}

if ($action == "download") {
	Zend_Session::writeClose();
	header('Content-Type: text/plain; charset=UTF-8');
	// We could open "php://compress.zlib" to create a .gz file or "php://compress.bzip2" to create a .bz2 file
	$gedout = fopen('php://output', 'w');
	if (strtolower(substr($fileName, -4, 4))!='.ged') {
		$fileName.='.ged';
	}
	header('Content-Disposition: attachment; filename="'.$fileName.'"');
	export_gedcom(WT_GEDCOM, $gedout, $exportOptions);
	fclose($gedout);
	exit;
}

$controller->pageHeader();

?>
<div id="tree-download">
	<h2><?php echo $controller->getPageTitle(); ?> - <?php echo $tree->tree_title_html; ?></h2>
	<form id="tree-export" method="post" action="admin_trees_export.php">
		<?php echo WT_Filter::getCsrf(); ?>
		<input type="hidden" name="ged" value="<?php echo $tree->tree_name_url; ?>">
		<label><?php echo WT_I18N::translate('A file on the server'); ?></label>
		<button id="submit-export" class="btn btn-primary" type="submit" onclick="return modalDialog('admin_trees_export.php?ged=<?php echo $tree->tree_name_url; ?>', '<?php echo WT_I18N::translate('Export'); ?>');">
			<i class="fa fa-play"></i>
			<?php echo WT_I18N::translate('continue'); ?>
		</button>
	</form>
	<hr>
	<form name="convertform" method="get">
		<input type="hidden" name="action" value="download">
		<input type="hidden" name="ged" value="<?php echo WT_GEDCOM; ?>">
		<label><?php echo WT_I18N::translate('A file on your computer'); ?></label>
		<div class="input">
			<dl>
				<dt>
					<?php echo WT_I18N::translate('Zip File(s)'); ?>
				</dt>
				<dd>
					<input type="checkbox" name="zip" value="yes">
				</dd>
				<div class="help_content">
					<?php echo WT_I18N::translate('To reduce the size of the download, you can compress the data into a .ZIP file. You will need to uncompress the .ZIP file before you can use it.'); ?>
				</div>
				<dt>
					<?php echo WT_I18N::translate('Apply privacy settings?'); ?>
				</dt>
				<dd>
					<input type="radio" name="privatize_export" value="none" checked="checked">&nbsp;&nbsp;<?php echo WT_I18N::translate('None'); ?>
					<br>
					<input type="radio" name="privatize_export" value="gedadmin">&nbsp;&nbsp;<?php echo WT_I18N::translate('Manager'); ?>
					<br>
					<input type="radio" name="privatize_export" value="user">&nbsp;&nbsp;<?php echo WT_I18N::translate('Member'); ?>
					<br>
					<input type="radio" name="privatize_export" value="visitor">&nbsp;&nbsp;<?php echo WT_I18N::translate('Visitor'); ?>
				</dd>
				<div class="help_content">
					<?php echo WT_I18N::translate('This option will remove private data from the downloaded GEDCOM file.  The file will be filtered according to the privacy settings that apply to each access level.  Privacy settings are specified on the GEDCOM configuration page.'); ?>
				</div>
				<dt>
					<?php echo WT_I18N::translate('Convert from UTF-8 to ANSI (ISO-8859-1)'); ?>
				</dt>
				<dd>
					<input type="checkbox" name="convert" value="yes">
				</dd>
				<div class="help_content">
					<?php echo WT_I18N::translate('Kiwitrees uses UTF-8 encoding for accented letters, special characters and non-latin scripts. If you want to use this GEDCOM file with genealogy software that does not support UTF-8, then you can create it using ISO-8859-1 encoding.'); ?>
				</div>
				<?php if (get_gedcom_setting(WT_GED_ID, 'GEDCOM_MEDIA_PATH')) { ?>
					<dt>
						<?php echo WT_I18N::translate('Add the GEDCOM media path to filenames'); ?>
					</dt>
					<dd>
						<input type="checkbox" name="conv_path" value="<?php echo WT_Filter::escapeHtml(get_gedcom_setting(WT_GED_ID, 'GEDCOM_MEDIA_PATH')); ?>">
						<span dir="auto"><?php echo WT_Filter::escapeHtml($GEDCOM_MEDIA_PATH); ?></span>
					</dd>
					<div class="help_content">
						<?php echo WT_I18N::translate('Media filenames will be prefixed by %s.', '<code dir="ltr">' . WT_Filter::escapeHtml(get_gedcom_setting(WT_GED_ID, 'GEDCOM_MEDIA_PATH')) . '</code>'); ?>
					</div>
				<?php } ?>
			</dl>
			<p>
				<button class="btn btn-primary clearfloat" type="submit">
					<i class="fa fa-play"></i>
					<?php echo WT_I18N::translate('continue'); ?>
				</button>
			</p>
		</div>
	</form>
</div>
