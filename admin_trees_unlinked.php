<?php
// Check a family tree for structural errors.
//
// Note that the tests and error messages are not yet finalised.  Wait until the code has stabilised before
// adding I18N.
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2002-2010 PGV Development Team.
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License or,
// at your discretion, any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
//
//$Id$

define('WT_SCRIPT_NAME', 'admin_trees_unlinked.php');

require './includes/session.php';
require WT_ROOT . 'includes/functions/functions_edit.php';
global $NOTE_ID_PREFIX, $REPO_ID_PREFIX;

$controller = new WT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(WT_I18N::translate('Find unlinked records'))
	->pageHeader()
	->addInlineJavascript('
		jQuery("#unlinked_accordion").accordion({heightStyle: "content", collapsible: true, active: false, header: "h3.drop"});
		jQuery("#unlinked_accordion").css("visibility", "visible");
	');

$action		= safe_GET('action');
$gedcom_id	= safe_GET('gedcom_id', array_keys(WT_Tree::getAll()), WT_GED_ID);

// the sql queries used to identify unlinked indis
$sql_INDI = "
	SELECT i_id
	FROM `##individuals`
	LEFT OUTER JOIN ##link
	 ON (##individuals.i_id = ##link.l_to AND ##individuals.i_file = ##link.l_file)
	 WHERE ##individuals.i_file = " . $gedcom_id . "
	 AND ##link.l_from IS NULL
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
?>
<div id="admin_unlinked">
	<h2><?php echo $controller->getPageTitle(); ?></h2>
	<div class="helpcontent">
		<?php echo /* I18N: Help text for the Find unlinked records tool. */ WT_I18N::translate('Produces lists of records that are not linked to any other records, such as individuals without parent or spouse family links. Includes lists for individuals, sources, repositories and shared notes. Does not include Families as a family record cannot exist without at least one family member.<br>
		<b>The definition of unlinked for each type of record is:</b><br>
		<ul><li>Individuals: a person who is not linked to any family, as a child or a spouse.</li>
		<li>Sources: a source record that is not used as a source for any record, fact, or event in the family tree.</li>
		<li>Repositories: a repository record that is not used as a repository for any source in the family tree.</li>
		<li>Notes: a shared note record that is not used as a note to any record, fact, or event in the family tree.</li>
		<li>Media: a media object that is not attached to any record, fact, or event in the family tree.</li><ul>'); ?>
	</div>
	<hr>
	<form method="get" name="unlinked_form" action="<?php echo WT_SCRIPT_NAME; ?>">
		<input type="hidden" name="action" value="view">
		<div id="unlinked_config">
			<label><?php echo WT_I18N::translate('Family tree'); ?></label>
			<select name="ged">
				<?php foreach (WT_Tree::getAll() as $tree) { ?>
					<option value="<?php echo $tree->tree_name_html; ?>"
					<?php if (empty($ged) && $tree->tree_id == WT_GED_ID || !empty($ged) && $ged == $tree->tree_name) { ?>
						 selected="selected"
					<?php } ?>
					 dir="auto"><?php echo $tree->tree_title_html; ?></option>
				<?php } ?>
			</select>
			<p>
				<button type="submit" class="btn btn-primary">
					<i class="fa fa-eye"></i>
					<?php echo WT_I18N::translate('View'); ?>
				</button>
			</p>
		</div>
	</form>
	<hr>
<?php
	// START OUTPUT
	if ($action == 'view') {
		$rows_INDI	= WT_DB::prepare($sql_INDI)->fetchAll(PDO::FETCH_ASSOC);
		$rows_SOUR	= WT_DB::prepare($sql_SOUR)->fetchAll(PDO::FETCH_ASSOC);
		$rows_NOTE	= WT_DB::prepare($sql_NOTE)->fetchAll(PDO::FETCH_ASSOC);
		$rows_REPO	= WT_DB::prepare($sql_REPO)->fetchAll(PDO::FETCH_ASSOC);
		$rows_MEDIA	= WT_DB::prepare($sql_MEDIA)->fetchAll(PDO::FETCH_ASSOC); ?>
		<div id="unlinked_accordion" style="visibility:hidden">
			<?php
			// -- Individuals --
			if ($rows_INDI) { ?>
				<h3 class="drop"><?php echo WT_I18N::plural('%s unlinked individual', '%s unlinked individuals', count($rows_INDI), count($rows_INDI)); ?></h3>
				<div>
					<?php foreach ($rows_INDI as $row) {
						$id = $row['i_id'];
						$record = WT_Person::getInstance($id);
						$fullname =  $record->getLifespanName(); ?>
						<a href="<?php echo $record->getHtmlUrl(); ?>" target="_blank" rel="noopener noreferrer"><?php echo $fullname; ?> (<?php echo $id; ?>)</a>
					<?php } ?>
				</div>
			<?php } else { ?>
				<h3 class="empty"><?php echo WT_I18N::translate('No unlinked individuals'); ?></h3>
			<?php }
			// -- Sources --
			if ($rows_SOUR) { ?>
				<h3 class="drop"><?php echo WT_I18N::plural('%s unlinked source', '%s unlinked sources', count($rows_SOUR), count($rows_SOUR)); ?></h3>
				<div>
					<?php foreach ($rows_SOUR as $row) {
						$id = $row['s_id'];
						$record = WT_Source::getInstance($id);
						$fullname =  $record->getFullName(); ?>
						<a href="<?php echo $record->getHtmlUrl(); ?>" target="_blank" rel="noopener noreferrer"><?php echo $fullname; ?> (<?php echo $id; ?>)</a>
						<?php } ?>
				</div>
			<?php } else { ?>
				<h3 class="empty"><?php echo WT_I18N::translate('No unlinked sources'); ?></h3>
			<?php }
			// -- Notes --
			if ($rows_NOTE) { ?>
				<h3 class="drop"><?php echo WT_I18N::plural('%s unlinked note', '%s unlinked notes', count($rows_NOTE), count($rows_NOTE)); ?></h3>
				<div>
					<?php foreach ($rows_NOTE as $row) {
						$id = $row['o_id'];
						$record = WT_Note::getInstance($id);
						$fullname =  $record->getFullName(); ?>
						<a href="<?php echo $record->getHtmlUrl(); ?>" target="_blank" rel="noopener noreferrer"><?php echo $fullname; ?> (<?php echo $id; ?>)</a>
						<?php } ?>
				</div>
			<?php } else { ?>
				<h3 class="empty"><?php echo WT_I18N::translate('No unlinked notes'); ?></h3>
			<?php }
			// -- Repositories --
			if ($rows_REPO) { ?>
				<h3 class="drop"><?php echo WT_I18N::plural('%s unlinked repository', '%s unlinked repositories', count($rows_REPO), count($rows_REPO)); ?></h3>
				<div>
					<?php foreach ($rows_REPO as $row) {
						$id = $row['o_id'];
						$record = WT_Repository::getInstance($id);
						$fullname =  $record->getFullName(); ?>
						<a href="<?php echo $record->getHtmlUrl(); ?>" target="_blank" rel="noopener noreferrer"><?php echo $fullname; ?> (<?php echo $id; ?>)</a>
						<?php } ?>
				</div>
			<?php } else { ?>
				<h3 class="empty"><?php echo WT_I18N::translate('No unlinked repositories'); ?></h3>
			<?php }
			// -- Media --
			if ($rows_MEDIA) { ?>
				<h3 class="drop"><?php echo WT_I18N::plural('%s unlinked media object', '%s unlinked media objects', count($rows_MEDIA), count($rows_MEDIA)); ?></h3>
				<div>
					<?php foreach ($rows_MEDIA as $row) {
						$id = $row['m_id'];
						$record = WT_Media::getInstance($id);
						$fullname =  $record->getFullName(); ?>
						<a href="<?php echo $record->getHtmlUrl(); ?>" target="_blank" rel="noopener noreferrer"><?php echo $fullname; ?> (<?php echo $id; ?>)</a>
						<?php } ?>
				</div>
			<?php } else { ?>
				<h3 class="empty"><?php echo WT_I18N::translate('No unlinked media objects'); ?></h3>
			<?php } ?>
		</div>
	<?php } ?>
</div>
