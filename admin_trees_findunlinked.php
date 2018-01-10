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

define('KT_SCRIPT_NAME', 'admin_trees_findunlinked.php');

require './includes/session.php';
require KT_ROOT . 'includes/functions/functions_edit.php';
global $NOTE_ID_PREFIX, $REPO_ID_PREFIX;

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Find unlinked records'))
	->pageHeader()
	->addInlineJavascript('
		jQuery("#unlinked_accordion").accordion({heightStyle: "content", collapsible: true, active: 0, header: "h3.drop"});
		jQuery("#unlinked_accordion").css("visibility", "visible");
	');

$action		= KT_Filter::post('action');
$gedcom_id	= KT_Filter::post('gedcom_id', null, KT_GED_ID);
$records	= KT_Filter::postArray('records');
$list		= array(
				'Individuals',
				'Sources',
				'Notes',
				'Repositories',
				'Media'
			);

// the sql queries used to identify unlinked indis
$sql_INDI = "
	SELECT i_id
	FROM `##individuals`
	LEFT OUTER JOIN ##link
	 ON (##individuals.i_id = ##link.l_from AND ##individuals.i_file = ##link.l_file)
	 WHERE ##individuals.i_file = " . $gedcom_id . "
	 AND ##link.l_to IS NULL
";
$sql_SOUR = "
	SELECT s_id
	FROM `##sources`
	LEFT OUTER JOIN ##link
	 ON (##sources.s_id = ##link.l_to AND ##sources.s_file = ##link.l_file)
	 WHERE ##sources.s_file = " . $gedcom_id . "
	 AND ##link.l_from IS NULL
";
$sql_MEDIA = "
	SELECT m_id
	FROM `##media`
	LEFT OUTER JOIN ##link
	 ON (##media.m_id = ##link.l_to AND ##media.m_file = ##link.l_file)
	 WHERE ##media.m_file = " . $gedcom_id . "
	 AND ##link.l_from IS NULL
";
$sql_NOTE = "
	SELECT o_id
	FROM `##other`
	LEFT OUTER JOIN ##link
	 ON (##other.o_id = ##link.l_to AND ##other.o_file = ##link.l_file)
	 WHERE ##other.o_file = " . $gedcom_id . "
	 AND ##other.o_id LIKE '" . $NOTE_ID_PREFIX . "%'
	 AND ##link.l_from IS NULL
";
$sql_REPO = "
	SELECT o_id
	FROM `##other`
	LEFT OUTER JOIN ##link
	 ON (##other.o_id = ##link.l_to AND ##other.o_file = ##link.l_file)
	 WHERE ##other.o_file = " . $gedcom_id . "
	 AND ##other.o_id LIKE '" . $REPO_ID_PREFIX . "%'
	 AND ##link.l_from IS NULL
";

// Start of display
?>
<div id="admin_unlinked">
	<h2><?php echo $controller->getPageTitle(); ?></h2>
	<div class="helpcontent">
		<?php echo /* I18N: Help text for the Find unlinked records tool. */ KT_I18N::translate('List records that are not linked to any other records. It does not include Families as a family record cannot exist without at least one family member.<br>
		The definition of unlinked for each type of record is:
		<ul><li>Individuals: a person who is not linked to any family, as a child or a spouse.</li>
		<li>Sources: a source record that is not used as a source for any record, fact, or event in the family tree.</li>
		<li>Repositories: a repository record that is not used as a repository for any source in the family tree.</li>
		<li>Notes: a shared note record that is not used as a note to any record, fact, or event in the family tree.</li>
		<li>Media: a media object that is registered in the family tree but not attached to any record, fact, or event.</li><ul>'); ?>
	</div>
	<hr>
	<form method="post" name="unlinked_form" action="<?php echo KT_SCRIPT_NAME; ?>">
		<input type="hidden" name="action" value="view">
		<div id="unlinked_config">
			<label class="bold"><?php echo KT_I18N::translate('Family tree'); ?></label>
			<select name="ged">
				<?php foreach (KT_Tree::getAll() as $tree) { ?>
					<option value="<?php echo $tree->tree_name_html; ?>"
					<?php if (empty($ged) && $tree->tree_id == KT_GED_ID || !empty($ged) && $ged == $tree->tree_name) { ?>
						 selected="selected"
					<?php } ?>
					 dir="auto"><?php echo $tree->tree_title_html; ?></option>
				<?php } ?>
			</select>
			<div class="unlinked_type">
				<span>
					<label for "type" class="bold"><?php echo KT_I18N::translate('Select all'); ?></label>
					<input type="checkbox" id="type" onclick="toggle_select(this)" checked="checked">
				</span>
				<?php
				foreach ($list as $selected) { ?>
					<span>
						<input class="check" type="checkbox" name="records[]" id="record_<?php echo $selected; ?>"
							<?php if (($records && in_array($selected, $records)) || !$records) {
								echo ' checked="checked" ';
							} ?>
						value="<?php echo $selected; ?>">
						<label for="record_'<?php echo $selected; ?>"><?php echo KT_I18N::translate($selected); ?></label>
					</span>
				<?php }	?>
			</div>
			<p>
				<button type="submit" class="btn btn-primary">
					<i class="fa fa-eye"></i>
					<?php echo KT_I18N::translate('View'); ?>
				</button>
			</p>
		</div>
	</form>
	<hr>
<?php
	// START OUTPUT
	if ($action == 'view') { ?>
		<div id="unlinked_accordion" style="visibility:hidden">
			<?php
			if ($records) {
				// -- Individuals --
				if (in_array('Individuals', $records)) {
					$rows_INDI	= KT_DB::prepare($sql_INDI)->fetchAll(PDO::FETCH_ASSOC);
					if ($rows_INDI) { ?>
						<h3 class="drop"><?php echo KT_I18N::plural('%s unlinked individual', '%s unlinked individuals', count($rows_INDI), count($rows_INDI)); ?></h3>
						<div>
							<?php foreach ($rows_INDI as $row) {
								$id = $row['i_id'];
								$record = KT_Person::getInstance($id);
								$fullname =  $record->getLifespanName(); ?>
								<a href="<?php echo $record->getHtmlUrl(); ?>" target="_blank" rel="noopener noreferrer"><?php echo $fullname; ?><span class="id">(<?php echo $id; ?>)</span></a>
							<?php } ?>
						</div>
					<?php } else { ?>
						<h3 class="empty"><?php echo KT_I18N::translate('No unlinked individuals'); ?></h3>
					<?php }
				}
				// -- Sources --
				if (in_array('Sources', $records)) {
					$rows_SOUR	= KT_DB::prepare($sql_SOUR)->fetchAll(PDO::FETCH_ASSOC);
					if ($rows_SOUR) { ?>
						<h3 class="drop"><?php echo KT_I18N::plural('%s unlinked source', '%s unlinked sources', count($rows_SOUR), count($rows_SOUR)); ?></h3>
						<div>
							<?php foreach ($rows_SOUR as $row) {
								$id = $row['s_id'];
								$record = KT_Source::getInstance($id);
								$fullname =  $record->getFullName(); ?>
								<a href="<?php echo $record->getHtmlUrl(); ?>" target="_blank" rel="noopener noreferrer"><?php echo $fullname; ?><span class="id">(<?php echo $id; ?>)</span></a>
								<?php } ?>
						</div>
					<?php } else { ?>
						<h3 class="empty"><?php echo KT_I18N::translate('No unlinked sources'); ?></h3>
					<?php }
				}
				// -- Notes --
				if (in_array('Notes', $records)) {
					$rows_NOTE	= KT_DB::prepare($sql_NOTE)->fetchAll(PDO::FETCH_ASSOC);
					if ($rows_NOTE) { ?>
						<h3 class="drop"><?php echo KT_I18N::plural('%s unlinked note', '%s unlinked notes', count($rows_NOTE), count($rows_NOTE)); ?></h3>
						<div>
							<?php foreach ($rows_NOTE as $row) {
								$id = $row['o_id'];
								$record = KT_Note::getInstance($id);
								$fullname =  $record->getFullName(); ?>
								<a href="<?php echo $record->getHtmlUrl(); ?>" target="_blank" rel="noopener noreferrer"><?php echo $fullname; ?><span class="id">(<?php echo $id; ?>)</span></a>
								<?php } ?>
						</div>
					<?php } else { ?>
						<h3 class="empty"><?php echo KT_I18N::translate('No unlinked notes'); ?></h3>
					<?php }
				}
				// -- Repositories --
				if (in_array('Repositories', $records)) {
					$rows_REPO	= KT_DB::prepare($sql_REPO)->fetchAll(PDO::FETCH_ASSOC);
					if ($rows_REPO) { ?>
						<h3 class="drop"><?php echo KT_I18N::plural('%s unlinked repository', '%s unlinked repositories', count($rows_REPO), count($rows_REPO)); ?></h3>
						<div>
							<?php foreach ($rows_REPO as $row) {
								$id = $row['o_id'];
								$record = KT_Repository::getInstance($id);
								$fullname =  $record->getFullName(); ?>
								<a href="<?php echo $record->getHtmlUrl(); ?>" target="_blank" rel="noopener noreferrer"><?php echo $fullname; ?><span class="id">(<?php echo $id; ?>)</span></a>
								<?php } ?>
						</div>
					<?php } else { ?>
						<h3 class="empty"><?php echo KT_I18N::translate('No unlinked repositories'); ?></h3>
					<?php }
				}
				// -- Media --
				if (in_array('Media', $records)) {
					$rows_MEDIA	= KT_DB::prepare($sql_MEDIA)->fetchAll(PDO::FETCH_ASSOC);
					if ($rows_MEDIA) { ?>
						<h3 class="drop"><?php echo KT_I18N::plural('%s unlinked media object', '%s unlinked media objects', count($rows_MEDIA), count($rows_MEDIA)); ?></h3>
						<div>
							<?php foreach ($rows_MEDIA as $row) {
								$id = $row['m_id'];
								$record = KT_Media::getInstance($id);
								$fullname =  $record->getFullName(); ?>
								<a href="<?php echo $record->getHtmlUrl(); ?>" target="_blank" rel="noopener noreferrer"><?php echo $fullname; ?><span class="id">(<?php echo $id; ?>)</span></a>
								<?php } ?>
						</div>
					<?php } else { ?>
						<h3 class="empty"><?php echo KT_I18N::translate('No unlinked media objects'); ?></h3>
					<?php }
				}
			} else { ?>
				<h3 class="empty"><?php echo KT_I18N::translate('You must select at least one record type'); ?></h3>
			<?php } ?>
		</div>
	<?php } ?>
</div>
