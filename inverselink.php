<?php
// Link media items to indi, sour and fam records
//
// This is the page that does the work of linking items.
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
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

define('WT_SCRIPT_NAME', 'inverselink.php');
require './includes/session.php';
require WT_ROOT.'includes/functions/functions_edit.php';

$controller = new WT_Controller_Page();
$controller
	->requireEditorLogin()
	->setPageTitle(WT_I18N::translate('Link to an existing media object'))
	->addExternalJavascript(WT_STATIC_URL . 'js/autocomplete.js')
	->addInlineJavascript('autocomplete();')
	->pageHeader();

//-- page parameters and checking
$linktoid = safe_GET_xref('linktoid');
$mediaid  = safe_GET_xref('mediaid');
$linkto   = safe_GET     ('linkto', array('person', 'source', 'family', 'manage', 'repository', 'note'));
$action   = safe_GET     ('action', WT_REGEX_ALPHA, 'choose');

// If GedFAct_assistant/_MEDIA/ installed ======================
if ($linkto=='manage' && array_key_exists('GEDFact_assistant', WT_Module::getActiveModules())) {
	require WT_ROOT.WT_MODULES_DIR.'GEDFact_assistant/_MEDIA/media_0_inverselink.php';
} else {

	//-- check for admin
	$paramok =  true;
	if (!empty($linktoid)) {
		$paramok = WT_GedcomRecord::getInstance($linktoid)->canDisplayDetails();
	}

	if ($action == "choose" && $paramok) { ?>
		<div id="inverselink-page">
			<form name="link" method="get" action="inverselink.php">
				<input type="hidden" name="action" value="update">
				<?php if (!empty($mediaid)) { ?>
					<input type="hidden" name="mediaid" value="<?php echo $mediaid; ?>">
				<?php }
				if (!empty($linktoid)) { ?>
					<input type="hidden" name="linktoid" value="<?php echo $linktoid; ?>">
				<?php } ?>
				<input type="hidden" name="linkto" value="<?php echo $linkto; ?>">
				<input type="hidden" name="ged" value="<?php echo $GEDCOM; ?>">
				<h2><?php echo WT_I18N::translate('Link to an existing media object'); ?></h2>
				<div id="add_facts">
					<div id="MEDIA_factdiv">
						<label><?php echo WT_I18N::translate('Media'); ?></label>
						<div class="input">
							<?php if (!empty($mediaid)) {
								//-- Get the title of this existing Media item
								$title=
									WT_DB::prepare("SELECT m_titl FROM `##media` where m_id=? AND m_file=?")
									->execute(array($mediaid, WT_GED_ID))
									->fetchOne();
								if ($title) { ?>
									<b><?php echo htmlspecialchars($title); ?></b>
								<?php } else { ?>
									<b><?php echo $mediaid; ?></b>
								<?php }
							} else { ?>
								<input data-autocomplete-type="OBJE" type="text" name="mediaid" id="mediaid" size="5">
								 <?php echo print_findmedia_link('mediaid', '1media');
							} ?>
						</div>
					</div>
					<?php if (!isset($linktoid)) {
						$linktoid = "";
					} ?>
					<?php if ($linkto == "person") { ?>
						<div id="INDI_factdiv">
							<label>
								<?php echo WT_I18N::translate('Individual'); ?>
							</label>
							<div class="input">
								<?php if ($linktoid == "") { ?>
									<input data-autocomplete-type="INDI" class="pedigree_form" type="text" name="linktoid" id="linktopid" size="3" value="<?php echo $linktoid; ?>">
									<?php echo print_findindi_link('linktopid');
								} else {
									$record = WT_Person::getInstance($linktoid);
									echo $record->format_list('span', false, $record->getFullName());
								} ?>
							</div>
						</div>
					<?php }
					if ($linkto == "family") { ?>
						<div id="FAM_factdiv">
							<label>
								<?php echo WT_I18N::translate('Family'); ?>
							</label>
							<div class="input">
								<?php if ($linktoid == "") { ?>
									<input data-autocomplete-type="FAM" class="pedigree_form" type="text" name="linktoid" id="linktofamid" size="3" value="<?php echo $linktoid; ?>">
									<?php echo print_findfamily_link('linktofamid');
								} else {
									$record = WT_Family::getInstance($linktoid);
									echo $record->format_list('span', false, $record->getFullName());
								} ?>
							</div>
						</div>
					<?php }
					if ($linkto == "source") { ?>
						<div id="SOUR_factdiv">
							<label>
								<?php echo WT_I18N::translate('Source'); ?>
							</label>
							<div class="input">
								<?php if ($linktoid == "") { ?>
									<input data-autocomplete-type="SOUR" class="pedigree_form" type="text" name="linktoid" id="linktosid" size="3" value="<?php echo $linktoid; ?>">
									<?php echo print_findsource_link('linktosid');
								} else {
									$record = WT_Source::getInstance($linktoid);
									echo $record->format_list('span', false, $record->getFullName());
								} ?>
							</div>
						</div>
					<?php }
					if ($linkto == "repository") { ?>
						<div id="REPO_factdiv">
							<label>
								<?php echo WT_I18N::translate('Repository'); ?>
							</label>
							<div class="input">
								<?php if ($linktoid == "") { ?>
									<input data-autocomplete-type="REPO" class="pedigree_form" type="text" name="linktoid" id="linktorid" size="3" value="<?php echo $linktoid; ?>">
								<?php } else {
									$record = WT_Repository::getInstance($linktoid);
									echo $record->format_list('span', false, $record->getFullName());
								} ?>
							</div>
						</div>
					<?php }
					if ($linkto == "note") { ?>
						<div id="NOTE_factdiv">
							<label>
								<?php echo WT_I18N::translate('Shared note'); ?>
							</label>
							<div class="input_group">
								<?php if ($linktoid == "") { ?>
									<input data-autocomplete-type="NOTE" class="pedigree_form" type="text" name="linktoid" id="linktonid" size="3" value="<?php echo $linktoid; ?>">
								<?php } else {
									$record = WT_Note::getInstance($linktoid);
									echo $record->format_list('span', false, $record->getFullName());
								} ?>
							</div>
						</div>
					<?php } ?>
					<p id="save-cancel">
						<button class="btn btn-primary" type="submit">
							<i class="fa fa-link"></i>
							<?php echo WT_I18N::translate('Set link'); ?>
						</button>
						<button class="btn btn-primary" type="button" onclick="closePopupAndReloadParent();">
							<i class="fa fa-times"></i>
							<?php echo WT_I18N::translate('close'); ?>
						</button>
					</p>
				</div>
			</form>
		</div>
	<?php
	} elseif ($action == "update" && $paramok) {
		linkMedia($mediaid, $linktoid);
		$controller->addInlineJavascript('closePopupAndReloadParent();');
	}
}
