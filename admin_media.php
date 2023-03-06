<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net
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

define('KT_SCRIPT_NAME', 'admin_media.php');
require './includes/session.php';
require KT_ROOT . 'includes/functions/functions_edit.php';

// type of file/object to include
$files = safe_GET('files', array('local', 'external', 'unused'), 'local');

// family tree setting MEDIA_DIRECTORY
$media_folders = all_media_folders();
$media_folder  = KT_Filter::get('media_folder', KT_REGEX_UNSAFE);
// User folders may contain special characters.  Restrict to actual folders.
if (!array_key_exists($media_folder, $media_folders)) {
	$media_folder = reset($media_folders);
}

// prefix to filename
$media_paths = media_paths($media_folder);
$media_path  = KT_Filter::get('media_path', KT_REGEX_UNSAFE);
// User paths may contain special characters.  Restrict to actual paths.
if (!array_key_exists($media_path, $media_paths)) {
	$media_path = reset($media_paths);
}

// subfolders within $media_path
$subfolders = safe_GET('subfolders', array('include', 'exclude'), 'include');
$action     = KT_Filter::get('action');

////////////////////////////////////////////////////////////////////////////////
// POST callback for file deletion
////////////////////////////////////////////////////////////////////////////////
$delete_file = KT_Filter::post('delete', KT_REGEX_UNSAFE);
if ($delete_file) {
	$controller = new KT_Controller_Ajax;
	// Only delete valid (i.e. unused) media files
	$media_folder = KT_Filter::post('media_folder', KT_REGEX_UNSAFE);
	$disk_files = all_disk_files ($media_folder, '', 'include', '');
	if (in_array($delete_file, $disk_files)) {
		$tmp = KT_DATA_DIR . $media_folder . $delete_file;
		if (@unlink($tmp)) {
			KT_FlashMessages::addMessage(KT_I18N::translate('The file %s was deleted.', $tmp));
		} else {
			KT_FlashMessages::addMessage(KT_I18N::translate('The file %s could not be deleted.', $tmp));
		}
		$tmp = KT_DATA_DIR . $media_folder . 'thumbs/' . $delete_file;
		if (file_exists($tmp)) {
			if (@unlink($tmp)) {
				KT_FlashMessages::addMessage(KT_I18N::translate('The file %s was deleted.', $tmp));
			} else {
				KT_FlashMessages::addMessage(KT_I18N::translate('The file %s could not be deleted.', $tmp));
			}
		}
	} else {
		// File no longer exists?  Maybe it was already deleted or renamed.
	}
	$controller->pageHeader();
	exit;
}

////////////////////////////////////////////////////////////////////////////////
// GET callback for server-side pagination
////////////////////////////////////////////////////////////////////////////////

switch($action) {
case 'load_json':
	Zend_Session::writeClose();
	$sSearch        = KT_Filter::get('sSearch');
	$iDisplayStart  = (int)KT_Filter::get('iDisplayStart');
	$iDisplayLength = (int)KT_Filter::get('iDisplayLength');

	switch ($files) {
	case 'local':
		// Filtered rows
		$SELECT1 = "
			SELECT SQL_CALC_FOUND_ROWS TRIM(LEADING ?
			FROM m_filename) AS media_path, 'OBJE' AS type, m_titl, m_id AS xref, m_file AS ged_id, m_gedcom AS gedrec, m_filename
			FROM  `##media`
			JOIN  `##gedcom_setting` ON (m_file = gedcom_id AND setting_name = 'MEDIA_DIRECTORY')
			JOIN  `##gedcom`
			USING (gedcom_id)
			WHERE setting_value=?
			AND m_filename LIKE CONCAT(?, '%')
			AND (SUBSTRING_INDEX(m_filename, '/', -1) LIKE CONCAT('%', ?, '%')
			OR  m_titl LIKE CONCAT('%', ?, '%'))
			AND m_filename NOT LIKE 'http://%'
			AND m_filename NOT LIKE 'https://%'
		";
		$ARGS1 = array($media_path, $media_folder, $media_path, $sSearch, $sSearch);
		// Unfiltered rows
		$SELECT2 =
				"SELECT COUNT(*)" .
				" FROM  `##media`" .
				" JOIN  `##gedcom_setting` ON (m_file = gedcom_id AND setting_name = 'MEDIA_DIRECTORY')" .
				" WHERE setting_value=?" .
				" AND   m_filename LIKE CONCAT(?, '%')" .
				" AND   m_filename NOT LIKE 'http://%'" .
				" AND   m_filename NOT LIKE 'https://%'";
		$ARGS2 = array($media_folder, $media_path);

		if ($subfolders=='exclude') {
			$SELECT1 .= " AND m_filename NOT LIKE CONCAT(?, '%/%')";
			$ARGS1[] = $media_path;
			$SELECT2 .= " AND m_filename NOT LIKE CONCAT(?, '%/%')";
			$ARGS2[] = $media_path;
		}

		if ($iDisplayLength>0) {
			$LIMIT = " LIMIT " . $iDisplayStart . ',' . $iDisplayLength;
		} else {
			$LIMIT = "";
		}
		$iSortingCols = KT_Filter::get('iSortingCols');
		if ($iSortingCols) {
			$ORDER_BY = " ORDER BY ";
			for ($i=0; $i<$iSortingCols; ++$i) {
				// Datatables numbers columns 0, 1, 2, ...
				// MySQL numbers columns 1, 2, 3, ...
				switch (KT_Filter::get('sSortDir_' . $i)) {
				case 'asc':
					$ORDER_BY .= (1+(int)KT_Filter::get('iSortCol_' . $i)).' ASC ';
					break;
				case 'desc':
					$ORDER_BY .= (1+(int)KT_Filter::get('iSortCol_' . $i)).' DESC ';
					break;
				}
				if ($i<$iSortingCols-1) {
					$ORDER_BY .= ',';
				}
			}
		} else {
			$ORDER_BY = "1 ASC";
		}

		$rows = KT_DB::prepare($SELECT1 . $ORDER_BY . $LIMIT)->execute($ARGS1)->fetchAll(PDO::FETCH_ASSOC);
		// Total filtered/unfiltered rows
		$iTotalDisplayRecords = KT_DB::prepare("SELECT FOUND_ROWS()")->fetchColumn();
		$iTotalRecords        = KT_DB::prepare($SELECT2)->execute($ARGS2)->fetchColumn();

		$aaData = array();
		foreach ($rows as $row) {
			$media = KT_Media::getInstance($row);
			switch ($media->isPrimary()) {
			case 'Y':
				$highlight = KT_I18N::translate('yes');
				break;
			case 'N':
				$highlight = KT_I18N::translate('no');
				break;
			default:
				$highlight = '';
				break;
			}

            // table array
			$aaData[] = array(
				media_file_info($media_folder, $media_path, $row['media_path']),
				$media->displayImage(),
				media_object_info($media),
				$highlight,
				KT_Gedcom_Tag::getFileFormTypeValue($media->getMediaType()),
                '',
                $media_path . $row['media_path']
			);
		}
		break;

	case 'external':
		// Filtered rows
		$SELECT1 =
				"SELECT SQL_CALC_FOUND_ROWS m_filename AS media_path, 'OBJE' AS type, m_id AS xref, m_file AS ged_id, m_gedcom AS gedrec, m_titl, m_filename" .
				" FROM  `##media`" .
				" WHERE (m_filename LIKE 'http://%' OR m_filename LIKE 'https://%')" .
				" AND   (m_filename LIKE CONCAT('%', ?, '%') OR m_titl LIKE CONCAT('%', ?, '%'))";
		$ARGS1 = array($sSearch, $sSearch);
		// Unfiltered rows
		$SELECT2 =
				"SELECT COUNT(*)" .
				" FROM  `##media`" .
				" WHERE (m_filename LIKE 'http://%' OR m_filename LIKE 'https://%')";
		$ARGS2 = array();

		if ($iDisplayLength>0) {
			$LIMIT = " LIMIT " . $iDisplayStart . ',' . $iDisplayLength;
		} else {
			$LIMIT = "";
		}
		$iSortingCols = KT_Filter::get('iSortingCols');
		if ($iSortingCols) {
			$ORDER_BY = " ORDER BY ";
			for ($i=0; $i<$iSortingCols; ++$i) {
				// Datatables numbers columns 0, 1, 2, ...
				// MySQL numbers columns 1, 2, 3, ...
				switch (KT_Filter::get('sSortDir_' . $i)) {
				case 'asc':
					$ORDER_BY .= (1+(int)KT_Filter::get('iSortCol_' . $i)).' ASC ';
					break;
				case 'desc':
					$ORDER_BY .= (1+(int)KT_Filter::get('iSortCol_' . $i)).' DESC ';
					break;
				}
				if ($i<$iSortingCols-1) {
					$ORDER_BY.=',';
				}
			}
		} else {
			$ORDER_BY="1 ASC";
		}

		$rows = KT_DB::prepare($SELECT1 . $ORDER_BY . $LIMIT)->execute($ARGS1)->fetchAll(PDO::FETCH_ASSOC);
		// Total filtered/unfiltered rows
		$iTotalDisplayRecords = KT_DB::prepare("SELECT FOUND_ROWS()")->fetchColumn();
		$iTotalRecords        = KT_DB::prepare($SELECT2)->execute($ARGS2)->fetchColumn();

		$aaData = array();
		foreach ($rows as $row) {
			$media = KT_Media::getInstance($row);
			switch ($media->isPrimary()) {
			case 'Y':
				$highlight = KT_I18N::translate('yes');
				break;
			case 'N':
				$highlight = KT_I18N::translate('no');
				break;
			default:
				$highlight = '';
				break;
			}

            // table array
			$aaData[] = array(
			 	KT_Gedcom_Tag::getLabelValue('URL', $row['m_filename']),
				$media->displayImage(),
				media_object_info($media),
				$highlight,
				KT_Gedcom_Tag::getFileFormTypeValue($media->getMediaType()),
                '',
                KT_Gedcom_Tag::getLabelValue('URL', $row['m_filename'])
			);
		}
		break;

	case 'unused':
		// Which trees use this media folder?
		$media_trees = KT_DB::prepare(
			"SELECT gedcom_name, gedcom_name" .
			" FROM `##gedcom`" .
			" JOIN `##gedcom_setting` USING (gedcom_id)" .
			" WHERE setting_name='MEDIA_DIRECTORY' AND setting_value=? AND gedcom_id > 0"
		)->execute(array($media_folder))->fetchAssoc();

		$disk_files = all_disk_files ($media_folder, $media_path, $subfolders, $sSearch);
		$db_files   = all_media_files($media_folder, $media_path, $subfolders, $sSearch);

		// All unused files
		$unused_files  = array_diff($disk_files, $db_files);
		$iTotalRecords = count($unused_files);

		// Filter unused files
		if ($sSearch) {
			$unused_files = array_filter($unused_files, function($x) use ($sSearch) {return strpos($x, $sSearch)!==false;});
		}
		$iTotalDisplayRecords = count($unused_files);

		// Sort files - only option is column 0
		sort($unused_files);
		if (KT_Filter::get('sSortDir_0') == 'desc') {
			$unused_files = array_reverse($unused_files);
		}

		// Paginate unused files
		$unused_files = array_slice($unused_files, $iDisplayStart, $iDisplayLength);

		$aaData = array();
		foreach ($unused_files as $unused_file) {
			$full_path  = KT_DATA_DIR . $media_folder . $media_path . $unused_file;
			$thumb_path = KT_DATA_DIR . $media_folder . 'thumbs/' . $media_path . $unused_file;
			if (!file_exists($thumb_path)) {
				$thumb_path = $full_path;
			}

			$imgsize = getimagesize($thumb_path);
			if ($imgsize && $imgsize[0] && $imgsize[1]) {
				// We can’t create a URL (not in public_html) or use the media firewall (no such object)
				// so just the base64-encoded image inline.
				$img = '<img src="data:' . $imgsize['mime'] . ';base64,' . base64_encode(file_get_contents($thumb_path)) . '" class="thumbnail" ' . $imgsize[3] . '" style="max-width:100px;height:auto;">';
			} else {
				$img = '-';
			}

			// Is there a pending record for this file?
			$exists_pending = KT_DB::prepare("
				SELECT 1 FROM `##change`
					WHERE status='pending'
					AND new_gedcom
					LIKE CONCAT('%\n1 FILE ', ?, '\n%')
			")->execute(array($unused_file))->fetchOne();

			// Form to create new media object in each tree
			$create_form='';
			if (!$exists_pending) {
				foreach ($media_trees as $media_tree) {
					$create_form .= '
						<p>
							<a onclick="window.open(\'addmedia.php?action=showmediaform&amp;ged=' . rawurlencode((string) $media_tree) . '&amp;filename=' . rawurlencode((string) $unused_file) . '\'); return false;">' .
								KT_I18N::translate('Create') . '
							</a>
							 — ' .
							KT_Filter::escapeHtml($media_tree) . '
						<p>
					';
				}
			}

            //-- Select & delete
			$delete_link = '
                <div class="delete_src">
				    <input type="checkbox" name="del_places[]" class="check" value="' . addslashes($media_folder . $media_path . $unused_file) . '" title="'. KT_I18N::translate('Delete'). '">
				</div>
            ';

            // table array
			$aaData[] = array(
				media_file_info($media_folder, $media_path, $unused_file),
				$img,
				$create_form,
				'',
				'',
                $delete_link,
                $media_path . $unused_file //for csv only
		    );
		}
		break;
	}

	header('Content-type: application/json');
	echo json_encode(array( // See http://www.datatables.net/usage/server-side
		'sEcho'                => (int)KT_Filter::get('sEcho'),
		'iTotalRecords'        => $iTotalRecords,
		'iTotalDisplayRecords' => $iTotalDisplayRecords,
		'aaData'               => $aaData
	));
	exit;
}

////////////////////////////////////////////////////////////////////////////////
// Local functions
////////////////////////////////////////////////////////////////////////////////

// A unique list of media folders, from all trees.
function all_media_folders() {
	return KT_DB::prepare(
		"SELECT setting_value, setting_value" .
		" FROM `##gedcom_setting`" .
		" WHERE setting_name='MEDIA_DIRECTORY'" .
		" GROUP BY 1" .
		" ORDER BY 1"
	)->fetchAssoc();
}

function media_paths($media_folder) {
	$media_paths = KT_DB::prepare(
		"SELECT LEFT(m_filename, CHAR_LENGTH(m_filename) - CHAR_LENGTH(SUBSTRING_INDEX(m_filename, '/', -1))) AS media_path" .
		" FROM  `##media`" .
		" JOIN  `##gedcom_setting` ON (m_file = gedcom_id AND setting_name = 'MEDIA_DIRECTORY')" .
		" WHERE setting_value=?" .
		"	AND   m_filename NOT LIKE 'http://%'" .
		" AND   m_filename NOT LIKE 'https://%'" .
		" GROUP BY 1" .
		" ORDER BY 1"
	)->execute(array($media_folder))->fetchOneColumn();

	if (!$media_paths || reset($media_paths)!='') {
		// Always include a (possibly empty) top-level folder
		array_unshift($media_paths, '');
	}

	return array_combine($media_paths, $media_paths);
}

function scan_dirs($dir, $recursive, $filter) {
	$files = array();

	// $dir comes from the database.  The actual folder may not exist.
	if (is_dir($dir)) {
		foreach (scandir($dir) as $path) {
			if (is_dir($dir . $path)) {
				// TODO - but what if there are user-defined subfolders “thumbs” or “watermarks”…
				if ($path!='.' && $path!='..' && $path!='thumbs' && $path!='watermarks' && $recursive) {
					foreach (scan_dirs($dir . $path . '/', $recursive, $filter) as $subpath) {
						$files[] = $path . '/' . $subpath;
					}
				}
			} elseif (!$filter || stripos($path, $filter)!==false) {
				$files[] = $path;
			}
		}
	}
	return $files;
}

// Fetch a list of all files on disk
function all_disk_files($media_folder, $media_path, $subfolders, $filter) {
	return scan_dirs(KT_DATA_DIR . $media_folder . $media_path, $subfolders == 'include', $filter);
}

function externalMedia() {
	$count = KT_DB::prepare("SELECT COUNT(*) FROM `##media` WHERE (m_filename LIKE 'http://%' OR m_filename LIKE 'https://%')")
		->execute()
		->fetchOne();
	return	$count;
}

// Fetch a list of all files on in the database
function all_media_files($media_folder, $media_path, $subfolders, $filter) {
	return KT_DB::prepare(
		"SELECT SQL_CALC_FOUND_ROWS TRIM(LEADING ? FROM m_filename) AS media_path, 'OBJE' AS type, m_titl, m_id AS xref, m_file AS ged_id, m_gedcom AS gedrec, m_filename" .
		" FROM  `##media`" .
		" JOIN  `##gedcom_setting` ON (m_file = gedcom_id AND setting_name = 'MEDIA_DIRECTORY')" .
		" JOIN  `##gedcom`         USING (gedcom_id)" .
		" WHERE setting_value=?" .
		" AND   m_filename LIKE CONCAT(?, '%')" .
		" AND   (SUBSTRING_INDEX(m_filename, '/', -1) LIKE CONCAT('%', ?, '%')" .
		"  OR   m_titl LIKE CONCAT('%', ?, '%'))" .
		"	AND   m_filename NOT LIKE 'http://%'" .
		" AND   m_filename NOT LIKE 'https://%'"
	)->execute(array($media_path, $media_folder, $media_path, $filter, $filter))->fetchOneColumn();
}

function media_file_info($media_folder, $media_path, $file) {
	$html = '<b>' . htmlspecialchars((string) $file). '</b>';

	$full_path = KT_DATA_DIR . $media_folder . $media_path . $file;
	if ($file && file_exists($full_path)) {
		$size = @filesize($full_path);
		if ($size!==false) {
			$size = (int)(($size+1023)/1024); // Round up to next KB
			$size = /* I18N: size of file in KB */ KT_I18N::translate('%s KB', KT_I18N::number($size));
			$html .= KT_Gedcom_Tag::getLabelValue('__FILE_SIZE__', $size);
			$imgsize = @getimagesize($full_path);
			if (is_array($imgsize)) {
				$imgsize = /* I18N: image dimensions, width × height */ KT_I18N::translate('%1$s × %2$s pixels', KT_I18N::number($imgsize['0']), KT_I18N::number($imgsize['1']));
				$html .= KT_Gedcom_Tag::getLabelValue('__IMAGE_SIZE__', $imgsize);
			}
		} else {
			$html .= '<div class="error">' . KT_I18N::translate('This media file exists, but cannot be accessed.') . '</div>' ;
		}
	} else {
		$html .= '<div class="error">' . KT_I18N::translate('This media file does not exist.') . '</div>' ;
	}
	return $html;
}

function media_object_info(KT_Media $media) {
	$xref   = $media->getXref();
	$gedcom = KT_Tree::getNameFromId($media->getGedId());
	$name   = $media->getFullName();
	$conf   = KT_Filter::escapeJS(KT_I18N::translate('Are you sure you want to delete “%s”?', strip_tags($name)));

	$html   =
		'<b>' . $name . '</b>' .
		'<div><i>' . htmlspecialchars((string) $media->getNote()) . '</i></div>' .
		'<br>' .
		'<a href="' . $media->getHtmlUrl() . '">' . KT_I18N::translate('View') . '</a>';

	$html .=
		' - ' .
		'<a href="addmedia.php?action=editmedia&amp;pid=' . $xref . '&ged=' . $gedcom . '" target="_blank" >' . KT_I18N::Translate('Edit') . '</a>' .
		' - ' .
		'<a onclick="if (confirm(\'' . $conf . '\')) jQuery.post(\'action.php\',{action:\'delete-media\',xref:\'' . $xref . '\',ged:\'' . $gedcom . '\'},function(){location.reload();})" href="#">' . KT_I18N::Translate('Delete') . '</a>' .
		' - ';


	$html .= '<a href="inverselink.php?mediaid=' . $xref . '&amp;linkto=manage" target="_blank">' . KT_I18N::Translate('Manage links') . '</a>';
	$html .= '<br><br>';

	$linked = array();
	foreach ($media->fetchLinkedIndividuals() as $link) {
		$linked[] = '<a href="' . $link->getHtmlUrl() . '">' . $link->getFullName() .' <i>'.$link->getLifeSpan().'</i>'. '</a>';
	}
	foreach ($media->fetchLinkedFamilies() as $link) {
		$linked[] = '<a href="' . $link->getHtmlUrl() . '">' . $link->getFullName() . '</a>';
	}
	foreach ($media->fetchLinkedNotes() as $link) {
		$linked[] = '<a href="' . $link->getHtmlUrl() . '">' . $link->getFullName() . '</a>';
	}
	foreach ($media->fetchLinkedSources() as $link) {
		$linked[] = '<a href="' . $link->getHtmlUrl() . '">' . $link->getFullName() . '</a>';
	}
	foreach ($media->fetchLinkedRepositories() as $link) {
		$linked[] = '<a href="' . $link->getHtmlUrl() . '">' . $link->getFullName() . '</a>';
	}
	foreach ($media->fetchLinkedMedia() as $link) {
		$linked[] = '<a href="' . $link->getHtmlUrl() . '">' . $link->getFullName() . '</a>';
	}
	if ($linked) {
		$html .= '<ul>';
		foreach ($linked as $link) {
			$html .= '<li>' . $link . '</li>';
		}
		$html .= '</ul>';
	} else {
		$html .= '<div class="error">' . KT_I18N::translate('This media object is not linked to any other record.') . '</div>';
	}

	return $html;
}

////////////////////////////////////////////////////////////////////////////////
// Start here
////////////////////////////////////////////////////////////////////////////////

// Preserve the pagination/filtering/sorting between requests, so that the
// browser’s back button works.  Pagination is dependent on the currently
// selected folder.
$table_id  = md5($files . $media_folder . $media_path . $subfolders);

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('Media'))
	->addExternalJavascript(KT_JQUERY_DATATABLES_URL)
    ->addExternalJavascript(KT_JQUERY_DT_HTML5)
    ->addExternalJavascript(KT_JQUERY_DT_BUTTONS)
	->pageHeader()
	->addInlineJavascript('
		jQuery("#media-table-' . $table_id . '").dataTable({
			dom: \'<"H"pBf<"dt-clear">irl>t<"F"pl>\',
			processing: true,
			serverSide: true,
			ajaxSource: "' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '?action=load_json&files=' . $files . '&media_folder=' . $media_folder . '&media_path=' . $media_path . '&subfolders=' . $subfolders . '",
			' . KT_I18N::datatablesI18N(array(1, 5, 10, 20, 50, 100, 500, 1000, -1)) . ',
            buttons: [{extend: "csv", exportOptions: {columns: [6] }}],
			jQueryUI: true,
			autoWidth:false,
			pageLength: 10,
			pagingType: "full_numbers",
			stateSave: true,
			stateDuration: 0,
			columns: [
				/*0 - media file */		{},
				/*1 - media object */	{sortable: false, class: "center"},
				/*2 - media name */		{sortable: ' . ($files === 'unused' ? 'false' : 'true') . '},
				/*3 - highlighted? */	{},
				/*4 - media type */		{},
                /*5 - DELETE    */      { visible: ' . (KT_USER_GEDCOM_ADMIN && $files === 'unused' ? 'true' : 'false') . ', sortable: false, class: "center" },
                /*6 - path for CSV only*/ { visible: false}
			]
		});
	');
?>

<form method="get" action="<?php echo KT_SCRIPT_NAME; ?>">
	<table class="media_items">
		<tr>
			<th><?php echo KT_I18N::translate('Media files'); ?></th>
			<th><?php echo KT_I18N::translate('Media folders'); ?></th>
		</tr>
		<tr>
			<td>
				<input type="radio" name="files" value="local"<?php echo $files=='local' ? ' checked="checked"' : ''; ?> onchange="this.form.submit();">
				<?php echo /* I18N: “Local files” are stored on this computer */ KT_I18N::translate('Local files'); ?>
				<?php if (externalMedia() > 0){ ?>
					<br>
					<input type="radio" name="files" value="external"<?php echo $files=='external' ? ' checked="checked"' : ''; ?> onchange="this.form.submit();">
					<?php echo /* I18N: “External files” are stored on other computers */ KT_I18N::translate('External files');
				} ?>
				<br>
				<input type="radio" name="files" value="unused"<?php echo $files=='unused' ? ' checked="checked"' : ''; ?> onchange="this.form.submit();">
				<?php echo KT_I18N::translate('Unused files'); ?>
			</td>
			<td>
				<?php
					switch ($files) {
					case 'local':
					case 'unused':
						$extra = 'onchange="this.form.submit();"';
						echo
							'<span dir="ltr">', // The full path will be LTR or mixed LTR/RTL.  Force LTR.
							KT_DATA_DIR;
						// Don’t show a list of media folders if it just contains one folder
						if (count($media_folders)>1) {
							echo '&nbsp;', select_edit_control('media_folder', $media_folders, null, $media_folder, $extra);
						} else {
							echo $media_folder, '<input type="hidden" name="media_folder" value="', htmlspecialchars((string) $media_folder), '">';
						}
						// Don’t show a list of subfolders if it just contains one subfolder
						if (count($media_paths)>1) {
							echo '&nbsp;', select_edit_control('media_path', $media_paths, null, $media_path, $extra);
						} else {
							echo $media_path, '<input type="hidden" name="media_path" value="', htmlspecialchars((string) $media_path), '">';
						}
						echo
							'</span>',
							'<div>',
							'<input type="radio" name="subfolders" value="include"', ($subfolders=='include' ? ' checked="checked"' : ''), ' onchange="this.form.submit();">',
							KT_I18N::translate('Include subfolders'),
							'<br>',
							'<input type="radio" name="subfolders" value="exclude"', ($subfolders=='exclude' ? ' checked="checked"' : ''), ' onchange="this.form.submit();">',
							KT_I18N::translate('Exclude subfolders'),
							'</div>';
						break;
					case 'external':
						echo KT_I18N::translate('External media files have a URL instead of a filename.');
						echo '<input type="hidden" name="media_folder" value="', htmlspecialchars((string) $media_folder), '">';
						echo '<input type="hidden" name="media_path" value="',   htmlspecialchars((string) $media_path),   '">';
						break;
					}
				?>
			</td>
		</tr>
	</table>
</form>
<br>
<br>
<table class="media_table" id="media-table-<?php echo $table_id ?>">
	<thead>
		<tr>
			<th><?php echo KT_I18N::translate('Media file'); ?></th>
			<th><?php echo KT_I18N::translate('Media'); ?></th>
			<th><?php echo KT_I18N::translate('Media object'); ?></th>
			<th><?php echo KT_I18N::translate('Highlight'); ?></th>
			<th><?php echo KT_I18N::translate('Media type'); ?></th>
            <?php if (KT_USER_GEDCOM_ADMIN && $files === 'unused') { ?>
                <th>
                    <div class="delete_src">
                        <input type="button" value="<?php echo KT_I18N::translate('Delete'); ?>" onclick="if (confirm('<?php echo htmlspecialchars(KT_I18N::translate('Permanently delete these records?')); ?>')) {return checkbox_delete('unusedmedia');} else {return false;}">
                        <input type="checkbox" onclick="toggle_select(this)" style="vertical-align:middle;">
                    </div>
                </th>
            <?php } ?>
		</tr>
	</thead>
	<tbody>
	</tbody>
</table>
