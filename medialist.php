<?php
// Displays a list of the media objects
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2015 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2010  PGV Development Team
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

define('WT_SCRIPT_NAME' , 'medialist.php');
require './includes/session.php';

require_once WT_ROOT.'includes/functions/functions_edit.php';
require_once WT_ROOT.'includes/functions/functions_print_facts.php';

$controller = new WT_Controller_Page();
$controller
	->setPageTitle(WT_I18N::translate('Media objects'))
	->pageHeader();

$search = WT_Filter::get('search');
$sortby = WT_Filter::get('sortby' , 'file|title' , 'title');
if (!WT_USER_CAN_EDIT && !WT_USER_CAN_ACCEPT) {
	$sortby='title';
}
$start          = WT_Filter::getInteger('start');
$max            = WT_Filter::get('max' , '10|20|30|40|50|75|100|125|150|200' , '20');
$folder         = WT_Filter::get('folder' , null, ''); // MySQL needs an empty string, not NULL
$reset          = WT_Filter::get('reset');
$apply_filter   = WT_Filter::get('apply_filter');
$filter         = WT_Filter::get('filter' , null, ''); // MySQL needs an empty string, not NULL
$subdirs        = WT_Filter::get('subdirs' , 'on');
$form_type      = WT_Filter::get('form_type' , implode('|' , array_keys(WT_Gedcom_Tag::getFileFormTypes())));
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
$folders = WT_Query_Media::folderList();

// A list of all media objects matching the search criteria
$medialist = WT_Query_Media::mediaList(
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
			<label for = "folder"><?php echo WT_I18N::translate('Folder'); ?></label>
			<?php echo select_edit_control('folder' , $folders, null, $folder); ?>
		</div>
		<div class="chart_options">
			<label for = "subdirs"><?php /* I18N: Label for check-box */ echo WT_I18N::translate('Include subfolders'); ?></label>
			<input type="checkbox" id="subdirs" name="subdirs" <?php if (!$currentdironly) { ?>checked="checked"<?php } ?>>
		</div>
		<div class="chart_options">
			<?php
				if (WT_USER_CAN_EDIT || WT_USER_CAN_ACCEPT) {
					echo '
						<label for = "folder">' . WT_I18N::translate('Sort order') . '</label>
						<select name="sortby" id="sortby">
							<option value="title" ' , ($sortby=='title') ? 'selected="selected"' : '' , '>' .
								/* I18N: An option in a list-box */ WT_I18N::translate('sort by title') . '
							</option>
							<option value="file" ' , ($sortby=='file') ? 'selected="selected"' : '' , '>' .
								/* I18N: An option in a list-box */ WT_I18N::translate('sort by filename') . '
							</option>
						</select>
					';
				}
			?>
		</div>
		<div class="chart_options">
			<label for="form-type"><?php echo WT_I18N::translate('Type'); ?></label>
			<select name="form_type" id="form-type">
				<option value=""></option>
				<?php foreach (WT_Gedcom_Tag::getFileFormTypes() as $value => $label): ?>
					<option value="<?php echo $value; ?>" <?php echo $value === $form_type ? 'selected' : ''; ?>>
						<?php echo $label; ?>
					</option>
				<?php endforeach; ?>
			</select>		
		</div>
		<div class="chart_options">
			<label for = "max"><?php echo WT_I18N::translate('Media objects per page'); ?></label>
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
			<label for = "filter"><?php echo WT_I18N::translate('Search filters'); ?></label>
			<input id="filter" name="filter" value="<?php echo WT_Filter::escapeHtml($filter); ?>" size="14" dir="auto">
		</div>
		<div class="btn btn-primary" style="display: inline-block;">
			<button type="submit" name="apply_filter" value="apply_filter"><?php echo WT_I18N::translate('Search'); ?></button>
			<button type="submit" name="reset"value="reset"><?php echo WT_I18N::translate('Reset'); ?></button>
		</div>
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
								$pagination .= '<a href="medialist.php?action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=0&amp;max=' . $max. '" class="icon-ldarrow"></a>';
							}
							if ($start>0) {
								$newstart = $start-$max;
								if ($start<0) $start = 0;
								$pagination .= '<a href="medialist.php?action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=' . $newstart. '&amp;max=' . $max. '" class="icon-larrow"></a>';
							}
						}
					} else {
						if ($ct>$max) {
							if ($currentPage < $lastPage) {
								$lastStart = ((int) ($ct / $max)) * $max;
								$pagination .= '<a href="medialist.php?action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=' . $lastStart. '&amp;max=' . $max. '" class="icon-rdarrow"></a>';
							}
							if ($start+$max < $ct) {
								$newstart = $start+$count;
								if ($start<0) $start = 0;
								$pagination .= '<a href="medialist.php?action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=' . $newstart. '&amp;max=' . $max. '" class="icon-rarrow"></a>';
							}
						}
					}
				$pagination .= '</p>
					<p class="aligncenter">' . WT_I18N::translate('Page %s of %s' , $currentPage, $lastPage). '</p>
					<p class="alignright">';
						if ($TEXT_DIRECTION=='ltr') {
							if ($ct>$max) {
								if ($start+$max < $ct) {
									$newstart = $start+$count;
									if ($start<0) $start = 0;
									$pagination .= '<a href="medialist.php?action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=' . $newstart. '&amp;max=' . $max. '" class="icon-rarrow"></a>';
								}
								if ($currentPage < $lastPage) {
									$lastStart = ((int) ($ct / $max)) * $max;
									$pagination .= '<a href="medialist.php?action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=' . $lastStart. '&amp;max=' . $max. '" class="icon-rdarrow"></a>';
								}
							}
						} else {
							if ($ct>$max) {
								if ($start>0) {
									$newstart = $start-$max;
									if ($start<0) $start = 0;
									$pagination .= '<a href="medialist.php?action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=' . $newstart. '&amp;max=' . $max. '" class="icon-larrow"></a>';
								}
								if ($currentPage > 1) {
									$lastStart = ((int) ($ct / $max)) * $max;
									$pagination .= '<a href="medialist.php?action=no&amp;search=no&amp;folder=' . rawurlencode($folder). '&amp;sortby=' . $sortby. '&amp;subdirs=' . $subdirs. '&amp;filter=' . rawurlencode($filter). '&amp;apply_filter=' . $apply_filter. '&amp;start=0&amp;max=' . $max. '" class="icon-ldarrow"></a>';
								}
							}
						}
				$pagination .= '</p></div>';

				// Output display
				echo '<h3>' , WT_I18N::translate('%s media objects found', $ct) , '</h3>';
				echo $pagination;
				echo '<div class="media-list-items">';
				// Start media loop
				for ($i=$start, $n=0; $i<$start+$count; ++$i) {
					$mediaobject = $medialist[$i];

					echo '<div class="media-list-item">';
						if (WT_USER_CAN_EDIT) {
							echo WT_Controller_Media::getMediaListMenu($mediaobject);
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
								echo WT_Gedcom_Tag::getLabelValue('TITL' , $mediaobject->getFullName());
							}
							// Show file details
							if ($mediaobject->isExternal()) {
								echo WT_Gedcom_Tag::getLabelValue('URL' , $mediaobject->getFilename());
							} else {
								if ($mediaobject->fileExists()) {
									if (WT_USER_CAN_EDIT || WT_USER_CAN_ACCEPT) {
										echo WT_Gedcom_Tag::getLabelValue('FILE' , $mediaobject->getFilename());
									}
									echo WT_Gedcom_Tag::getLabelValue('FORM' , $mediaobject->mimeType());
									echo WT_Gedcom_Tag::getLabelValue('TYPE' , $mediaobject->getMediaType());
									echo WT_Gedcom_Tag::getLabelValue('__FILE_SIZE__' , $mediaobject->getFilesize());
									$imgsize = $mediaobject->getImageAttributes();
									if ($imgsize['WxH']) {
										echo WT_Gedcom_Tag::getLabelValue('__IMAGE_SIZE__' , $imgsize['WxH']);
									}
								} else {
									echo '<p class="ui-state-error">' , /* I18N: %s is a filename */ WT_I18N::translate('The file “%s” does not exist.' , $mediaobject->getFilename()), '</p>';
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
								echo '<a class="media-list-link" href="' . $individual->getHtmlUrl() . '">' . WT_I18N::translate('View person') . ' — ' . $individual->getFullname().'</a><br>';
							}
							foreach ($mediaobject->fetchLinkedFamilies('OBJE') as $family) {
								echo '<a class="media-list-link" href="' . $family->getHtmlUrl() . '">' . WT_I18N::translate('View family') . ' — ' . $family->getFullname().'</a><br>';
							}
							foreach ($mediaobject->fetchLinkedSources('OBJE') as $source) {
								echo '<a class="media-list-link" href="' . $source->getHtmlUrl() . '">' . WT_I18N::translate('View source') . ' — ' . $source->getFullname().'</a><br>';
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