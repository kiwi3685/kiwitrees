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

define('KT_SCRIPT_NAME', 'addmedia.php');
require './includes/session.php';
require_once KT_ROOT . 'includes/functions/functions_print_lists.php';
require KT_ROOT . 'includes/functions/functions_edit.php';

$pid			= KT_Filter::get('pid', KT_REGEX_XREF, KT_Filter::post('pid', KT_REGEX_XREF)); // edit this media object
$linktoid		= KT_Filter::get('linktoid', KT_REGEX_XREF, KT_Filter::post('linktoid', KT_REGEX_XREF)); // create a new media object, linked to this record
$action			= KT_Filter::get('action', null, KT_Filter::post('action'));
$type			= KT_Filter::get('type');
$filename		= KT_Filter::get('filename', null, KT_Filter::post('filename'));
$text			= KT_Filter::postArray('text');
$tag			= KT_Filter::postArray('tag', KT_REGEX_TAG);
$islink			= KT_Filter::postArray('islink');
$glevels		= KT_Filter::postArray('glevels', '[0-9]');
$folder			= KT_Filter::post('folder');
$update_CHAN	= !KT_Filter::postBool('preserve_last_changed');

$controller = new KT_Controller_Page();
$controller
	->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
	->addInlineJavascript('
		autocomplete();
		display_help();
	')
	->requireMemberLogin();

$disp = true;
$media = KT_Media::getInstance($pid);
if ($media) {
	$disp = $media->canDisplayDetails();
}
if ($action == 'update' || $action == 'create') {
	if (!isset($linktoid) || $linktoid == 'new') $linktoid = '';
	if (!empty($linktoid)) {
		$disp = KT_GedcomRecord::getInstance($linktoid)->canDisplayDetails();
	}
}

if (!KT_USER_CAN_EDIT || !$disp) {
	$controller
		->pageHeader()
		->addInlineJavascript('closePopupAndReloadParent();');
	exit;
}

// TODO - there is a lot of common code in the create and update cases....
// .... and also in the admin_media_upload.php script

switch ($action) {
	case 'create': // Save the information from the “showcreateform” action
		$controller->setPageTitle(KT_I18N::translate('Create a new media object'));

		// Validate the media folder
		$folderName = str_replace('\\', '/', $folder);
		$folderName = trim($folderName, '/');
		if ($folderName == '.') {
			$folderName = '';
		}
		if ($folderName) {
			$folderName .= '/';
			// Not allowed to use “../”
			if (strpos('/' . $folderName, '/../') !== false) {
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
		if (!empty($_FILES['thumbnail']['name']) && empty($_FILES['mediafile']['name'])) {
			// Assume the user used the wrong field, and treat this as a main image
			$_FILES['mediafile'] = $_FILES['thumbnail'];
			unset($_FILES['thumbnail']);
		}

		// Thumbnails must be images.
		if (!empty($_FILES['thumbnail']['name']) && !preg_match('/^image/', $_FILES['thumbnail']['type'])) {
			KT_FlashMessages::addMessage(KT_I18N::translate('Thumbnails must be images.'));
			break;
		}

		// User-specified filename?
		if ($tag[0]=='FILE' && $text[0]) {
			$filename = $text[0];
		}
		// Use the name of the uploaded file?
		// If no filename specified, use the name of the uploaded file?
		if (!$filename && !empty($_FILES['mediafile']['name'])) {
			$filename = $_FILES['mediafile']['name'];
		}

		// Validate the media path and filename
		if (preg_match('/^https?:\/\//i', $text[0], $match)) {
			// External media needs no further validation
			$fileName   = $filename;
			$folderName = '';
			unset($_FILES['mediafile'], $_FILES['thumbnail']);
		} elseif (preg_match('/([\/\\\\<>])/', $filename, $match)) {
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
		if (!empty($_FILES['mediafile']['name'])) {
			$serverFileName = KT_DATA_DIR . $MEDIA_DIRECTORY . $folderName . $fileName;
			if (file_exists($serverFileName)) {
				KT_FlashMessages::addMessage(KT_I18N::translate('The file %s already exists.  Use another filename.', $folderName . $fileName));
				$filename = '';
				break;
			}
			if (move_uploaded_file($_FILES['mediafile']['tmp_name'], $serverFileName)) {
				chmod($serverFileName, KT_PERM_FILE);
				AddToLog('Media file ' . $serverFileName . ' uploaded', 'media');
			} else {
				KT_FlashMessages::addMessage(
					KT_I18N::translate('There was an error uploading your file.') .
					'<br>' .
					file_upload_error_text($_FILES['mediafile']['error'])
				);
				$filename = '';
				break;
			}

			// Now copy the (optional) thumbnail
			if (!empty($_FILES['thumbnail']['name']) && preg_match('/^image\/(png|gif|jpeg)/', $_FILES['thumbnail']['type'], $match)) {
				// Thumbnails have either
				// (a) the same filename as the main image
				// (b) the same filename as the main image - but with a .png extension
				if ($match[1]=='png' && !preg_match('/\.(png)$/i', $fileName)) {
					$thumbFile = preg_replace('/\.[a-z0-9]{3,5}$/', '.png', $fileName);
				} else {
					$thumbFile = $fileName;
				}
				$serverFileName = KT_DATA_DIR . $MEDIA_DIRECTORY . 'thumbs/' . $folderName .  $thumbFile;
				if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $serverFileName)) {
					chmod($serverFileName, KT_PERM_FILE);
					AddToLog('Thumbnail file ' . $serverFileName . ' uploaded', 'media');
				}
			}
		}

		$controller->pageHeader();
		// Build the gedcom record
		$media_id = get_new_xref('OBJE');
		if ($media_id) {
			$newged = '0 @' . $media_id . "@ OBJE\n";
			if ($tag[0]=='FILE') {
				// The admin has an edit field to change the file name
				$text[0] = $folderName . $fileName;
			} else {
				// Users keep the original filename
				$newged .= '1 FILE ' . $folderName . $fileName;
			}

			$newged  = handle_updates($newged);

			if (append_gedrec($newged, KT_GED_ID)) {
				if ($linktoid) {
					linkMedia($media_id, $linktoid, 1);
					AddToLog('Media ID ' . $media_id . ' successfully added to ' . $linktoid, 'edit');
					$controller->addInlineJavascript('closePopupAndReloadParent();');
				} else {
					AddToLog('Media ID ' . $media_id . ' successfully added.', 'edit');
					$controller->addInlineJavascript('openerpasteid("' . $media_id . '");');
				}
			}
		}
	//	$controller->addInlineJavascript('closePopupAndReloadParent("");');
	?>
		<div style="margin: 20px 50px">
			<button class="btn btn-primary" type="button"  onclick="closePopupAndReloadParent();">
				<i class="fa fa-times"></i>
				<?php echo KT_I18N::translate('close'); ?>
			</button>
			<p class="warning"><?php echo KT_I18N::translate('Click the close button to return to the manage media page.'); ?></p>
		</div>
	<?php
		exit;

	case 'update': // Save the information from the “editmedia” action
		$controller->setPageTitle(KT_I18N::translate('Edit media object'));

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

		// Validate the media path and filename
		if (preg_match('/^https?:\/\//i', $filename, $match)) {
			// External media needs no further validation
			$fileName   = $filename;
			$folderName = '';
			unset($_FILES['mediafile'], $_FILES['thumbnail']);
		} elseif (preg_match('/([\/\\\\<>])/', $filename, $match)) {
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

		$oldFilename = $media->getFilename();
		$newFilename = $folderName . $fileName;

		// Cannot rename local to external or vice-versa
		if (isFileExternal($oldFilename) != isFileExternal($filename)) {
			KT_FlashMessages::addMessage(KT_I18N::translate('Media file %1$s could not be renamed to %2$s.', '<span class="filename">'.$oldFilename.'</span>', '<span class="filename">'.$newFilename.'</span>'));
			break;
		}

		$messages = false;
		// Move files on disk (if we can) to reflect the change to the GEDCOM data
		if (!$media->isExternal()) {
			$oldServerFile  = $media->getServerFilename('main');
			$oldServerThumb = $media->getServerFilename('thumb');

			$newmedia = new KT_Media("0 @xxx@ OBJE\n1 FILE " . $newFilename);
			$newServerFile  = $newmedia->getServerFilename('main');
			$newServerThumb = $newmedia->getServerFilename('thumb');

			// We could be either renaming an existing file, or updating a record (with no valid file) to point to a new file
			if ($oldServerFile != $newServerFile) {
				//-- check if the file is used in more than one gedcom
				//-- do not allow it to be moved or renamed if it is
				$multi_gedcom=!$media->isExternal() && is_media_used_in_other_gedcom($media->getFilename(), KT_GED_ID);
				if ($multi_gedcom) {
					KT_FlashMessages::addMessage(KT_I18N::translate('This file is linked to another genealogical database on this server.  It cannot be deleted, moved, or renamed until these links have been removed.'));
					break;
				}

				if (!file_exists($newServerFile) || @md5_file($oldServerFile)==md5_file($newServerFile)) {
					if (@rename($oldServerFile, $newServerFile)) {
						KT_FlashMessages::addMessage(KT_I18N::translate('Media file %1$s successfully renamed to %2$s.', '<span class="filename">'.$oldFilename.'</span>', '<span class="filename">'.$newFilename.'</span>'));
					} else {
						KT_FlashMessages::addMessage(KT_I18N::translate('Media file %1$s could not be renamed to %2$s.', '<span class="filename">'.$oldFilename.'</span>', '<span class="filename">'.$newFilename.'</span>'));
					}
					$messages = true;
				}
				if (!file_exists($newServerFile)) {
					KT_FlashMessages::addMessage(KT_I18N::translate('Media file %s does not exist.', '<span class="filename">'.$newFilename.'</span>'));
					$messages = true;
				}
			}
			if ($oldServerThumb != $newServerThumb) {
				if (!file_exists($newServerThumb) || @md5_file($oldServerFile)==md5_file($newServerThumb)) {
					if (@rename($oldServerThumb, $newServerThumb)) {
						KT_FlashMessages::addMessage(KT_I18N::translate('Thumbnail file %1$s successfully renamed to %2$s.', '<span class="filename">'.$oldFilename.'</span>', '<span class="filename">'.$newFilename.'</span>'));
					} else {
						KT_FlashMessages::addMessage(KT_I18N::translate('Thumbnail file %1$s could not be renamed to %2$s.', '<span class="filename">'.$oldFilename.'</span>', '<span class="filename">'.$newFilename.'</span>'));
					}
					$messages = true;
				}
				if (!file_exists($newServerThumb)) {
					KT_FlashMessages::addMessage(KT_I18N::translate('Thumbnail file %s does not exist.', '<span class="filename">'.$newFilename.'</span>'));
					$messages = true;
				}
			}
		}

		// Insert the 1 FILE xxx record into the arrays used by function handle_updates()
		$glevels	= array_merge(array('1'), $glevels);
		$tag		= array_merge(array('FILE'), $tag);
		$islink		= array_merge(array(0), $islink);
		$text		= array_merge(array($newFilename), $text);

		if (!empty($pid)) {
			$gedrec = find_gedcom_record($pid, KT_GED_ID, true);
		}
		$newrec = "0 @$pid@ OBJE\n";
		$newrec = handle_updates($newrec);
		if (!$update_CHAN) {
			$newrec .= get_sub_record(1, '1 CHAN', $gedrec);
		}
		//-- look for the old record media in the file
		//-- if the old media record does not exist that means it was
		//-- generated at import and we need to append it
		replace_gedrec($pid, KT_GED_ID, $newrec, $update_CHAN);

		if ($pid && $linktoid != '') {
			$link = linkMedia($pid, $linktoid, 1);
			if ($link) {
				AddToLog('Media ID ' . $pid . ' successfully added to' . $linktoid, 'edit');
			}
		}
		$controller->pageHeader();
		if ($messages) { ?>
			<button onclick="closePopupAndReloadParent();">
				<?php echo KT_I18N::translate('close'); ?>
			</button>
		<?php } else {
			$controller->addInlineJavascript('closePopupAndReloadParent();');

		}
		exit;
	case 'showmediaform':
		$controller->setPageTitle(KT_I18N::translate('Create a new media object'));
		$action = 'create';
		break;
	case 'editmedia':
		$controller->setPageTitle(KT_I18N::translate('Edit media object'));
		$action = 'update';
		break;
	default:
		throw new Exception('Bad $action (' . $action . ') in addmedia.php');
}

$controller
		->pageHeader()
		->getPageTitle();
?>
<div id="addmedia-page">
	<h2><?php echo $controller->getPageTitle(); ?></h2>
	<form method="post" name="newmedia" action="addmedia.php" enctype="multipart/form-data">
		<input type="hidden" name="action" value="<?php echo $action; ?>">
		<input type="hidden" name="ged" value="<?php echo  KT_GEDCOM; ?>">
		<input type="hidden" name="pid" value="<?php echo  $pid; ?>">
		<?php if ($linktoid) { ?>
			<input type="hidden" name="linktoid" value="<?php echo $linktoid; ?>">
		<?php } ?>
		<div id="add_facts">
			<?php if (!$linktoid && $type != 'event' && $action == 'create') { ?>
				<div id="MEDIA_factdiv">
					<label>
						<?php echo KT_I18N::translate('Enter a Person, Family, or Source ID'); ?>
					</label>
					<div class="input">
						<input type="text" data-autocomplete-type="IFS" name="linktoid" id="linktoid" value="">
					</div>
					<div class="help_text">
						<span class="help_content">
							<?php echo KT_I18N::translate('Enter or search for the ID of the person, family, or source to which this media item should be linked.'); ?>
						</span>
					</div>
				</div>
			<?php }
			$gedrec = find_gedcom_record($pid, KT_GED_ID, true);
			// 0 OBJE
			// 1 FILE
			if ($gedrec == '') {
				$gedfile = 'FILE';
				if ($filename != '')
					$gedfile = 'FILE ' . $filename;
			} else {
				$gedfile = get_first_tag(1, 'FILE', $gedrec);
				if (empty($gedfile))
					$gedfile = 'FILE';
			}
			if ($gedfile != 'FILE') {
				$gedfile = 'FILE ' . substr($gedfile, 5);
				$readOnly = 'READONLY';
			} else {
				$readOnly = '';
			}
			if ($gedfile == 'FILE') {
				// Box for user to choose to upload file from local computer ?>
				<div id="MEDIA-UP_factdiv">
					<label>
						<?php echo  KT_I18N::translate('Media file to upload'); ?>
					</label>
					<div class="input">
						<input type="file" name="mediafile" onchange="updateFormat(this.value);">
						<div class="help_text">
							<span class="help_content">
								<?php echo KT_I18N::translate('Maximum file size allowed is %s', detectMaxUploadFileSize()); ?>
							</span>
						</div>
					</div>
				</div>
				<?php
				// Check for thumbnail generation support
				if (KT_USER_GEDCOM_ADMIN) { ?>
					<div id="THUMB-UP_factdiv">
						<label>
							<?php echo KT_I18N::translate('Thumbnail to upload'); ?>
						</label>
						<div class="input">
							<input type="file" name="thumbnail">
						</div>
						<span id="upload_thumbnail_file" class="help_text"></span>
					</div>
				<?php }
			}

			// Filename on server
			$isExternal = isFileExternal($gedfile);
			if ($gedfile == 'FILE') {
				if (KT_USER_GEDCOM_ADMIN) { ?>
					<div id="FILE_factdiv">
						<?php add_simple_tag("1 $gedfile",'',KT_I18N::translate('File name on server'),'','NOCLOSE'); ?>
					</div>
					<div class="help_text">
						<span class="help_content">
							<?php echo
								KT_I18N::translate('Do not change to keep original file name.') .
								KT_I18N::translate('You may enter a URL, beginning with &laquo;http://&raquo;.');
							?>
						</span>
					</div>
				<?php }
				$fileName = '';
				$folder = '';
			} else {
				if ($isExternal) {
					$fileName	= substr($gedfile, 5);
					$folder		= '';
				} else {
					$tmp		=substr($gedfile, 5);
					$fileName	= basename($tmp);
					$folder		= dirname($tmp);
					if ($folder == '.') {
						$folder = '';
					}
				} ?>
				<div id="SERVER_factdiv">
					<label>
						<?php echo KT_I18N::translate('File name on server'); ?>
					</label>
					<div class="input">
						<?php if (KT_USER_GEDCOM_ADMIN) { ?>
						    <input name="filename" type="text" value="<?php echo htmlspecialchars($fileName); ?>"
						    <?php if ($isExternal) {
						        echo '>';
						    } else {
						        echo '>'; ?>
						    <?php } ?>
						<?php } else { ?>
						    <?php echo $fileName; ?>
						    <input name="filename" type="hidden" value="<?php echo htmlspecialchars($fileName); ?>">
						<?php } ?>
					</div>
					<div class="help_text">
						<span class="helpcontent">
							<?php echo /* I18N: Help text for add media screens*/KT_I18N::translate('Leave this field blank to keep the original name of the file you have uploaded from your local computer.<br><br>Media files can and perhaps should be named differently on the server than on your local computer. This is because often the local file name has meaning to you but is less meaningful to others visiting this site. Consider also the possibility that you and someone else both try to upload different files called "granny.jpg".<br>In this field you specify the new name of the file. The name you enter here will also be used to name the thumbnail, which can be uploaded separately or generated automatically. You do not need to enter the file name extension (jpg, gif, pdf, doc, etc.)'); ?>
						</span>
					</div>
				</div>
			<?php } ?>

			<div id="SERVER_factdiv">
				<?php // Box for user to choose the folder to store the image
				if (!$isExternal) { ?>
						<label>
							<?php echo KT_I18N::translate('Folder name on server'); ?>
						</label>
					<div class="input">
						<?php //-- don’t let regular users change the location of media items
						if ($action != 'update' || KT_USER_GEDCOM_ADMIN) {
							$mediaFolders = KT_Query_Media::folderList();
							echo '<span dir="ltr">
								<select name="folder_list" onchange="document.newmedia.folder.value=this.options[this.selectedIndex].value;">
									<option';
										if ($folder == '') {
											echo ' selected="selected"';
										}
										echo ' value=""> ' . KT_I18N::translate('Choose: ') . '
									</option>';
									if (KT_USER_IS_ADMIN) {
										echo ' <option value="other" disabled>'.
											KT_I18N::translate('Other folder... please type in') . '
										</option>';
									}
									foreach ($mediaFolders as $f) {
										echo '<option value="' . $f . '"';
											if ($folder === $f) {
												echo ' selected="selected"';
											}
										echo '>' . $f . '</option>';
									}
								echo '</select>
							</span>';
						} else {
							echo $folder;
						}
						if (KT_USER_IS_ADMIN) {
							echo '<br>
							<span dir="ltr">
								<input type="text" name="folder" value="' . $folder . '">
							</span>';
						} else { ?>
							<input name="folder" type="hidden" value="<?php echo addslashes($folder); ?>">
					<?php } ?>
					</div>
					<?php if ($gedfile == 'FILE') { ?>
						<div class="help_text">
							<span class="help_content">
								<?php echo KT_I18N::translate('This entry is ignored if you have entered a URL into the file name field.'); ?>
							</span>
							<span id="upload_server_folder" class="help_text"></span>
						</div>
					<?php }
				} else { ?>
					<div class="input">
						<input name="folder" type="hidden" value="">
					</div>
				<?php } ?>
			</div>
			<hr>
			<?php
			// 2 TITL
			if ($gedrec == '') {
				$gedtitl = 'TITL';
			} else {
				$gedtitl = get_first_tag(2, 'TITL', $gedrec);
				if (empty($gedtitl)) {
					$gedtitl = get_first_tag(1, 'TITL', $gedrec);
				}
				if (empty($gedtitl)) {
					$gedtitl = 'TITL';
				}
			}
			add_simple_tag("2 $gedtitl");

			if (strstr($ADVANCED_NAME_FACTS, '_HEB')!==false) {
				// 3 _HEB
				if ($gedrec == '') {
					$gedtitl = '_HEB';
				} else {
					$gedtitl = get_first_tag(3, '_HEB', $gedrec);
					if (empty($gedtitl)) {
						$gedtitl = '_HEB';
					}
				}
				add_simple_tag("3 $gedtitl");
			}

			if (strstr($ADVANCED_NAME_FACTS, 'ROMN')!==false) {
				// 3 ROMN
				if ($gedrec == '') {
					$gedtitl = 'ROMN';
				} else {
					$gedtitl = get_first_tag(3, 'ROMN', $gedrec);
					if (empty($gedtitl)) {
						$gedtitl = 'ROMN';
					}
				}
				add_simple_tag("3 $gedtitl");
			}
			// 2 FORM
			if ($gedrec == '')
				$gedform = 'FORM';
			else {
				$gedform = get_first_tag(2, 'FORM', $gedrec);
				if (empty($gedform))
					$gedform = 'FORM';
			}
			$formid = add_simple_tag("2 $gedform");

			// automatically set the format field from the filename
			$controller->addInlineJavascript('
				function updateFormat(filename) {
					var extsearch=/\.([a-zA-Z]{3,4})$/;
					if (extsearch.exec(filename)) {
						ext = RegExp.$1.toLowerCase();
						if (ext=="jpg") ext="jpeg";
						if (ext=="tif") ext="tiff";
					} else {
						ext = "";
					}
					formfield = document.getElementById("' . $formid . '");
					formfield.value = ext;
				}
			');

			// 3 TYPE
			if ($gedrec == '')
				$gedtype = 'TYPE photo'; // default to ‘Photo’ unless told otherwise
			else {
				$temp = str_replace("\r\n", "\n", $gedrec) . "\n";
				$types = preg_match("/3 TYPE(.*)\n/", $temp, $matches);
				if (empty($matches[0]))
					$gedtype = 'TYPE photo'; // default to ‘Photo’ unless told otherwise
				else
					$gedtype = 'TYPE ' . trim($matches[1]);
			}
			add_simple_tag("3 $gedtype");


			// 2 _PRIM
			if ($gedrec == '') {
				$gedprim = '_PRIM';
			} else {
				$gedprim = get_first_tag(1, '_PRIM', $gedrec);
				if (empty($gedprim)) {
					$gedprim = '_PRIM';
				}
			}
			add_simple_tag(
				"1 $gedprim",
				'',
				'',
				''
			); ?>
			<div class="help_text">
				<span id="<?php echo $gedprim; ?>" class="help_text"></span>
			</div>
			<?php
			//-- print out editing fields for any other data in the media record
			$sourceSOUR = '';
			if (!empty($gedrec)) {
				preg_match_all('/\n(1 (?!FILE|FORM|TYPE|TITL|_PRIM|_THUM|CHAN|DATA).*(\n[2-9] .*)*)/', $gedrec, $matches);
				foreach ($matches[1] as $subrec) {
					$pieces = explode("\n", $subrec);
					foreach ($pieces as $piece) {
						$ft = preg_match("/(\d) (\w+)(.*)/", $piece, $match);
						if ($ft == 0) continue;
						$subLevel = $match[1];
						$fact = trim($match[2]);
						$event = trim($match[3]);
						if ($fact=='NOTE' || $fact=='TEXT') {
							$event .= get_cont(($subLevel +1), $subrec, false);
						}
						if ($sourceSOUR!='' && $subLevel<=$sourceLevel) {
							// Get rid of all saved Source data
							add_simple_tag($sourceLevel .' SOUR '. $sourceSOUR);
							add_simple_tag(($sourceLevel+1) .' PAGE '. $sourcePAGE);
							add_simple_tag(($sourceLevel+2) .' TEXT '. $sourceTEXT);
							add_simple_tag(($sourceLevel+2) .' DATE '. $sourceDATE, '', KT_Gedcom_Tag::getLabel('DATA:DATE'));
							add_simple_tag(($sourceLevel+1) .' QUAY '. $sourceQUAY);
							$sourceSOUR = '';
						}

						if ($fact=='SOUR') {
							$sourceLevel = $subLevel;
							$sourceSOUR = $event;
							$sourcePAGE = '';
							$sourceTEXT = '';
							$sourceDATE = '';
							$sourceQUAY = '';
							continue;
						}

						// Save all incoming data about this source reference
						if ($sourceSOUR!='') {
							if ($fact == 'PAGE') {
								$sourcePAGE = $event;
								continue;
							}
							if ($fact == 'TEXT') {
								$sourceTEXT = $event;
								continue;
							}
							if ($fact == 'DATE') {
								$sourceDATE = $event;
								continue;
							}
							if ($fact == 'QUAY') {
								$sourceQUAY = $event;
								continue;
							}
							continue;
						}

						// Output anything that isn’t part of a source reference
						if (!empty($fact) && $fact != 'CONC' && $fact != 'CONT' && $fact != 'DATA') {
							add_simple_tag($subLevel . ' ' . $fact . ' ' . $event);
						}
					}
				}

				if ($sourceSOUR != '') {
					// Get rid of all saved Source data
					add_simple_tag($sourceLevel .' SOUR '. $sourceSOUR);
					add_simple_tag(($sourceLevel+1) .' PAGE '. $sourcePAGE);
					add_simple_tag(($sourceLevel+2) .' TEXT '. $sourceTEXT);
					add_simple_tag(($sourceLevel+2) .' DATE '. $sourceDATE, '', KT_Gedcom_Tag::getLabel('DATA:DATE'));
					add_simple_tag(($sourceLevel+1) .' QUAY '. $sourceQUAY);
				}
			}

			if (KT_USER_IS_ADMIN && $action != 'create') {
				echo no_update_chan($media);
			} ?>

		</div>
		<div id="additional_facts">
			<p><?php echo print_add_layer('SOUR', 1); ?></p>
			<p><?php echo print_add_layer('NOTE', 1); ?></p>
			<p><?php echo print_add_layer('SHARED_NOTE', 1); ?></p>
			<p><?php echo print_add_layer('RESN', 1); ?></p>
		</div>
		<p id="save-cancel">
			<button class="btn btn-primary" type="submit">
				<i class="fa fa-save"></i>
				<?php echo KT_I18N::translate('Save'); ?>
			</button>
			<button class="btn btn-primary" type="button" onclick="window.close();">
				<i class="fa fa-times"></i>
				<?php echo KT_I18N::translate('close'); ?>
			</button>
		</p>
	</form>
</div>
