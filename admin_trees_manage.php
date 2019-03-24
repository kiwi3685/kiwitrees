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

define('KT_SCRIPT_NAME', 'admin_trees_manage.php');
require './includes/session.php';
require KT_ROOT . 'includes/functions/functions_edit.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('Manage family trees'));

// Don’t allow the user to cancel the request.  We do not want to be left
// with an incomplete transaction.
ignore_user_abort(true);

// $path is the full path to the (possibly temporary) file.
// $filename is the actual filename (no folder).
function import_gedcom_file($gedcom_id, $path, $filename) {
	// Read the file in blocks of roughly 64K.  Ensure that each block
	// contains complete gedcom records.  This will ensure we don’t split
	// multi-byte characters, as well as simplifying the code to import
	// each block.

	$file_data	= '';
	$fp			= fopen($path, 'rb');

	KT_DB::exec("START TRANSACTION");
	KT_DB::prepare("DELETE FROM `##gedcom_chunk` WHERE gedcom_id=?")->execute(array($gedcom_id));

	while (!feof($fp)) {
		$file_data .= fread($fp, 65536);
		// There is no strrpos() function that searches for substrings :-(
		for ($pos=strlen($file_data)-1; $pos>0; --$pos) {
			if ($file_data[$pos]=='0' && ($file_data[$pos-1]=="\n" || $file_data[$pos-1]=="\r")) {
				// We’ve found the last record boundary in this chunk of data
				break;
			}
		}
		if ($pos) {
			KT_DB::prepare(
				"INSERT INTO `##gedcom_chunk` (gedcom_id, chunk_data) VALUES (?, ?)"
			)->execute(array($gedcom_id, substr($file_data, 0, $pos)));
			$file_data=substr($file_data, $pos);
		}
	}
	KT_DB::prepare(
		"INSERT INTO `##gedcom_chunk` (gedcom_id, chunk_data) VALUES (?, ?)"
	)->execute(array($gedcom_id, $file_data));

	set_gedcom_setting($gedcom_id, 'gedcom_filename', $filename);
	KT_DB::exec("COMMIT");
	fclose($fp);
}


$default_tree_title  = /* I18N: Default name for a new tree */ KT_I18N::translate('My family tree');
$default_tree_name   = 'tree';
$default_tree_number = 1;
$existing_trees      = KT_Tree::getNameList();
while (array_key_exists($default_tree_name . $default_tree_number, $existing_trees)) {
	$default_tree_number++;
}
$default_tree_name .= $default_tree_number;

// Process POST actions
switch (KT_Filter::post('action')) {
case 'delete':
	$gedcom_id = KT_Filter::postInteger('gedcom_id');
	if (KT_Filter::checkCsrf() && $gedcom_id) {
		KT_Tree::delete($gedcom_id);
	}
	header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME);
	break;
case 'setdefault':
	if (KT_Filter::checkCsrf()) {
		KT_Site::preference('DEFAULT_GEDCOM', KT_Filter::post('default_ged'));
	}
	break;
case 'new_tree':
	$ged_name		= basename(KT_Filter::post('ged_name'));
	$gedcom_title	= KT_Filter::post('gedcom_title');
	if (KT_Filter::checkCsrf() && $ged_name && $gedcom_title) {
		KT_Tree::create($ged_name, $gedcom_title);
	}
	break;
case 'replace_upload':
	KT_FlashMessages::addMessage('Starting upload');
	$gedcom_id			= KT_Filter::postInteger('gedcom_id');
	$keep_media         = KT_Filter::post('keep_media', '1', '0');
	$GEDCOM_MEDIA_PATH  = KT_Filter::post('GEDCOM_MEDIA_PATH');
	$WORD_WRAPPED_NOTES = KT_Filter::post('WORD_WRAPPED_NOTES', '1', '0');
	// Make sure the gedcom still exists
	if (KT_Filter::checkCsrf() && get_gedcom_from_id($gedcom_id)) {
		set_gedcom_setting(KT_GED_ID, 'keep_media', $keep_media);
		set_gedcom_setting(KT_GED_ID, 'GEDCOM_MEDIA_PATH', $GEDCOM_MEDIA_PATH);
		set_gedcom_setting(KT_GED_ID, 'WORD_WRAPPED_NOTES', $WORD_WRAPPED_NOTES);
		foreach ($_FILES as $FILE) {
			if ($FILE['error'] == 0 && is_readable($FILE['tmp_name'])) {

				$filename	= $FILE['name'];
				$source		= $FILE['tmp_name'];
				$type		= $FILE['type'];
				$name		= explode(".", $filename);
				$ext		= strtolower($name[1]);
				$ged_name	= strtolower($name[0]) . '.ged';

				if ($ext == 'zip') {
					//check for valid zip file
					$accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed');
					foreach($accepted_types as $mime_type) {
						if($mime_type == $type) {
							$okay = true;
							break;
						}
					}

					$target_path = KT_DATA_DIR . $filename;
					if(move_uploaded_file($source, $target_path)) {
						$zip = new ZipArchive();
						$x = $zip->open($target_path);
						if ($x === true) {
							$zip->extractTo(KT_DATA_DIR);
							$zip->close();
							unlink($target_path);
						}
					}
					// import the unzipped file
					import_gedcom_file($gedcom_id, KT_DATA_DIR . $ged_name, $ged_name);

				} else {
					// import as a ged file
					import_gedcom_file($gedcom_id, $FILE['tmp_name'], $FILE['name']);
				}
			}
		}
	}
	header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME);
	exit;
case 'replace_import':
	$gedcom_id			= KT_Filter::postInteger('gedcom_id');
	$keep_media         = KT_Filter::post('keep_media', '1', '0');
	$GEDCOM_MEDIA_PATH  = KT_Filter::post('GEDCOM_MEDIA_PATH');
	$WORD_WRAPPED_NOTES = KT_Filter::post('WORD_WRAPPED_NOTES', '1', '0');
	// Make sure the gedcom still exists
	if (KT_Filter::checkCsrf() && get_gedcom_from_id($gedcom_id)) {
		$ged_name = basename(KT_Filter::post('ged_name'));
		import_gedcom_file($gedcom_id, KT_DATA_DIR . $ged_name, $ged_name);
		set_gedcom_setting(KT_GED_ID, 'keep_media', $keep_media);
		set_gedcom_setting(KT_GED_ID, 'GEDCOM_MEDIA_PATH', $GEDCOM_MEDIA_PATH);
		set_gedcom_setting(KT_GED_ID, 'WORD_WRAPPED_NOTES', $WORD_WRAPPED_NOTES);
	}
	header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME);
	exit;
}

$controller->pageHeader();

// Process GET actions
switch (KT_Filter::get('action')) {
case 'importform':
	$gedcom_id	 = KT_Filter::get('gedcom_id');
	$gedcom_name = get_gedcom_from_id($gedcom_id);
	// Check it exists
	if (!$gedcom_name) {
		break;
	}
	echo '
	<div id="trees_import">
		<h2>' . $tree->tree_title_html . ' — ' . KT_I18N::translate('Import a GEDCOM file') . '</h2>
		<h4 class="accepted">' . /* I18N: %s is the name of a family tree */ KT_I18N::translate('This will delete all the genealogy data from “%s” and replace it with data from a GEDCOM file.', $tree->tree_title_html) . '</h4>';
		// the javascript in the next line strips any path associated with the file before comparing it to the current GEDCOM name (both Chrome and IE8 include c:\fakepath\ in the filename).
		$previous_gedcom_filename = get_gedcom_setting($gedcom_id, 'gedcom_filename');
		$old_file = KT_Filter::escapeHtml($previous_gedcom_filename);
		echo '
		<form
			name="replaceform"
			method="post"
			enctype="multipart/form-data"
			action="' . KT_SCRIPT_NAME . '"
			onsubmit="
				var newfile = document.replaceform.ged_name.value;
				newfile = newfile.substr(newfile.lastIndexOf(\'\\\\\')+1);
				if (newfile!=\'' . $old_file . '\' && \'' . $old_file . '\' != \'\')
				return confirm(newfile\'' . KT_Filter::escapeHtml(KT_I18N::translate('You have selected a GEDCOM with a different name.  Is this correct?')) . '\');
				else return true;"
		>
			<input type="hidden" name="gedcom_id" value="' . $gedcom_id . '">' .
			KT_Filter::getCsrf() . '
			<h3>' . KT_I18N::translate('Select a GEDCOM file to import') . '</h3>
			<div class="tree_import">
				<label>' . KT_I18N::translate('A file on your computer') . '</label>
				<div class="input">
					<input type="radio" name="action" id="import-computer" value="replace_upload" checked>
					<span class="input_addon">
						<input type="file" name="ged_name" id="import-computer-file">
					</span>
					<div class="help_content">' .
							KT_I18N::translate('Maximum file size allowed is %s', detectMaxUploadFileSize()) . '
							<br>' .
							KT_I18N::translate('You can upload a zip file containing your GEDCOM file. The zip folder must have the same name as your GEDCOM file.') . '
					</div>
				</div>
			</div>
			<div class="tree_import">
				<label>' . KT_I18N::translate('A file on the server') . '</label>
				<div class="input">
					<input type="radio" name="action" id="import-server" value="replace_import">
					<span class="input_addon">' . KT_DATA_DIR;
						$d = opendir(KT_DATA_DIR);
						$files = array();
						while (($f = readdir($d)) !== false) {
							if (!is_dir(KT_DATA_DIR . $f) && is_readable(KT_DATA_DIR.$f)) {
								$fp		= fopen(KT_DATA_DIR . $f, 'rb');
								$header	= fread($fp, 64);
								fclose($fp);
								if (preg_match('/^(' . KT_UTF8_BOM . ')?0 *HEAD/', $header)) {
									$files[] = $f;
								}
							}
						}
						sort($files);
						echo '<select name="ged_name">';
							foreach ($files as $file) {
								echo '<option value="' . htmlspecialchars($file) . '"';
								if ($file == $previous_gedcom_filename) {
									echo ' selected="selected"';
								}
								echo'>' . htmlspecialchars($file) . '</option>';
							}
							if (!$files) {
								echo '<option disabled selected>' . KT_I18N::translate('No GEDCOM files found.') . '</option>';
							}
						echo '</select>
					</span>
				</div>
			</div>
			<hr>
			<h3>' . KT_I18N::translate('Import options') . '</h3>
			<div class="tree_import">
				<label>' . KT_I18N::translate('Keep media objects') . '</label>
				<div class="input">
					<input type="checkbox" name="keep_media" value="1" ';
					 	if (get_gedcom_setting(KT_GED_ID, 'keep_media') == '1') {
							echo 'checked';
						}
					echo '>
					<div class="help_content">' .
						KT_I18N::translate('If you have created media objects in kiwitrees, and edited your gedcom off-line using a program that deletes media objects, then check this box to merge the current media objects with the new GEDCOM. <p style="color:red">Otherwise, ensure this box is NOT checked</p>') . '
					</div>
				</div>
			</div>
			<div class="tree_import">
				<label>' . KT_I18N::translate('Add spaces where notes were wrapped') . '</label>
				<div class="input">' .
					edit_field_yes_no('WORD_WRAPPED_NOTES', get_gedcom_setting(KT_GED_ID, 'WORD_WRAPPED_NOTES')) . '
					<div class="help_content">' .
						KT_I18N::translate('Some genealogy programs wrap notes at word boundaries while others wrap notes anywhere.  This can cause kiwitrees to run words together.  Setting this to <b>Yes</b> will add a space between words where they are wrapped in the original GEDCOM during the import process. If you have already imported the file you will need to re-import it.') . '
					</div>
				</div>
			</div>
			<div class="tree_import">
				<label>' . /* I18N: A media path (e.g. c:\aaa\bbb\ccc\ddd.jpeg) in a GEDCOM file */ KT_I18N::translate('Remove the GEDCOM media path from filenames') . '</label>
				<div class="input">
					<input type="text" name="GEDCOM_MEDIA_PATH" value="' . $GEDCOM_MEDIA_PATH . '" dir="ltr" maxlength="255">
					<div class="help_content">' .
						// I18N: A “path” is something like “C:\Documents\My_User\Genealogy\Photos\Gravestones\John_Smith.jpeg”
						KT_I18N::translate('Some genealogy applications create GEDCOM files that contain media filenames with full paths.  These paths will not exist on the web-server.  To allow kiwitrees to find the file, the first part of the path must be removed.').
						// I18N: %s are all folder names; “GEDCOM media path” is a configuration setting
						KT_I18N::translate('For example, if the GEDCOM file contains %1$s and kiwitrees expects to find %2$s in the media folder, then the GEDCOM media path would be %3$s.', '<span class="accepted">/home/familytree/documents/family/photo.jpeg</span>', '<span class="accepted">family/photo.jpeg</span>', '<span class="accepted">/home/familytree/documents/</span>').
						KT_I18N::translate('This setting is only used when you read or write GEDCOM files.') .'
					</div>
				</div>
			</div>
			<p>
				<button class="btn btn-primary" type="submit">
				<i class="fa fa-play"></i>' .
					KT_I18N::translate('continue') . '
				</button>
			</p>
		</form>
	</div>';
	return;
}

echo '
	<a class="current faq_link" href="http://kiwitrees.net/faqs/introduction/" target="_blank" rel="noopener noreferrer" title="' . KT_I18N::translate('View FAQ for this page.') . '">' . KT_I18N::translate('View FAQ for this page.'). '<i class="fa fa-comments-o"></i></a>
	<h2>' . $controller->getPageTitle() . '</h2>
	';

// List the gedcoms available to this user
foreach (KT_Tree::GetAll() as $tree) {
	if (userGedcomAdmin(KT_USER_ID, $tree->tree_id)) {
		echo '
			<table class="gedcom_table">
				<tr>
					<th>' . KT_I18N::translate('Family tree') . '</th>
					<th>
						<a class="accepted" href="index.php?ged=' . $tree->tree_name_url . '" dir="auto">' . $tree->tree_title_html . '</a>
						<a href="admin_trees_config.php?ged=' . $tree->tree_name_html . '">
							<i class="fa fa-cog"></i>
						</a>
					</th>
				</tr>
				<tr>
					<th class="accepted">' . $tree->tree_name_html . '</th>
					<td>';
						// The third row shows an optional progress bar and a list of maintenance options
						$importing = KT_DB::prepare(
							"SELECT 1 FROM `##gedcom_chunk` WHERE gedcom_id=? AND imported=0 LIMIT 1"
						)->execute(array($tree->tree_id))->fetchOne();
						if ($importing) {
							$in_progress = KT_DB::prepare(
								"SELECT 1 FROM `##gedcom_chunk` WHERE gedcom_id=? AND imported=1 LIMIT 1"
							)->execute(array($tree->tree_id))->fetchOne();
							if (!$in_progress) {
								echo '<div id="import' . $tree->tree_id . '"><div id="progressbar' . $tree->tree_id . '"><div style="position:absolute;" class="error">' . KT_I18N::translate('Deleting old genealogy data…') . '</div></div></div>';
							$controller->addInlineJavascript(
								'jQuery("#progressbar'.$tree->tree_id.'").progressbar({value: 0});'
							);
							} else {
								echo '<div id="import' . $tree->tree_id . '"></div>';
							}
							$controller->addInlineJavascript(
								'jQuery("#import' . $tree->getTreeId() . '").load("import.php?gedcom_id=' . $tree->tree_id . '");'
							);
							echo '<table border="0" width="100%" id="actions' . $tree->tree_id . '" style="display:none">';
						} else {
							echo '<table border="0" width="100%" id="actions' . $tree->tree_id . '">';
						}
							echo '<tr align="center">' .
								// import
								'<td>
									<a href="' . KT_SCRIPT_NAME . '?action=importform&amp;gedcom_id=' . $tree->tree_id . '">' .
										KT_I18N::translate('Import a GEDCOM file') . '
										<i class="fa fa-upload"></i>
									</a>
								</td>' .
								// download
								'<td>
									<a href="admin_trees_download.php?ged=' . $tree->tree_name_url .'">' .
										KT_I18N::translate('Export a GEDCOM file') . '
										<i class="fa fa-download"></i>
									</a>
								</td>' .
								// delete
								'<td>
									<a href="#" onclick="if (confirm(\''. KT_Filter::escapeJs(KT_I18N::translate('Are you sure you want to delete “%s”?', $tree->tree_name)) .'\')) document.delete_form' . $tree->tree_id . '.submit(); return false;">' .
										KT_I18N::translate('Delete this family tree') . '
										<i class="fa fa-trash"></i>
									</a>
									<form name="delete_form' . $tree->tree_id  .'" method="post" action="' . KT_SCRIPT_NAME  .'">
										<input type="hidden" name="action" value="delete">
										<input type="hidden" name="gedcom_id" value="' . $tree->tree_id . '">' .
										KT_Filter::getCsrf() . '
									</form>' .
								'</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		<br><hr>';
	}
} ?>

<?php // Options for creating new gedcoms and setting defaults
if (KT_USER_IS_ADMIN) {
	if (count(KT_Tree::GetAll())>1) { ?>
		<div class="gedcom_table2">
			<form name="defaultform" method="post" action="<?php echo KT_SCRIPT_NAME; ?>">
				<?php echo KT_Filter::getCsrf(); ?>
				<label>
					<?php echo KT_I18N::translate('Default family tree'); ?>
				</label>
				<input type="hidden" name="action" value="setdefault">
				<?php echo select_edit_control('default_ged', KT_Tree::getNameList(), '', KT_Site::preference('DEFAULT_GEDCOM'), 'onchange="document.defaultform.submit();"'); ?>
				<span class="help-text">
					<?php echo KT_I18N::translate('This selects the family tree shown to visitors when they first arrive at the site.'); ?>
				</span>
				<div class="input-group">
					<button class="btn btn-primary" type="submit">
					<i class="fa fa-floppy-o"></i>
						<?php echo KT_I18N::translate('Save'); ?>
					</button>
				</div>
			</form>
		</div>
		<hr class="clearfloat">
	<?php } ?>
	<div class="gedcom_table3">
		<h3><?php echo KT_I18N::translate('Create a new family tree'); ?></h3>
		<?php if (!KT_Tree::GetAll()) { ?>
			<p class="warning">
				<?php echo KT_I18N::translate('You need to create a family tree.'); ?>
			</p>
		<?php } ?>
		<form name="createform" method="post" action="<?php echo KT_SCRIPT_NAME; ?>">
			<?php echo KT_Filter::getCsrf(); ?>
			<label for="gedcom_title">
				<?php echo KT_I18N::translate('Family tree title'); ?>
			</label>
			<input
				type="text"
				id="gedcom_title"
				name="gedcom_title"
				dir="ltr"
				value=""
				size="50"
				maxlength="255"
				required
				placeholder="<?php echo $default_tree_title; ?>"
			>
			<span class="help-text">
				<?php echo KT_I18N::translate('This is the name used for display.'); ?>
			</span>
			<div class="input-group">
				<label for="new_tree">
					<?php echo KT_I18N::translate('URL'); ?>
				</label>
				<span>
					<?php echo KT_SERVER_NAME . KT_SCRIPT_PATH; ?>?ged=
				</span>
				<input type="hidden" id="new_tree" name="action" value="new_tree">
				<input
					type="text"
					id="ged_name"
					name="ged_name"
					pattern="[^&lt;&gt;&amp;&quot;#^$*?{}()\[\]/\\]*"
					maxlength="31"
					value=""
					placeholder="<?php echo $default_tree_name; ?>"
					required
				>
				<span class="help-text">
					<?php echo KT_I18N::translate('Keep this short and avoid spaces and punctuation. A family name might be a good choice.'); ?>
				</span>
			</div>
			<button class="btn btn-primary" type="submit">
			<i class="fa fa-check"></i>
				<?php echo KT_I18N::translate('create'); ?>
			</button>
			<p class="warning help-text clearfloat">
				<?php echo KT_I18N::translate('After creating the family tree you will be able to upload or import data from a GEDCOM file.'); ?>
			</p>
		</form>
	</div>
<?php }
