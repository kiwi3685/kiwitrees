<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2020 kiwitrees.net
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

define('KT_SCRIPT_NAME' , 'medialist.php');
require './includes/session.php';

require_once KT_ROOT.'includes/functions/functions_edit.php';
require_once KT_ROOT.'includes/functions/functions_print_facts.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_Module::isActiveList(KT_GED_ID, 'list_media', KT_USER_ACCESS_LEVEL))
	->setPageTitle(KT_I18N::translate('Media objects'))
	->pageHeader();

$search = KT_Filter::get('search');
$sortby = KT_Filter::get('sortby' , 'file|title' , 'title');
if (!KT_USER_CAN_EDIT && !KT_USER_CAN_ACCEPT) {
	$sortby='title';
}
$start          = KT_Filter::getInteger('start');
$max            = KT_Filter::get('max' , '10|20|30|40|50|75|100|125|150|200' , '20');
$folder         = KT_Filter::get('folder' , null, ''); // MySQL needs an empty string, not NULL
$reset          = KT_Filter::get('reset');
$apply_filter   = KT_Filter::get('apply_filter');
$filter         = KT_Filter::get('filter' , null, ''); // MySQL needs an empty string, not NULL
$subdirs        = KT_Filter::get('subdirs' , 'on');
$form_type      = KT_Filter::get('form_type');
$currentdironly = ($subdirs=='on') ? false : true;

// reset all variables
if ($reset == 'reset') {
	$sortby = 'title';
	$max = '20';
	$folder = '';
	$currentdironly = true;
	$filter = '';
	$form_type = '';
}

// A list of all subfolders used by this tree
$folders = KT_Query_Media::folderList();

// A list of all media objects matching the search criteria
$medialist = KT_Query_Media::mediaList(
	$folder,
	$currentdironly ? 'exclude' : 'include' ,
	$sortby,
	$filter,
	$form_type
);


?>
<div id="medialist-page">
	<h2><?php echo $controller->getPageTitle(); ?></h2>

	<form action="medialist.php" method="get">
		<input type="hidden" name="action" value="filter">
		<input type="hidden" name="search" value="yes">
		<div class="chart_options">
			<label for = "folder"><?php echo KT_I18N::translate('Folder'); ?></label>
			<?php echo select_edit_control('folder' , $folders, null, $folder); ?>
		</div>
		<div class="chart_options">
			<label for = "subdirs"><?php /* I18N: Label for check-box */ echo KT_I18N::translate('Include subfolders'); ?></label>
			<input type="checkbox" id="subdirs" name="subdirs" <?php if (!$currentdironly) { ?>checked="checked"<?php } ?>>
		</div>
		<div class="chart_options">
			<?php
				if (KT_USER_CAN_EDIT || KT_USER_CAN_ACCEPT) {
					echo '
						<label for = "folder">' . KT_I18N::translate('Sort order') . '</label>
						<select name="sortby" id="sortby">
							<option value="title" ' , ($sortby=='title') ? 'selected="selected"' : '' , '>' .
								/* I18N: An option in a list-box */ KT_I18N::translate('sort by title') . '
							</option>
							<option value="file" ' , ($sortby=='file') ? 'selected="selected"' : '' , '>' .
								/* I18N: An option in a list-box */ KT_I18N::translate('sort by filename') . '
							</option>
						</select>
					';
				}
			?>
		</div>
		<div class="chart_options">
			<label for="form-type"><?php echo KT_I18N::translate('Type'); ?></label>
			<select name="form_type" id="form-type">
				<option value=""></option>
				<option value="blank" <?php echo $form_type == 'blank' ? 'selected' : ''; ?>>
					<?php echo /* I18N: A filter on the media list for items with no TYPE tag set */ KT_I18N::translate('No type'); ?>
				</option>
				<?php foreach (KT_Gedcom_Tag::getFileFormTypes() as $value => $label): ?>
					<option value="<?php echo $value; ?>" <?php echo $value === $form_type ? 'selected' : ''; ?>>
						<?php echo $label; ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="chart_options">
			<label for = "max"><?php echo KT_I18N::translate('Media objects per page'); ?></label>
			<select name="max" id="max">
				<?php
				foreach (array('10' , '20' , '30' , '40' , '50' , '75' , '100' , '125' , '150' , '200') as $selectEntry) {
					echo '<option value="' , $selectEntry, '"';
					if ($selectEntry==$max) echo ' selected="selected"';
					echo '>' , $selectEntry, '</option>';
				}
				?>
			</select>
		</div>
		<div class="chart_options">
			<label for = "filter"><?php echo KT_I18N::translate('Search filters'); ?></label>
			<input id="filter" name="filter" value="<?php echo KT_Filter::escapeHtml($filter); ?>" size="14" dir="auto">
		</div>
		<p id="save-cancel">
			<button class="btn btn-primary" type="submit" name="apply_filter" value="apply_filter">
				<i class="fa fa-search"></i>
				<?php echo KT_I18N::translate('Search'); ?>
			</button>
			<button class="btn btn-primary" type="submit" name="reset"value="reset">
				<i class="fa fa-refresh"></i>
				<?php echo KT_I18N::translate('Reset'); ?>
			</button>
		</p>
	</form>
	<hr style="clear:both;">
	<!-- end of form -->

	<?php
	if ($search) {
		if (!empty($medialist)) {
			// Count the number of items in the medialist
			$ct = count($medialist);
			$start = 0;
			if (isset($_GET['start'])) $start = $_GET['start'];
			$count = $max;
			if ($start + $count > $ct) $count = $ct - $start;
		} else {
			$ct = '0';
		}

		if ($ct>0) {
			echo '<div class="media-list-results">';
				// Prepare pagination details
				$currentPage = ((int) ($start / $max)) + 1;
				$lastPage = (int) (($ct + $max - 1) / $max);

				$pagination = '<div class="pagination"><p class="alignleft">';
					if ($TEXT_DIRECTION=='ltr') {
						if ($ct>$max) {
							if ($currentPage > 1) {
								$pagination .= '<a href="medialist.php?action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&form_type=' . $form_type. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=0&amp;max=' . $max. '" class="icon-ldarrow"></a>';
							}
							if ($start>0) {
								$newstart = $start-$max;
								if ($start<0) $start = 0;
								$pagination .= '<a href="medialist.php?action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&form_type=' . $form_type. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=' . $newstart. '&amp;max=' . $max. '" class="icon-larrow"></a>';
							}
						}
					} else {
						if ($ct>$max) {
							if ($currentPage < $lastPage) {
								$lastStart = ((int) ($ct / $max)) * $max;
								$pagination .= '<a href="medialist.php?action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&form_type=' . $form_type. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=' . $lastStart. '&amp;max=' . $max. '" class="icon-rdarrow"></a>';
							}
							if ($start+$max < $ct) {
								$newstart = $start+$count;
								if ($start<0) $start = 0;
								$pagination .= '<a href="medialist.php?action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&form_type=' . $form_type. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=' . $newstart. '&amp;max=' . $max. '" class="icon-rarrow"></a>';
							}
						}
					}
				$pagination .= '</p>
					<p class="aligncenter">' . KT_I18N::translate('Page %s of %s' , $currentPage, $lastPage). '</p>
					<p class="alignright">';
						if ($TEXT_DIRECTION=='ltr') {
							if ($ct>$max) {
								if ($start+$max < $ct) {
									$newstart = $start+$count;
									if ($start<0) $start = 0;
									$pagination .= '<a href="medialist.php?action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&form_type=' . $form_type. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=' . $newstart. '&amp;max=' . $max. '" class="icon-rarrow"></a>';
								}
								if ($currentPage < $lastPage) {
									$lastStart = ((int) ($ct / $max)) * $max;
									$pagination .= '<a href="medialist.php?action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&form_type=' . $form_type. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=' . $lastStart. '&amp;max=' . $max. '" class="icon-rdarrow"></a>';
								}
							}
						} else {
							if ($ct>$max) {
								if ($start>0) {
									$newstart = $start-$max;
									if ($start<0) $start = 0;
									$pagination .= '<a href="medialist.php?action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&form_type=' . $form_type. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=' . $newstart. '&amp;max=' . $max. '" class="icon-larrow"></a>';
								}
								if ($currentPage > 1) {
									$lastStart = ((int) ($ct / $max)) * $max;
									$pagination .= '<a href="medialist.php?action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&form_type=' . $form_type. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=0&amp;max=' . $max. '" class="icon-ldarrow"></a>';
								}
							}
						}
				$pagination .= '</p></div>';

				// Output display
				echo '<h3>' , KT_I18N::translate('%s media objects found', $ct) , '</h3>';
				echo $pagination;
				echo '<div class="media-list-items">';
				// Start media loop
				for ($i=$start, $n=0; $i<$start+$count; ++$i) {
					$mediaobject = $medialist[$i];

					echo '<div class="media-list-item">';
						if (KT_USER_CAN_EDIT) {
							echo KT_Controller_Media::getMediaListMenu($mediaobject);
						}
						echo '<div class="media-list-image">';
							echo $mediaobject->displayImage();
						echo '</div>';
						echo '<div class="media-list-data">';
							// If sorting by title, highlight the title.  If sorting by filename, highlight the filename
							if ($sortby=='title') {
								echo '<p class="medialist_title"><a href="' , $mediaobject->getHtmlUrl(), '">';
								echo $mediaobject->getFullName();
								echo '</a></p>';
							} else {
								echo '<p><b><a href="' , $mediaobject->getHtmlUrl(), '">';
								echo basename($mediaobject->getFilename());
								echo '</a></b></p>';
								echo KT_Gedcom_Tag::getLabelValue('TITL' , $mediaobject->getFullName());
							}
							// Show file details
							if ($mediaobject->isExternal()) {
								echo KT_Gedcom_Tag::getLabelValue('URL' , $mediaobject->getFilename());
							} else {
								if ($mediaobject->fileExists()) {
									if (KT_USER_CAN_EDIT || KT_USER_CAN_ACCEPT) {
										echo KT_Gedcom_Tag::getLabelValue('FILE' , $mediaobject->getFilename());
									}
									echo KT_Gedcom_Tag::getLabelValue('FORM' , $mediaobject->mimeType());
									echo KT_Gedcom_Tag::getLabelValue('TYPE' , KT_Gedcom_Tag::getFileFormTypeValue($mediaobject->getMediaType()));
									switch ($mediaobject->isPrimary()) {
									case 'Y':
										echo KT_Gedcom_Tag::getLabelValue('_PRIM', KT_I18N::translate('yes'));
										break;
									case 'N':
										echo KT_Gedcom_Tag::getLabelValue('_PRIM', KT_I18N::translate('no'));
										break;
									}
									echo KT_Gedcom_Tag::getLabelValue('__FILE_SIZE__' , $mediaobject->getFilesize());
									$imgsize = $mediaobject->getImageAttributes();
									if ($imgsize['WxH']) {
										echo KT_Gedcom_Tag::getLabelValue('__IMAGE_SIZE__' , $imgsize['WxH']);
									}
								} else {
									echo '<p class="ui-state-error">' , /* I18N: %s is a filename */ KT_I18N::translate('The file “%s” does not exist.' , $mediaobject->getFilename()), '</p>';
								}
							}
							if (is_null(print_fact_sources($mediaobject->getGedcomRecord(), 1)) && is_null(print_fact_notes($mediaobject->getGedcomRecord(), 1)) ) {
							echo '<div class="media-list-sources" style="display:none">';
							} else {
							echo '<div class="media-list-sources">';
							}
								echo print_fact_sources($mediaobject->getGedcomRecord(), 1),
									print_fact_notes($mediaobject->getGedcomRecord(), 1), '
							</div>';
							foreach ($mediaobject->fetchLinkedIndividuals('OBJE') as $individual) {
								echo '<a class="media-list-link" href="' . $individual->getHtmlUrl() . '">' . KT_I18N::translate('View person') . ' — ' . $individual->getFullname().'</a><br>';
							}
							foreach ($mediaobject->fetchLinkedFamilies('OBJE') as $family) {
								echo '<a class="media-list-link" href="' . $family->getHtmlUrl() . '">' . KT_I18N::translate('View family') . ' — ' . $family->getFullname().'</a><br>';
							}
							foreach ($mediaobject->fetchLinkedSources('OBJE') as $source) {
								echo '<a class="media-list-link" href="' . $source->getHtmlUrl() . '">' . KT_I18N::translate('View source') . ' — ' . $source->getFullname().'</a><br>';
							}
						echo '</div>';
					echo '</div>';
				} // end media loop
				echo '</div>';
				echo $pagination;

			echo '</div>';
		}
	} ?>
</div>
