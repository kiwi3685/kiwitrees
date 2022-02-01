<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net
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

define('KT_SCRIPT_NAME', 'admin_media_upload.php');
require './includes/session.php';
require_once KT_ROOT.'includes/functions/functions_mediadb.php';

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->requireEditorLogin() /* Editing may be disabled, even for admins */
	->setPageTitle(KT_I18N::translate('Upload media files'));

$action = KT_Filter::post('action');

if ($action == "upload") {
	for ($i = 1; $i < 6; $i ++) {
		if (!empty($_FILES['mediafile' . $i]["name"]) || !empty($_FILES['thumbnail' . $i]["name"])) {
			$folder = KT_Filter::post('folder' . $i, KT_REGEX_UNSAFE);

			// Validate the media folder
			$folderName = str_replace('\\', '/', $folder);
			$folderName = trim($folderName, '/');
			if ($folderName == '.') {
				$folderName = '';
			}
			if ($folderName) {
				$folderName .= '/';
				// Not allowed to use “../”
				if (strpos('/' . $folderName, '/../')!==false) {
					KT_FlashMessages::addMessage('Folder names are not allowed to include “../”');
					break;
				}
			}

			// Make sure the media folder exists
			if (!is_dir(KT_DATA_DIR . $MEDIA_DIRECTORY)) {
				if (@mkdir(KT_DATA_DIR . $MEDIA_DIRECTORY, KT_PERM_EXE, true)) {
					KT_FlashMessages::addMessage(KT_I18N::translate('The folder %s was created.', '<span class="filename">' . KT_DATA_DIR . $MEDIA_DIRECTORY . '</span>'));
				} else {
					KT_FlashMessages::addMessage(KT_I18N::translate('The folder %s does not exist, and it could not be created.', '<span class="filename">' . KT_DATA_DIR . $MEDIA_DIRECTORY . '</span>'));
					break;
				}
			}

			// Managers can create new media paths (subfolders).  Users must use existing folders.
			if ($folderName && !is_dir(KT_DATA_DIR . $MEDIA_DIRECTORY . $folderName)) {
				if (KT_USER_GEDCOM_ADMIN) {
					if (@mkdir(KT_DATA_DIR . $MEDIA_DIRECTORY . $folderName, KT_PERM_EXE, true)) {
						KT_FlashMessages::addMessage(KT_I18N::translate('The folder %s was created.', '<span class="filename">' . KT_DATA_DIR . $MEDIA_DIRECTORY . $folderName . '</span>'));
					} else {
						KT_FlashMessages::addMessage(KT_I18N::translate('The folder %s does not exist, and it could not be created.', '<span class="filename">' . KT_DATA_DIR . $MEDIA_DIRECTORY . $folderName . '</span>'));
						break;
					}
				} else {
					// Regular users should not have seen this option - so no need for an error message.
					break;
				}
			}

			// The media folder exists.  Now create a thumbnail folder to match it.
			if (!is_dir(KT_DATA_DIR . $MEDIA_DIRECTORY . 'thumbs/' . $folderName)) {
				if (!@mkdir(KT_DATA_DIR . $MEDIA_DIRECTORY . 'thumbs/' . $folderName, KT_PERM_EXE, true)) {
					KT_FlashMessages::addMessage(KT_I18N::translate('The folder %s does not exist, and it could not be created.', '<span class="filename">' . KT_DATA_DIR . $MEDIA_DIRECTORY . 'thumbs/' . $folderName . '</span>'));
					break;
				}
			}

			// A thumbnail file with no main image?
			if (!empty($_FILES['thumbnail' . $i]['name']) && empty($_FILES['mediafile' . $i]['name'])) {
				// Assume the user used the wrong field, and treat this as a main image
				$_FILES['mediafile' . $i] = $_FILES['thumbnail' . $i];
				unset($_FILES['thumbnail' . $i]);
			}

			// Check for image having 0 bytes (corrupted)  or too large to import
			if ($_FILES['mediafile' . $i]['size'] && ($_FILES['mediafile' . $i]['size'] === 0 || $_FILES['mediafile' . $i]['size'] > int_from_bytestring(detectMaxUploadFileSize()))) {
				KT_FlashMessages::addMessage(KT_I18N::translate('The media file you selected either has a size of zero bytes or is too large to be uploaded.'));
				unset($_FILES['mediafile' . $i]);
				break;
			}

			// Thumbnails must be images.
			if (!empty($_FILES['thumbnail' . $i]['name']) && !preg_match('/^image\/(png|gif|jpeg)/', $_FILES['thumbnail' . $i]['type'])) {
				KT_FlashMessages::addMessage(KT_I18N::translate('Thumbnails must be images.'));
				break;
			}

			// User-specified filename?
			$filename = KT_Filter::post('filename' . $i, KT_REGEX_UNSAFE);
			// Use the name of the uploaded file?
			if (!$filename && !empty($_FILES['mediafile' . $i]['name'])) {
				$filename = $_FILES['mediafile' . $i]['name'];
			}

			// Validate the media path and filename
			if (preg_match('/([\/\\\\<>])/', $filename, $match)) {
				// Local media files cannot contain certain special characters
				KT_FlashMessages::addMessage(KT_I18N::translate('Filenames are not allowed to contain the character “%s”.', $match[1]));
				$filename = '';
				break;
			} elseif (preg_match('/(\.(php|pl|cgi|bash|sh|bat|exe|com|htm|html|shtml))$/i', $filename, $match)) {
				// Do not allow obvious script files.
				KT_FlashMessages::addMessage(KT_I18N::translate('Filenames are not allowed to have the extension “%s”.', $match[1]));
				$filename = '';
				break;
			} elseif (!$filename) {
				KT_FlashMessages::addMessage(KT_I18N::translate('No media file was provided.'));
				break;
			} else {
				$fileName = $filename;
			}

			// Now copy the file to the correct location.
			if (!empty($_FILES['mediafile' . $i]['name'])) {
				$serverFileName = KT_DATA_DIR . $MEDIA_DIRECTORY . $folderName . $fileName;
				if (file_exists($serverFileName)) {
					KT_FlashMessages::addMessage(KT_I18N::translate('The file %s already exists.  Use another filename.', $folderName . $fileName));
					$filename = '';
					break;
				}
				if (move_uploaded_file($_FILES['mediafile' . $i]['tmp_name'], $serverFileName)) {
					KT_FlashMessages::addMessage(KT_I18N::translate('The file %s was uploaded.', '<span class="filename">' . $serverFileName . '</span>'));
					chmod($serverFileName, KT_PERM_FILE);
					AddToLog('Media file ' . $serverFileName . ' uploaded', 'media');
				} else {
					KT_FlashMessages::addMessage(
						KT_I18N::translate('There was an error uploading your file.') .
						'<br>' .
						file_upload_error_text($_FILES['mediafile' . $i]['error'])
					);
					$filename = '';
					break;
				}

				// Now copy the (optional thumbnail)
				if (!empty($_FILES['thumbnail' . $i]['name']) && preg_match('/^image\/(png|gif|jpeg)/', $_FILES['thumbnail' . $i]['type'], $match)) {
					$extension = $match[1];
					$thumbFile = preg_replace('/\.[a-z0-9]{3,5}$/', '.' . $extension, $fileName);
					$serverFileName = KT_DATA_DIR . $MEDIA_DIRECTORY . 'thumbs/' . $folderName .  $thumbFile;
					if (move_uploaded_file($_FILES['thumbnail' . $i]['tmp_name'], $serverFileName)) {
					KT_FlashMessages::addMessage(KT_I18N::translate('The file %s was uploaded.', '<span class="filename">' . $serverFileName . '</span>'));
						chmod($serverFileName, KT_PERM_FILE);
						AddToLog('Thumbnail file ' . $serverFileName . ' uploaded', 'media');
					}
				}
			}
		}
	}
}

$controller->pageHeader();

$mediaFolders = KT_Query_Media::folderListAll();

// Determine file size limit
$filesize = detectMaxUploadFileSize();
if (empty($filesize)) $filesize = "2M";

// Print the form
echo '<form name="uploadmedia" enctype="multipart/form-data" method="post" action="', KT_SCRIPT_NAME, '">';
echo '<input type="hidden" name="action" value="upload">';
echo '<p>', KT_I18N::translate('Upload media files'), ':&nbsp;&nbsp;<span class="accepted">' , KT_I18N::translate('Maximum file size allowed is %s', detectMaxUploadFileSize()) , '</span></p>';

// Print 5 forms for uploading images
for ($i=1; $i<6; $i++) {
	echo '<table class="upload_media">';
	echo '<tr><th>', KT_I18N::translate('Media file'), ':&nbsp;&nbsp;', $i, '</th></tr>';
	echo '<tr><td>';
	echo KT_I18N::translate('Media file to upload'), help_link('upload_media_file');
	echo '</td>';
	echo '<td>';
	echo '<input name="mediafile', $i, '" type="file" size="40">';
	echo '</td></tr>';
	echo '<tr><td>';
	echo KT_I18N::translate('Thumbnail to upload'), help_link('upload_thumbnail_file');
	echo '</td>';
	echo '<td>';
	echo '<input name="thumbnail', $i, '" type="file" size="40">';
	echo '</td></tr>';

	if (KT_USER_GEDCOM_ADMIN) {
		echo '<tr><td>';
		echo KT_I18N::translate('File name on server'), help_link('upload_server_file');
		echo '</td>';
		echo '<td>';
		echo '<input name="filename', $i, '" type="text" size="40">';
		if ($i==1) echo "<sub>", KT_I18N::translate('Do not change to keep original file name.'), "</sub>";
		echo '</td></tr>';
	} else {
		echo '<tr style="display:none;"><td><input type="hidden" name="filename', $i, '" value=""></td></tr>';
	}

	if (KT_USER_GEDCOM_ADMIN) {
		echo '<tr><td>';
		echo KT_I18N::translate('Folder name on server'), help_link('upload_server_folder');
		echo '</td>';
		echo '<td>';

		echo '<span dir="ltr"><select name="folder_list', $i, '" onchange="document.uploadmedia.folder', $i, '.value=this.options[this.selectedIndex].value;">';
		echo '<option';
		echo ' value="/"> ', KT_I18N::translate('Choose: '), ' </option>';
		if (KT_USER_IS_ADMIN) echo '<option value="other" disabled>', KT_I18N::translate('Other folder... please type in'), "</option>";
		foreach ($mediaFolders as $f) {
			echo '<option value="', htmlspecialchars($f), '">', htmlspecialchars($f), "</option>";
		}
		echo "</select></span>";
		if (KT_USER_IS_ADMIN) {
			echo '<br><span dir="ltr"><input name="folder', $i, '" type="text" size="40" value=""></span>';
		} else {
			echo '<input name="folder', $i, '" type="hidden" value="">';
		}
		echo '</td></tr>';
	} else {
		echo '<tr style="display:none;"><td><input name="folder', $i, '" type="hidden" value=""></td></tr>';
	}
	echo '</table>';
}
// Print the Submit button for uploading the media
echo '<input type="submit" value="', KT_I18N::translate('Upload'), '">';
echo '</form>';
