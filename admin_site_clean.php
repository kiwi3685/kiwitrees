<?php
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2010 PGV Development Team
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

define('WT_SCRIPT_NAME', 'admin_site_clean.php');
require './includes/session.php';

$controller = new WT_Controller_Page();
$controller
	->restrictAccess(WT_USER_IS_ADMIN)
	->setPageTitle(/* I18N: The “Data folder” is a configuration setting */ WT_I18N::translate('Clean up data folder'))
	->pageHeader();

require WT_ROOT.'includes/functions/functions_edit.php';

function full_rmdir($dir) {
	if (!is_writable($dir)) {
		if (!@chmod($dir, WT_PERM_EXE)) {
			return false;
		}
	}

	$d = dir($dir);
	while (false !== ($entry = $d->read())) {
		if ($entry == '.' || $entry == '..') {
			continue;
		}
		$entry = $dir . '/' . $entry;
		if (is_dir($entry)) {
			if (!full_rmdir($entry)) {
				return false;
			}
			continue;
		}
		if (!@unlink($entry)) {
			$d->close();
			return false;
		}
	}

	$d->close();
	rmdir($dir);
	return TRUE;
}

// Vars
$ajaxdeleted		= false;
$locked_by_context	= array('index.php', 'config.ini.php', 'language');

// If we are storing the media in the data folder (this is the
// default), then don’t delete it.
// Need to consider the settings for all gedcoms
foreach (WT_Tree::getAll() as $tree) {
	$MEDIA_DIRECTORY = $tree->preference('MEDIA_DIRECTORY');

	if (substr($MEDIA_DIRECTORY, 0, 3) !='../') {
		// Just need to add the first part of the path
		$tmp = explode('/', $MEDIA_DIRECTORY);
		$locked_by_context[] = $tmp[0];
	}
}
?>
<h3><?php echo $controller->getPageTitle(); ?></h3>
<p>
	<?php echo WT_I18N::translate('Files marked with %s are required for proper operation and cannot be removed.', '<i class="icon-resn-confidential"></i>'); ?>
</p>

<?php
//post back
if (isset($_REQUEST['to_delete'])) {
	echo '<div class="error">', WT_I18N::translate('Deleted files:'), '</div>';
	foreach ($_REQUEST['to_delete'] as $k=>$v) {
		if (is_dir(WT_DATA_DIR.$v)) {
			full_rmdir(WT_DATA_DIR.$v);
		} elseif (file_exists(WT_DATA_DIR.$v)) {
			unlink(WT_DATA_DIR.$v);
		}
		echo '<div class="error">', $v, '</div>';
	}
}
?>

	<form name="delete_form" method="post" action="">
		<div id="cleanup">
			<ul>
				<?php
				$dir	 = dir(WT_DATA_DIR);
				$entries = array();
				while (false !== ($entry = $dir->read())) {
					$entries[] = $entry;
				}
				sort($entries);
				foreach ($entries as $entry) {
					if ($entry[0] != '.') {
						$file_path = WT_DATA_DIR . $entry;
						$icon = '<i class="fa ' . (is_dir($file_path)? 'fa-folder-open-o' : 'fa-file-o') . '"></i>';
						if (in_array($entry, $locked_by_context)) { ?>
							<li class="facts_value" name="<?php echo $entry; ?>" id="lock_<?php echo $entry; ?>" >
								<i class="icon-resn-confidential"></i>
								<?php echo $icon; ?>
								<span><?php echo $entry; ?></span>
						<?php } else { ?>
							<li class="facts_value" name="<?php echo $entry; ?>" id="li_<?php echo $entry; ?>" >
								<input type="checkbox" name="to_delete[]" value="<?php echo $entry; ?>">
								<?php echo $icon; ?>
								<?php echo $entry; ?>
								<?php $element[] = 'li_' . $entry; ?>
						<?php } ?>
						</li>
					<?php }
				}
				$dir->close(); ?>
			</ul>
			<button class="btn btn-primary delete" type="submit">
				<i class="fa fa-trash-o"></i>
				<?php echo WT_I18N::translate('Delete'); ?>
			</button>
		</div>
	</form>
<?php
