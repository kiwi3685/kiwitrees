<?php
// UI for online updating of the GEDCOM config file.
//
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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

define('WT_SCRIPT_NAME', 'admin_trees_config.php');

require './includes/session.php';
require WT_ROOT.'includes/functions/functions_edit.php';

$controller = new WT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(WT_I18N::translate('Family tree configuration'))
	->addInlineJavascript('
		if(jQuery("#theme input:radio[id=radio_colors]").is(":checked")) {
			jQuery("#colors_palette").show();
		} else {
			jQuery("#colors_palette").hide();
		}
		jQuery("#theme input:radio[id^=radio_]").click(function(){
			var div = "#radio_" + jQuery(this).val();
			if (div == "#radio_colors") {
				jQuery("#colors_palette").show();
			} else {
				jQuery("#colors_palette").hide();
			}
		});
	');

$PRIVACY_CONSTANTS = array(
	'none'         => WT_I18N::translate('Show to visitors'),
	'privacy'      => WT_I18N::translate('Show to members'),
	'confidential' => WT_I18N::translate('Show to managers'),
	'hidden'       => WT_I18N::translate('Hide from everyone')
);

// List custom theme files that might exist
$custom_files = array(
		'mystyle.css',
		'mytheme.php',
		'myheader.php',
		'myfooter.php'
	);

switch (WT_Filter::post('action')) {
case 'delete':
	if (!WT_Filter::checkCsrf()) {
		break;
	}
	WT_DB::prepare(
		"DELETE FROM `##default_resn` WHERE default_resn_id=?"
	)->execute(array(WT_Filter::post('default_resn_id')));
	// Reload the page, so that the new privacy restrictions are reflected in the header
	header('Location: '.WT_SERVER_NAME.WT_SCRIPT_PATH.WT_SCRIPT_NAME.'#privacy');
	exit;
case 'add':
	if (!WT_Filter::checkCsrf()) {
		break;
	}
	if ((WT_Filter::post('xref') || WT_Filter::post('tag_type')) && WT_Filter::post('resn')) {
		if (WT_Filter::post('xref')=='') {
			WT_DB::prepare(
				"DELETE FROM `##default_resn` WHERE gedcom_id=? AND tag_type=? AND xref IS NULL"
			)->execute(array(WT_GED_ID, WT_Filter::post('tag_type')));
		}
		if (WT_Filter::post('tag_type')=='') {
			WT_DB::prepare(
				"DELETE FROM `##default_resn` WHERE gedcom_id=? AND xref=? AND tag_type IS NULL"
			)->execute(array(WT_GED_ID, WT_Filter::post('xref')));
		}
		WT_DB::prepare(
			"REPLACE INTO `##default_resn` (gedcom_id, xref, tag_type, resn) VALUES (?, NULLIF(?, ''), NULLIF(?, ''), ?)"
		)->execute(array(WT_GED_ID, WT_Filter::post('xref'), WT_Filter::post('tag_type'), WT_Filter::post('resn')));
	}
	// Reload the page, so that the new privacy restrictions are reflected in the header
	header('Location: '.WT_SERVER_NAME.WT_SCRIPT_PATH.WT_SCRIPT_NAME.'#privacy');
	exit;
case 'update':
	if (!WT_Filter::checkCsrf()) {
		break;
	}
	set_gedcom_setting(WT_GED_ID, 'ABBREVIATE_CHART_LABELS',      WT_Filter::postBool('NEW_ABBREVIATE_CHART_LABELS'));
	set_gedcom_setting(WT_GED_ID, 'ADVANCED_NAME_FACTS',          WT_Filter::post('NEW_ADVANCED_NAME_FACTS'));
	set_gedcom_setting(WT_GED_ID, 'ADVANCED_PLAC_FACTS',          WT_Filter::post('NEW_ADVANCED_PLAC_FACTS'));
	// For backwards compatibility with we store the two calendar formats in one variable
	// e.g. "gregorian_and_jewish"
	set_gedcom_setting(WT_GED_ID, 'CALENDAR_FORMAT',              implode('_and_', array_unique(array(
		WT_Filter::post('NEW_CALENDAR_FORMAT0', 'gregorian|julian|french|jewish|hijri|jalali', 'none'),
		WT_Filter::post('NEW_CALENDAR_FORMAT1', 'gregorian|julian|french|jewish|hijri|jalali', 'none')
	))));
	set_gedcom_setting(WT_GED_ID, 'ALL_CAPS',                     WT_Filter::postBool('NEW_ALL_CAPS'));
	set_gedcom_setting(WT_GED_ID, 'CHART_BOX_TAGS',               WT_Filter::post('NEW_CHART_BOX_TAGS'));
	set_gedcom_setting(WT_GED_ID, 'COMMON_NAMES_ADD',             str_replace(' ', '', WT_Filter::post('NEW_COMMON_NAMES_ADD')));
	set_gedcom_setting(WT_GED_ID, 'COMMON_NAMES_REMOVE',          str_replace(' ', '', WT_Filter::post('NEW_COMMON_NAMES_REMOVE')));
	set_gedcom_setting(WT_GED_ID, 'COMMON_NAMES_THRESHOLD',       WT_Filter::post('NEW_COMMON_NAMES_THRESHOLD', WT_REGEX_INTEGER, 40));
	set_gedcom_setting(WT_GED_ID, 'CONTACT_USER_ID',              WT_Filter::post('NEW_CONTACT_USER_ID'));
	set_gedcom_setting(WT_GED_ID, 'DEFAULT_PEDIGREE_GENERATIONS', WT_Filter::post('NEW_DEFAULT_PEDIGREE_GENERATIONS'));
	set_gedcom_setting(WT_GED_ID, 'EXPAND_NOTES',                 WT_Filter::postBool('NEW_EXPAND_NOTES'));
	set_gedcom_setting(WT_GED_ID, 'EXPAND_RELATIVES_EVENTS',      WT_Filter::postBool('NEW_EXPAND_RELATIVES_EVENTS'));
	set_gedcom_setting(WT_GED_ID, 'EXPAND_HISTO_EVENTS',      	  WT_Filter::postBool('NEW_EXPAND_HISTO_EVENTS'));
	set_gedcom_setting(WT_GED_ID, 'EXPAND_SOURCES',               WT_Filter::postBool('NEW_EXPAND_SOURCES'));
	set_gedcom_setting(WT_GED_ID, 'FAM_FACTS_ADD',                str_replace(' ', '', WT_Filter::post('NEW_FAM_FACTS_ADD')));
	set_gedcom_setting(WT_GED_ID, 'FAM_FACTS_QUICK',              str_replace(' ', '', WT_Filter::post('NEW_FAM_FACTS_QUICK')));
	set_gedcom_setting(WT_GED_ID, 'FAM_FACTS_UNIQUE',             str_replace(' ', '', WT_Filter::post('NEW_FAM_FACTS_UNIQUE')));
	set_gedcom_setting(WT_GED_ID, 'FAM_ID_PREFIX',                WT_Filter::post('NEW_FAM_ID_PREFIX'));
	set_gedcom_setting(WT_GED_ID, 'FULL_SOURCES',                 WT_Filter::postBool('NEW_FULL_SOURCES'));
	set_gedcom_setting(WT_GED_ID, 'GEDCOM_ID_PREFIX',             WT_Filter::post('NEW_GEDCOM_ID_PREFIX'));
	set_gedcom_setting(WT_GED_ID, 'GEDCOM_MEDIA_PATH',            WT_Filter::post('NEW_GEDCOM_MEDIA_PATH'));
	set_gedcom_setting(WT_GED_ID, 'GENERATE_UIDS',                WT_Filter::postBool('NEW_GENERATE_UIDS'));
	set_gedcom_setting(WT_GED_ID, 'HIDE_GEDCOM_ERRORS',           WT_Filter::postBool('NEW_HIDE_GEDCOM_ERRORS'));
	set_gedcom_setting(WT_GED_ID, 'HIDE_LIVE_PEOPLE',             WT_Filter::postBool('NEW_HIDE_LIVE_PEOPLE'));
	set_gedcom_setting(WT_GED_ID, 'INDI_FACTS_ADD',               str_replace(' ', '', WT_Filter::post('NEW_INDI_FACTS_ADD')));
	set_gedcom_setting(WT_GED_ID, 'INDI_FACTS_QUICK',             str_replace(' ', '', WT_Filter::post('NEW_INDI_FACTS_QUICK')));
	set_gedcom_setting(WT_GED_ID, 'INDI_FACTS_UNIQUE',            str_replace(' ', '', WT_Filter::post('NEW_INDI_FACTS_UNIQUE')));
	set_gedcom_setting(WT_GED_ID, 'KEEP_ALIVE_YEARS_BIRTH',       WT_Filter::post('KEEP_ALIVE_YEARS_BIRTH', WT_REGEX_INTEGER, 0));
	set_gedcom_setting(WT_GED_ID, 'KEEP_ALIVE_YEARS_DEATH',       WT_Filter::post('KEEP_ALIVE_YEARS_DEATH', WT_REGEX_INTEGER, 0));
	set_gedcom_setting(WT_GED_ID, 'LANGUAGE',                     WT_Filter::post('GEDCOMLANG'));
	set_gedcom_setting(WT_GED_ID, 'MAX_ALIVE_AGE',                WT_Filter::post('MAX_ALIVE_AGE', WT_REGEX_INTEGER, 100));
	set_gedcom_setting(WT_GED_ID, 'MAX_DESCENDANCY_GENERATIONS',  WT_Filter::post('NEW_MAX_DESCENDANCY_GENERATIONS'));
	set_gedcom_setting(WT_GED_ID, 'MAX_PEDIGREE_GENERATIONS',     WT_Filter::post('NEW_MAX_PEDIGREE_GENERATIONS'));
	set_gedcom_setting(WT_GED_ID, 'MEDIA_ID_PREFIX',              WT_Filter::post('NEW_MEDIA_ID_PREFIX'));
	set_gedcom_setting(WT_GED_ID, 'MEDIA_UPLOAD',                 WT_Filter::post('NEW_MEDIA_UPLOAD'));
	set_gedcom_setting(WT_GED_ID, 'META_DESCRIPTION',             WT_Filter::post('NEW_META_DESCRIPTION'));
	set_gedcom_setting(WT_GED_ID, 'META_TITLE',                   WT_Filter::post('NEW_META_TITLE'));
	set_gedcom_setting(WT_GED_ID, 'NOTE_ID_PREFIX',               WT_Filter::post('NEW_NOTE_ID_PREFIX'));
	set_gedcom_setting(WT_GED_ID, 'NO_UPDATE_CHAN',               WT_Filter::postBool('NEW_NO_UPDATE_CHAN'));
	set_gedcom_setting(WT_GED_ID, 'PEDIGREE_FULL_DETAILS',        WT_Filter::postBool('NEW_PEDIGREE_FULL_DETAILS'));
	set_gedcom_setting(WT_GED_ID, 'PEDIGREE_LAYOUT',              WT_Filter::postBool('NEW_PEDIGREE_LAYOUT'));
	set_gedcom_setting(WT_GED_ID, 'PEDIGREE_ROOT_ID',             WT_Filter::post('NEW_PEDIGREE_ROOT_ID', WT_REGEX_XREF));
	set_gedcom_setting(WT_GED_ID, 'PEDIGREE_SHOW_GENDER',         WT_Filter::postBool('NEW_PEDIGREE_SHOW_GENDER'));
	set_gedcom_setting(WT_GED_ID, 'PREFER_LEVEL2_SOURCES',        WT_Filter::post('NEW_PREFER_LEVEL2_SOURCES'));
	set_gedcom_setting(WT_GED_ID, 'QUICK_REQUIRED_FACTS',         WT_Filter::post('NEW_QUICK_REQUIRED_FACTS'));
	set_gedcom_setting(WT_GED_ID, 'QUICK_REQUIRED_FAMFACTS',      WT_Filter::post('NEW_QUICK_REQUIRED_FAMFACTS'));
	set_gedcom_setting(WT_GED_ID, 'REPO_FACTS_ADD',               str_replace(' ', '', WT_Filter::post('NEW_REPO_FACTS_ADD')));
	set_gedcom_setting(WT_GED_ID, 'REPO_FACTS_QUICK',             str_replace(' ', '', WT_Filter::post('NEW_REPO_FACTS_QUICK')));
	set_gedcom_setting(WT_GED_ID, 'REPO_FACTS_UNIQUE',            str_replace(' ', '', WT_Filter::post('NEW_REPO_FACTS_UNIQUE')));
	set_gedcom_setting(WT_GED_ID, 'REPO_ID_PREFIX',               WT_Filter::post('NEW_REPO_ID_PREFIX'));
	set_gedcom_setting(WT_GED_ID, 'REQUIRE_AUTHENTICATION',       WT_Filter::postBool('NEW_REQUIRE_AUTHENTICATION'));
	set_gedcom_setting(WT_GED_ID, 'SAVE_WATERMARK_IMAGE',         WT_Filter::postBool('NEW_SAVE_WATERMARK_IMAGE'));
	set_gedcom_setting(WT_GED_ID, 'SAVE_WATERMARK_THUMB',         WT_Filter::postBool('NEW_SAVE_WATERMARK_THUMB'));
	set_gedcom_setting(WT_GED_ID, 'SHOW_AGE_DIFF',                WT_Filter::postBool('NEW_SHOW_AGE_DIFF'));
	set_gedcom_setting(WT_GED_ID, 'SHOW_COUNTER',                 WT_Filter::postBool('NEW_SHOW_COUNTER'));
	set_gedcom_setting(WT_GED_ID, 'SHOW_DEAD_PEOPLE',             WT_Filter::post('SHOW_DEAD_PEOPLE'));
	set_gedcom_setting(WT_GED_ID, 'SHOW_EST_LIST_DATES',          WT_Filter::postBool('NEW_SHOW_EST_LIST_DATES'));
	set_gedcom_setting(WT_GED_ID, 'SHOW_FACT_ICONS',              WT_Filter::postBool('NEW_SHOW_FACT_ICONS'));
	set_gedcom_setting(WT_GED_ID, 'SHOW_GEDCOM_RECORD',           WT_Filter::postBool('NEW_SHOW_GEDCOM_RECORD'));
	set_gedcom_setting(WT_GED_ID, 'SHOW_HIGHLIGHT_IMAGES',        WT_Filter::postBool('NEW_SHOW_HIGHLIGHT_IMAGES'));
	set_gedcom_setting(WT_GED_ID, 'SHOW_LAST_CHANGE',             WT_Filter::postBool('NEW_SHOW_LAST_CHANGE'));
	set_gedcom_setting(WT_GED_ID, 'SHOW_LDS_AT_GLANCE',           WT_Filter::postBool('NEW_SHOW_LDS_AT_GLANCE'));
	set_gedcom_setting(WT_GED_ID, 'SHOW_LEVEL2_NOTES',            WT_Filter::postBool('NEW_SHOW_LEVEL2_NOTES'));
	set_gedcom_setting(WT_GED_ID, 'SHOW_LIVING_NAMES',            WT_Filter::post('SHOW_LIVING_NAMES'));
	set_gedcom_setting(WT_GED_ID, 'SHOW_MEDIA_DOWNLOAD',          WT_Filter::postBool('NEW_SHOW_MEDIA_DOWNLOAD'));
	set_gedcom_setting(WT_GED_ID, 'SHOW_NO_WATERMARK',            WT_Filter::post('NEW_SHOW_NO_WATERMARK'));
	set_gedcom_setting(WT_GED_ID, 'SHOW_PARENTS_AGE',             WT_Filter::postBool('NEW_SHOW_PARENTS_AGE'));
	set_gedcom_setting(WT_GED_ID, 'SHOW_PEDIGREE_PLACES',         WT_Filter::post('NEW_SHOW_PEDIGREE_PLACES'));
	set_gedcom_setting(WT_GED_ID, 'SHOW_PEDIGREE_PLACES_SUFFIX',  WT_Filter::postBool('NEW_SHOW_PEDIGREE_PLACES_SUFFIX'));
	set_gedcom_setting(WT_GED_ID, 'SHOW_PRIVATE_RELATIONSHIPS',   WT_Filter::post('SHOW_PRIVATE_RELATIONSHIPS'));
	set_gedcom_setting(WT_GED_ID, 'SHOW_RELATIVES_EVENTS',        WT_Filter::post('NEW_SHOW_RELATIVES_EVENTS'));
	set_gedcom_setting(WT_GED_ID, 'SHOW_STATS',                   WT_Filter::postBool('NEW_SHOW_STATS'));
	set_gedcom_setting(WT_GED_ID, 'SOURCE_ID_PREFIX',             WT_Filter::post('NEW_SOURCE_ID_PREFIX'));
	set_gedcom_setting(WT_GED_ID, 'SOUR_FACTS_ADD',               str_replace(' ', '', WT_Filter::post('NEW_SOUR_FACTS_ADD')));
	set_gedcom_setting(WT_GED_ID, 'SOUR_FACTS_QUICK',             str_replace(' ', '', WT_Filter::post('NEW_SOUR_FACTS_QUICK')));
	set_gedcom_setting(WT_GED_ID, 'SOUR_FACTS_UNIQUE',            str_replace(' ', '', WT_Filter::post('NEW_SOUR_FACTS_UNIQUE')));
	set_gedcom_setting(WT_GED_ID, 'SUBLIST_TRIGGER_I',            WT_Filter::post('NEW_SUBLIST_TRIGGER_I', WT_REGEX_INTEGER, 200));
	set_gedcom_setting(WT_GED_ID, 'SURNAME_LIST_STYLE',           WT_Filter::post('NEW_SURNAME_LIST_STYLE'));
	set_gedcom_setting(WT_GED_ID, 'SURNAME_TRADITION',            WT_Filter::post('NEW_SURNAME_TRADITION'));
	set_gedcom_setting(WT_GED_ID, 'THEME_DIR',                    WT_Filter::post('NEW_THEME_DIR'));
	set_gedcom_setting(WT_GED_ID, 'COLOR_PALETTE',                WT_Filter::post('NEW_COLOR_PALETTE'));
	set_gedcom_setting(WT_GED_ID, 'THUMBNAIL_WIDTH',              WT_Filter::post('NEW_THUMBNAIL_WIDTH'));
	set_gedcom_setting(WT_GED_ID, 'USE_GEONAMES',                 WT_Filter::postBool('NEW_USE_GEONAMES'));
	set_gedcom_setting(WT_GED_ID, 'USE_RIN',                      WT_Filter::postBool('NEW_USE_RIN'));
	set_gedcom_setting(WT_GED_ID, 'USE_SILHOUETTE',               WT_Filter::postBool('NEW_USE_SILHOUETTE'));
	set_gedcom_setting(WT_GED_ID, 'WATERMARK_THUMB',              WT_Filter::postBool('NEW_WATERMARK_THUMB'));
	set_gedcom_setting(WT_GED_ID, 'WEBMASTER_USER_ID',            WT_Filter::post('NEW_WEBMASTER_USER_ID'));
	set_gedcom_setting(WT_GED_ID, 'WEBTREES_EMAIL',               WT_Filter::post('NEW_WEBTREES_EMAIL'));
	set_gedcom_setting(WT_GED_ID, 'subtitle',               	  WT_Filter::post('new_subtitle', WT_REGEX_UNSAFE));
	if (WT_Filter::post('gedcom_title', WT_REGEX_UNSAFE)) {
		set_gedcom_setting(WT_GED_ID, 'title',                    WT_Filter::post('gedcom_title', WT_REGEX_UNSAFE));
	}

	// Only accept valid folders for NEW_MEDIA_DIRECTORY
	$NEW_MEDIA_DIRECTORY = preg_replace('/[\/\\\\]+/', '/', WT_Filter::post('NEW_MEDIA_DIRECTORY') . '/');
	if (substr($NEW_MEDIA_DIRECTORY, 0, 1) == '/') {
		$NEW_MEDIA_DIRECTORY = substr($NEW_MEDIA_DIRECTORY, 1);
	}

	if ($NEW_MEDIA_DIRECTORY) {
		if (is_dir(WT_DATA_DIR . $NEW_MEDIA_DIRECTORY)) {
			set_gedcom_setting(WT_GED_ID, 'MEDIA_DIRECTORY', $NEW_MEDIA_DIRECTORY);
		} elseif (@mkdir(WT_DATA_DIR . $NEW_MEDIA_DIRECTORY, 0755, true)) {
			set_gedcom_setting(WT_GED_ID, 'MEDIA_DIRECTORY', $NEW_MEDIA_DIRECTORY);
			WT_FlashMessages::addMessage(WT_I18N::translate('The folder %s was created.', WT_DATA_DIR . $NEW_MEDIA_DIRECTORY));
		} else {
			WT_FlashMessages::addMessage(WT_I18N::translate('The folder %s does not exist, and it could not be created.', WT_DATA_DIR . $NEW_MEDIA_DIRECTORY));
		}
	}

	$gedcom = WT_Filter::post('gedcom');
	if ($gedcom && $gedcom != WT_GEDCOM) {
		try {
			WT_DB::prepare("UPDATE `##gedcom` SET gedcom_name = ? WHERE gedcom_id = ?")->execute(array($gedcom, WT_GED_ID));
			WT_DB::prepare("UPDATE `##site_setting` SET setting_value = ? WHERE setting_name='DEFAULT_GEDCOM' AND setting_value = ?")->execute(array($gedcom, WT_GEDCOM));
		} catch (Exception $ex) {
			// Probably a duplicate name.
			$gedcom = WT_GEDCOM;
		}
	}

	// Reload the page, so that the settings take effect immediately.
	Zend_Session::writeClose();
	header('Location: ' . WT_SERVER_NAME . WT_SCRIPT_PATH . WT_SCRIPT_NAME . '?ged=' . $gedcom);
	exit;
}

$controller
	->pageHeader()
	->addExternalJavascript(WT_STATIC_URL.'js/autocomplete.js')
	->addInlineJavascript('autocomplete();')
	->addInlineJavascript('
    	// run test on initial page load
	    checkSize();
	    // run test on resize of the window
	    jQuery(window).resize(checkSize);
		//Function to the css rule
		function checkSize(){
		    if (jQuery("h3.accordion").css("display") == "block" ){
				jQuery("#accordion").accordion({event: "click", collapsible: true, heightStyle: "content"});
		    } else {
				jQuery("#tabs").tabs();
			}
		}
		if(jQuery("input[name=\'NEW_WATERMARK_THUMB\']:checked").val() != 1){
			 jQuery("#watermarks").hide()
		 }
		 jQuery("input[name=\'NEW_WATERMARK_THUMB\']").on("change",function(){
			 var showOrHide = (jQuery(this).val() == 1) ? true : false;
			 jQuery("#watermarks").toggle(showOrHide);
		 })
	');

?>
<div id="family_tree_config">
	<h2><?php echo WT_I18N::translate('Family tree configuration'); ?></h2>
	<form enctype="multipart/form-data" method="post" id="configform" name="configform" action="<?php echo WT_SCRIPT_NAME; ?>">
		<?php echo WT_Filter::getCsrf(); ?>
		<input type="hidden" name="action" value="update">
		<input type="hidden" name="ged" value="<?php echo htmlspecialchars(WT_GEDCOM); ?>">

		<div id="tabs">
			<ul>
				<li><a href="#file-options"><span><?php echo WT_I18N::translate('General'); ?></span></a></li>
				<li><a href="#contact"><span><?php echo WT_I18N::translate('Contact information'); ?></span></a></li>
				<li><a href="#website"><span><?php echo WT_I18N::translate('Website'); ?></span></a></li>
				<li><a href="#privacy"><span><?php echo WT_I18N::translate('Privacy'); ?></span></a></li>
				<li><a href="#config-media"><span><?php echo WT_I18N::translate('Media'); ?></span></a></li>
				<li><a href="#layout-options"><span><?php echo WT_I18N::translate('Layout'); ?></span></a></li>
				<li><a href="#hide-show"><span><?php echo WT_I18N::translate('Hide &amp; show'); ?></span></a></li>
				<li><a href="#edit-options"><span><?php echo WT_I18N::translate('Edit options'); ?></span></a></li>
				<li><a href="#theme"><span><?php echo WT_I18N::translate('Theme'); ?></span></a></li>
			</ul>
			<div id="accordion">
				<!-- GENERAL -->
				<h3 class="accordion"><?php echo WT_I18N::translate('General'); ?></h3>
				<div id="file-options">
					<div class="tree_config odd">
						<label><?php echo WT_I18N::translate('Family tree title'); ?></label>
						<div class="input_group">
							<input type="text" name="gedcom_title" dir="ltr" value="<?php echo WT_Filter::escapeHtml(get_gedcom_setting(WT_GED_ID, 'title')); ?>" required maxlength="255">
						</div>
					</div>
					<div class="tree_config even">
						<label><?php echo WT_I18N::translate('Family tree subtitle'); ?></label>
						<div class="input_group">
							<input type="text" name="new_subtitle"dir="ltr" value="<?php echo WT_Filter::escapeHtml(get_gedcom_setting(WT_GED_ID, 'subtitle')); ?>" maxlength="255">
						</div>
					</div>
					<div class="tree_config odd">
						<label><?php echo WT_I18N::translate('URL'); ?></label>
						<div class="input_group">
							<span class="input_label left"><?php echo WT_SERVER_NAME, WT_SCRIPT_PATH ?>index.php?ged=</span>
							<input type="text" name="gedcom" dir="ltr" value="<?php echo WT_Filter::escapeHtml(WT_GEDCOM); ?>" required maxlength="255">
							<span class="help_content">
								<?php /*I18N: Help text for family tree URL */ echo WT_I18N::translate('Avoid spaces and punctuation. A family name might be a good choice.'); ?>
							</span>
						</div>
					</div>
					<div class="tree_config even">
						<label><?php echo WT_I18N::translate('Language'); ?></label>
						<div class="input_group">
							<?php echo edit_field_language('GEDCOMLANG', $LANGUAGE); ?>
							<span class="help_content">
								<?php echo WT_I18N::translate('If a visitor to the site has not specified a preferred language in their browser configuration, or they have specified an unsupported language, then this language will be used. Typically, this setting applies to search engines.'); ?>
							</span>
						</div>
					</div>
					<div class="tree_config odd">
						<label> <?php echo WT_I18N::translate('Default individual'); ?></label>
						<div class="input_group">
							<input data-autocomplete-type="INDI" type="text" dir="ltr" name="NEW_PEDIGREE_ROOT_ID" id="NEW_PEDIGREE_ROOT_ID" value="<?php echo get_gedcom_setting(WT_GED_ID, 'PEDIGREE_ROOT_ID'); ?>" maxlength="20">
							<span class="input_label right">
								<?php
								$person = WT_Person::getInstance(get_gedcom_setting(WT_GED_ID, 'PEDIGREE_ROOT_ID'));
								if ($person) {
									echo ' <span class="list_item">' . $person->getFullName() . ' ' . $person->getLifeSpan() . '</span>';
								} else {
									echo ' <span class="error">' . WT_I18N::translate('Unable to find record with ID') . '</span>';
								}
								?>
							</span>
							<span class="help_content">
								<?php echo WT_I18N::translate('This individual will be selected by default when viewing charts and reports.'); ?>
							</span>
						</div>
					</div>
					<div class="tree_config even">
						<label><?php echo WT_I18N::translate('Calendar conversion'); ?></label>
						<div class="input_group">
							<div class="sub_input_group">
								<select id="NEW_CALENDAR_FORMAT0" name="NEW_CALENDAR_FORMAT0">
									<?php
									$CALENDAR_FORMATS=explode('_and_', $CALENDAR_FORMAT);
									if (count($CALENDAR_FORMATS)==1) {
										$CALENDAR_FORMATS[]='none';
									}
									foreach (array(
										'none'     =>WT_I18N::translate('No calendar conversion'),
										'gregorian'=>WT_Date_Gregorian::calendarName(),
										'julian'   =>WT_Date_Julian::calendarName(),
										'french'   =>WT_Date_French::calendarName(),
										'jewish'   =>WT_Date_Jewish::calendarName(),
										'hijri'    =>WT_Date_Hijri::calendarName(),
										'jalali'   =>WT_Date_Jalali::calendarName(),
									) as $cal=>$name) {
										echo '<option value="', $cal, '"';
										if ($CALENDAR_FORMATS[0]==$cal) {
											echo ' selected="selected"';
										}
										echo '>', $name, '</option>';
									}
									?>
								</select>
								<select id="NEW_CALENDAR_FORMAT1" name="NEW_CALENDAR_FORMAT1">
									<?php
									foreach (array(
										'none'     =>WT_I18N::translate('No calendar conversion'),
										'gregorian'=>WT_Date_Gregorian::calendarName(),
										'julian'   =>WT_Date_Julian::calendarName(),
										'french'   =>WT_Date_French::calendarName(),
										'jewish'   =>WT_Date_Jewish::calendarName(),
										'hijri'    =>WT_Date_Hijri::calendarName(),
										'jalali'   =>WT_Date_Jalali::calendarName(),
									) as $cal=>$name) {
										echo '<option value="', $cal, '"';
										if ($CALENDAR_FORMATS[1]==$cal) {
											echo ' selected="selected"';
										}
										echo '>', $name, '</option>';
									}
									?>
								</select>
							</div>
							<span class="help_content">
								<?php
								$d1 = new WT_Date('22 SEP 1792'); $d1=$d1->Display(false, null, array());
								$d2 = new WT_Date('31 DEC 1805'); $d2=$d2->Display(false, null, array());
								$d3 = new WT_Date('15 OCT 1582'); $d3=$d3->Display(false, null, array());

								echo WT_I18N::translate('Different calendar systems are used in different parts of the world, and many other calendar systems have been used in the past. Where possible, you should enter dates using the calendar in which the event was originally recorded. You can then specify a conversion to show these dates in a more familiar calendar. If you regularly use two calendars you can specify two conversions and dates will be converted to both the selected calendars.'); ?>
								 <a href="#" class="more accepted"><?php echo /* I18N: Click this text to read or hide more information */ WT_I18N::translate('More / Less ....'); ?></a>
								<div class="hidden" style="display: none;">
									<h3><?php echo WT_I18N::translate('The following calendars are supported:'); ?></h3>
									<ul>
										<li><?php echo WT_Date_Gregorian::calendarName(); ?></li>
										<li><?php echo WT_Date_Julian::calendarName(); ?></li>
										<li><?php echo WT_Date_Jewish::calendarName(); ?></li>
										<li><?php echo WT_Date_French::calendarName(); ?></li>
										<li><?php echo WT_Date_Hijri::calendarName(); ?></li>
										<li><?php echo WT_Date_Jalali::calendarName(); ?></li>
									</ul>
									<p>
										<?php echo /* I18N: The three place holders are all dates. */ WT_I18N::translate('Dates are only converted if they are valid for the calendar. For example, only dates between %1$s and %2$s will be converted to the French calendar and only dates after %3$s will be converted to the Gregorian calendar.', $d1, $d2, $d3); ?>
									</p>
									<p>
										<?php echo WT_I18N::translate('In some calendars, days start at midnight. In other calendars, days start at sunset. The conversion process does not take account of the time, so for any event that occurs between sunset and midnight, the conversion between these types of calendar will be one day out.'); ?>
									</p>
								</div>
							</span>
						</div>
					</div>
					<div class="tree_config odd">
						<label><?php echo WT_I18N::translate('Use RIN number instead of GEDCOM ID'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_USE_RIN', get_gedcom_setting(WT_GED_ID, 'USE_RIN')); ?>
							<span class="help_content">
								<?php echo WT_I18N::translate('Set to <b>Yes</b> to use the RIN number instead of the GEDCOM ID when asked for Individual IDs in configuration files, user settings, and charts. This is useful for genealogy programs that do not consistently export GEDCOMs with the same ID assigned to each individual but always use the same RIN.'); ?>
							</span>
						</div>
					</div>
					<div class="tree_config even">
						<label><?php echo WT_I18N::translate('Automatically create globally unique IDs'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_GENERATE_UIDS', get_gedcom_setting(WT_GED_ID, 'GENERATE_UIDS')); ?>
							<span class="help_content">
								<?php echo WT_I18N::translate('<b>GUID</b> in this context is an acronym for «Globally Unique ID».'); ?>
								 <a href="#" class="more accepted"><?php echo /* I18N: Click this text to read or hide more information */ WT_I18N::translate('More / Less ....'); ?></a>
								<div class="hidden" style="display: none;">
									<?php echo WT_I18N::translate('GUIDs are intended to help identify each individual in a manner that is repeatable, so that central organizations such as the Family History Center of the LDS Church in Salt Lake City, or even compatible programs running on your own server, can determine whether they are dealing with the same person no matter where the GEDCOM originates.  The goal of the Family History Center is to have a central repository of genealogical data and expose it through web services. This will enable any program to access the data and update their data within it.<br><br>If you do not intend to share this GEDCOM with anyone else, you do not need to let kiwitrees create these GUIDs; however, doing so will do no harm other than increasing the size of your GEDCOM.'); ?>
								</div>
							</span>
						</div>
					</div>
					<div class="tree_config odd">
						<label><?php echo WT_I18N::translate('XREF prefixes'); ?></label>
						<div class="input_group">
							<div class="sm_input_group">
								<span class="input_label"><?php echo WT_I18N::translate('Individual'); ?></span>
								<input type="text" name="NEW_GEDCOM_ID_PREFIX" dir="ltr" value="<?php echo $GEDCOM_ID_PREFIX; ?>" size="5" maxlength="20">
							</div>
							<div class="sm_input_group">
								<span class="input_label"><?php echo WT_I18N::translate('Family'); ?></span>
								<input type="text" name="NEW_FAM_ID_PREFIX" dir="ltr" value="<?php echo $FAM_ID_PREFIX; ?>" size="5" maxlength="20">
							</div>
							<div class="sm_input_group">
								<span class="input_label"><?php echo WT_I18N::translate('Source'); ?></span>
								<input type="text" name="NEW_SOURCE_ID_PREFIX" dir="ltr" value="<?php echo $SOURCE_ID_PREFIX; ?>" size="5" maxlength="20">
							</div>
							<div class="sm_input_group">
								<span class="input_label"><?php echo WT_I18N::translate('Repository'); ?></span>
								<input type="text" name="NEW_REPO_ID_PREFIX" dir="ltr" value="<?php echo $REPO_ID_PREFIX; ?>" size="5" maxlength="20">
							</div>
							<div class="sm_input_group">
								<span class="input_label"><?php echo WT_I18N::translate('Media'); ?></span>
								<input type="text" name="NEW_MEDIA_ID_PREFIX" dir="ltr" value="<?php echo $MEDIA_ID_PREFIX; ?>" size="5" maxlength="20">
							</div>
							<div class="sm_input_group">
								<span class="input_label"><?php echo WT_I18N::translate('Note'); ?></span>
								<input type="text" name="NEW_NOTE_ID_PREFIX" dir="ltr" value="<?php echo $NOTE_ID_PREFIX; ?>" size="5" maxlength="20">
							</div>
							<span class="help_content">
								<?php echo WT_I18N::translate('In a family tree, each record has an internal reference number (called an “XREF”) such as “F123” or “R14”.	You can choose the prefix that will be used whenever <b>new</b> XREFs are created.'); ?>
							</span>
						</div>
					</div>
				</div>
				<!-- CONTACT -->
				<h3 class="accordion"><?php echo WT_I18N::translate('Contact information'); ?></h3>
				<div id="contact">
					<div class="tree_config odd">
						<?php if (empty($WEBTREES_EMAIL)) {
							$WEBTREES_EMAIL = "kiwitrees-noreply@".preg_replace("/^www\./i", "", $_SERVER["SERVER_NAME"]);
						} ?>
						<label><?php echo WT_I18N::translate('Kiwitrees reply address'); ?></label>
						<div class="input_group">
							<input type="text" name="NEW_WEBTREES_EMAIL" required value="<?php echo $WEBTREES_EMAIL; ?>" size="50" maxlength="255" dir="ltr">
							<span class="help_content">
								<?php echo WT_I18N::translate('Email address to be used in the “From:” field of emails that kiwitrees creates automatically.'); ?>
								 <a href="#" class="more accepted"><?php echo /* I18N: Click this text to read or hide more information */ WT_I18N::translate('More / Less ....'); ?></a>
								<div class="hidden" style="display: none;">
									<?php echo WT_I18N::translate('Kiwitrees can automatically create emails to notify administrators of changes that need to be reviewed. webtrees also sends notification emails to users who have requested an account.<br><br>Usually, the “From:” field of these automatically created emails is something like From: kiwitrees-noreply@yoursite to show that no response to the email is required. To guard against spam or other email abuse, some email systems require each message’s “From:” field to reflect a valid email account and will not accept messages that are apparently from account kiwitrees-noreply.'); ?>
								</div>
							</span>
						</div>
					</div>
					<div class="tree_config even">
						<label><?php echo WT_I18N::translate('Genealogy contact'); ?></label>
						<div class="input_group">
							<select name="NEW_CONTACT_USER_ID">
								<?php $CONTACT_USER_ID = get_gedcom_setting(WT_GED_ID, 'CONTACT_USER_ID');
								echo '<option value="" ';
									if ($CONTACT_USER_ID == '') echo ' selected="selected"';
								echo '>'. WT_I18N::translate('none'), '</option>';
								foreach (get_all_users() as $user_id=>$user_name) {
									if (get_user_setting($user_id, 'verified_by_admin')) {
										echo '<option value="' . $user_id . '"';
										if ($CONTACT_USER_ID == $user_id) echo ' selected="selected"';
										echo '>' . getUserFullName($user_id) . ' - ' . $user_name . '</option>';
									}
								} ?>
							</select>
							<span class="help_content">
								<?php echo WT_I18N::translate('The person to contact about the genealogical data on this site.'); ?>
							</span>
						</div>
					</div>
					<div class="tree_config odd">
						<label><?php echo WT_I18N::translate('Technical help contact'); ?></label>
						<div class="input_group">
							<select name="NEW_WEBMASTER_USER_ID">
							<?php
								$WEBMASTER_USER_ID=get_gedcom_setting(WT_GED_ID, 'WEBMASTER_USER_ID');
								echo '<option value="" ';
								if ($WEBMASTER_USER_ID=='') echo ' selected="selected"';
								echo '>'. WT_I18N::translate('none'), '</option>';
								foreach (get_all_users() as $user_id=>$user_name) {
									if (userIsAdmin($user_id)) {
										echo '<option value="'.$user_id.' "';
										if ($WEBMASTER_USER_ID==$user_id) echo ' selected="selected"';
										echo '>'.getUserFullName($user_id).' - '.$user_name.'</option>';
									}
								}
							?>
							</select>
							<span class="help_content">
								<?php echo WT_I18N::translate('The person to be contacted about technical questions or errors encountered on your site.'); ?>
							</span>
						</div>
					</div>
				</div>
				<!-- WEBSITE -->
				<h3 class="accordion"><?php echo WT_I18N::translate('Website'); ?></h3>
				<div id="website">
					<div class="tree_config odd">
						<label><?php echo WT_I18N::translate('Add to TITLE header tag'); ?></label>
						<div class="input_group">
							<input type="text" dir="ltr" name="NEW_META_TITLE" value="<?php echo htmlspecialchars(get_gedcom_setting(WT_GED_ID, 'META_TITLE')); ?>" size="40" maxlength="255">
							<span class="help_content">
								<?php echo WT_I18N::translate('This text will be appended to each page title. It will be shown in the browser’s title bar, bookmarks, etc.'); ?>
							</span>
						</div>
					</div>
					<div class="tree_config even">
						<label><?php echo WT_I18N::translate('Description META tag'); ?></label>
						<div class="input_group">
							<input type="text" dir="ltr" name="NEW_META_DESCRIPTION" value="<?php echo get_gedcom_setting(WT_GED_ID, 'META_DESCRIPTION'); ?>" size="40" maxlength="255">
							<span class="help_content">
								<?php echo WT_I18N::translate('The value to place in the “meta description” tag in the HTML page header. Leave this field empty to use the name of the currently active family tree.'); ?>
							</span>
						</div>
					</div>
				</div>
				<!-- PRIVACY OPTIONS -->
				<h3 class="accordion"><?php echo WT_I18N::translate('Privacy'); ?></h3>
				<div id="privacy">
					<div class="tree_config odd">
						<label><?php echo WT_I18N::translate('Enable privacy'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_HIDE_LIVE_PEOPLE', $HIDE_LIVE_PEOPLE); ?>
							<span class="help_content">
								<?php echo WT_I18N::translate('This option will enable all privacy settings and hide the details of living people, as defined or modified below. If privacy is not enabled kiwitrees will ignore all the other settings on this page.'); ?>
								<a href="#" class="more accepted"><?php echo /* I18N: Click this text to read or hide more information */ WT_I18N::translate('More / Less ....'); ?></a>
								<div class="hidden" style="display: none;">
									<p><?php echo WT_I18N::plural('<b>Note:</b> "living" is defined (if no death or burial is known) as ending %d year after birth or estimated birth.','<b>Note:</b> "living" is defined (if no death or burial is known) as ending %d years after birth or estimated birth.', get_gedcom_setting(WT_GED_ID, 'MAX_ALIVE_AGE'), get_gedcom_setting(WT_GED_ID, 'MAX_ALIVE_AGE')); ?></p>
									<p><?php echo WT_I18N::translate('The length of time after birth can be set using the option "Age at which to assume a person is dead".'); ?></p>
								</div>
							</span>
						</div>
					</div>
					<div class="tree_config even">
						<label><?php echo WT_I18N::translate('Show dead people'); ?></label>
						<div class="input_group">
							<?php echo edit_field_access_level("SHOW_DEAD_PEOPLE", get_gedcom_setting(WT_GED_ID, 'SHOW_DEAD_PEOPLE')); ?>
							<span class="help_content">
								<?php echo WT_I18N::translate('Set the privacy access level for all dead people.'); ?>
							</span>
						</div>
					</div>
					<div class="tree_config odd">
						<label><?php echo WT_I18N::translate('Age at which to assume a person is dead'); ?></label>
						<div class="input_group">
							<input type="text" name="MAX_ALIVE_AGE" value="<?php echo get_gedcom_setting(WT_GED_ID, 'MAX_ALIVE_AGE'); ?>" size="5" maxlength="3">
							<?php echo WT_I18N::translate('years'); ?>
							<span class="help_content">
								<?php echo WT_I18N::translate('If this person has any events other than death, burial, or cremation more recent than this number of years, they are considered to be "alive". Children\'s birth dates are considered to be such events for this purpose.'); ?>
							</span>
						</div>
					</div>
					<div class="tree_config even">
						<label><?php /* I18N: ... [who were] born in the last XX years or died in the last YY years */ echo WT_I18N::translate('Extend privacy of dead people'); ?></label>
						<div class="input_group">
							<?php echo /* I18N: ... Extend privacy to dead people [who were] ... */ WT_I18N::translate(
									'born in the last %1$s years or died in the last %2$s years',
									'<input type="text" name="KEEP_ALIVE_YEARS_BIRTH" value="'.get_gedcom_setting(WT_GED_ID, 'KEEP_ALIVE_YEARS_BIRTH').'" size="5" maxlength="3">',
									'<input type="text" name="KEEP_ALIVE_YEARS_DEATH" value="'.get_gedcom_setting(WT_GED_ID, 'KEEP_ALIVE_YEARS_DEATH').'" size="5" maxlength="3">'
								); ?>
							<span class="help_content">
								<?php echo WT_I18N::translate('In some countries privacy laws apply not only to living people but also to those who have died recently. This option allows you to extend the privacy rules for living people to those who were born or died within a specified number of years. Leave these values at zero to disable this feature.'); ?>
							</span>
						</div>
					</div>
					<div class="tree_config odd">
						<label><?php echo WT_I18N::translate('Names of private individuals'); ?></label>
						<div class="input_group">
							<?php echo edit_field_access_level("SHOW_LIVING_NAMES", get_gedcom_setting(WT_GED_ID, 'SHOW_LIVING_NAMES')); ?>
							<span class="help_content">
								<?php echo WT_I18N::translate('This option will show the names (but no other details) of private individuals. Individuals are private if they are still alive or if a privacy restriction has been added to their individual record. To hide a specific name, add a privacy restriction to that name record.'); ?>
							</span>
						</div>
					</div>
					<div class="tree_config even">
						<label><?php echo WT_I18N::translate('Show private relationships'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('SHOW_PRIVATE_RELATIONSHIPS', get_gedcom_setting(WT_GED_ID, 'SHOW_PRIVATE_RELATIONSHIPS')); ?>
							<span class="help_content">
								<?php echo WT_I18N::translate('This option will retain family links in private records. This means you will see empty "private" boxes on the pedigree chart and on other charts with private people.'); ?>
							</span>
						</div>
					</div>
					<hr>
					<h3><?php echo WT_I18N::translate('Privacy restrictions'); ?></h3>
					<span class="help_content">
						<?php echo WT_I18N::translate('You can set the access for a specific record, fact, or event by adding a restriction to it. If a record, fact, or event does not have a restriction the following default restrictions will be used.'); ?>
					</span>
					<div class="tree_config">
						<?php
						$all_tags	= array();
						$tags		= array_unique(array_merge(
							explode(',', get_gedcom_setting(WT_GED_ID, 'INDI_FACTS_ADD')), explode(',', get_gedcom_setting(WT_GED_ID, 'INDI_FACTS_UNIQUE')),
							explode(',', get_gedcom_setting(WT_GED_ID, 'FAM_FACTS_ADD' )), explode(',', get_gedcom_setting(WT_GED_ID, 'FAM_FACTS_UNIQUE' )),
							explode(',', get_gedcom_setting(WT_GED_ID, 'NOTE_FACTS_ADD')), explode(',', get_gedcom_setting(WT_GED_ID, 'NOTE_FACTS_UNIQUE')),
							explode(',', get_gedcom_setting(WT_GED_ID, 'SOUR_FACTS_ADD')), explode(',', get_gedcom_setting(WT_GED_ID, 'SOUR_FACTS_UNIQUE')),
							explode(',', get_gedcom_setting(WT_GED_ID, 'REPO_FACTS_ADD')), explode(',', get_gedcom_setting(WT_GED_ID, 'REPO_FACTS_UNIQUE')),
							array('SOUR', 'REPO', 'OBJE', '_PRIM', 'NOTE', 'SUBM', 'SUBN', '_UID', 'CHAN')
						));

						foreach ($tags as $tag) {
							if ($tag) {
								$all_tags[$tag] = WT_Gedcom_Tag::getLabel($tag);
							}
						}

						uasort($all_tags, 'utf8_strcasecmp');
						?>
						<table>
							<thead>
								<tr>
									<th><?php echo WT_I18N::translate('Record'); ?></th>
									<th><?php echo WT_I18N::translate('Fact or event'); ?></th>
									<th><?php echo WT_I18N::translate('Access level'); ?></th>
									<th><?php echo WT_I18N::translate('Action'); ?></th>
								</tr>
							</thead>
							<tbody>
								<tr class="even">
									<td><input data-autocomplete-type="IFSRO" type="text" class="pedigree_form" name="xref" id="xref" dir="ltr" maxlength="20" placeholder="<?php echo /* I18N: a placeholder for input of all or any record type */ WT_I18N::translate('All records'); ?>"></td>
									<td><?php echo select_edit_control('tag_type', $all_tags, '', null, null); ?></td>
									<td><?php echo select_edit_control('resn', $PRIVACY_CONSTANTS, null, 'privacy', null); ?></td>
									<td>
										<button class="btn btn-primary" type="submit"  onClick="document.configform.elements[\'action\'].value=\'add\';document.configform.submit();">
											<i class="fa fa-plus"></i>
											<?php echo WT_I18N::translate('add'); ?>
										</button>
										<input type="hidden" name="default_resn_id" value=""><!-- value set by JS -->
									</td>
								</tr>
								<?php
								$rows=WT_DB::prepare(
									"SELECT default_resn_id, tag_type, xref, resn".
									" FROM `##default_resn`".
									" LEFT JOIN `##name` ON (gedcom_id=n_file AND xref=n_id AND n_num=0)".
									" WHERE gedcom_id=?".
									" ORDER BY xref IS NULL, n_sort, xref, tag_type"
								)->execute(array(WT_GED_ID))->fetchAll();
								$n = 1;
								foreach ($rows as $row) { ?>
									<tr class="<?php echo ($n % 2 == 0 ? 'even' : 'odd'); ?>">
										<td>
											<?php
											$n++;
											if ($row->xref) {
												$record=WT_GedcomRecord::getInstance($row->xref);
												if ($record) {
													echo '<a href="', $record->getHtmlUrl(), '">', $record->getFullName(), '</a>';
												} else {
													echo WT_I18N::translate('this record does not exist');
												}
											} else {
												echo '&nbsp;';
											} ?>
										</td>
										<td>
											<?php if ($row->tag_type) {
												// I18N: e.g. Marriage (MARR)
												echo WT_Gedcom_Tag::getLabel($row->tag_type);
											} else {
												echo '&nbsp;';
											} ?>
										</td>
										<td>
											<?php echo $PRIVACY_CONSTANTS[$row->resn]; ?>
										</td>
										<td>
											<button class="btn btn-primary" type="submit"  onClick="document.configform.elements[\'action\'].value=\'delete\';document.configform.elements[\'default_resn_id\'].value=\''.$row->default_resn_id.'\';document.configform.submit();">
												<i class="fa fa-trash-o"></i>
												<?php echo WT_I18N::translate('delete'); ?>
											</button>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
				<!-- MEDIA -->
				<h3 class="accordion"><?php echo WT_I18N::translate('Media'); ?></h3>
				<div id="config-media">
					<div class="tree_config odd">
						<label><?php echo WT_I18N::translate('Media folder'); ?></label>
						<div class="input_group">
							<span class="input_label left"><?php echo WT_DATA_DIR; ?></span>
							<input type="text" name="NEW_MEDIA_DIRECTORY" value="<?php echo $MEDIA_DIRECTORY; ?>" dir="ltr" size="15" maxlength="255">
							<span class="help_content">
								<?php echo WT_I18N::translate('This folder will be used to store the media files for this family tree.'); ?>
								<a href="#" class="more accepted"><?php echo /* I18N: Click this text to read or hide more information */ WT_I18N::translate('More / Less ....'); ?></a>
								<div class="hidden" style="display: none;">
									<p><?php echo WT_I18N::translate('If you select a different folder, you must also move any media files from the existing folder to the new one.'); ?></p>
									<p><?php echo WT_I18N::translate('If two family trees use the same media folder, then they will be able to share media files.  If they use different media folders, then their media files will be kept separate.'); ?></p>
								</div>
							</span>
						</div>
					</div>
					<div class="tree_config even">
						<label><?php echo WT_I18N::translate('Who can upload new media files'); ?></label>
						<div class="input_group">
							<?php echo select_edit_control('NEW_MEDIA_UPLOAD',
								array(
									WT_PRIV_USER=>WT_I18N::translate('Show to members'),
									WT_PRIV_NONE=>WT_I18N::translate('Show to managers'),
									WT_PRIV_HIDE=>WT_I18N::translate('Hide from everyone')
								),
								 null, get_gedcom_setting(WT_GED_ID, 'MEDIA_UPLOAD')); ?>
							 <span class="help_content">
									<?php echo WT_I18N::translate('If you are concerned that users might upload inappropriate images, you can restrict media uploads to managers only.'); ?>
								</span>
						</div>
					</div>
					<div class="tree_config odd">
						<label><?php echo WT_I18N::translate('Show download link in media viewer'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_SHOW_MEDIA_DOWNLOAD', get_gedcom_setting(WT_GED_ID, 'SHOW_MEDIA_DOWNLOAD')); ?>
							<span class="help_content">
							   <?php echo WT_I18N::translate('The media viewer can show a link which when clicked will download the media file to the local PC.<br><br>You may want to hide the download link for security reasons.'); ?>
						   </span>
					    </div>
					</div>
					<div class="tree_config even">
						<label><?php echo WT_I18N::translate('Width of generated thumbnails'); ?></label>
						<div class="input_group">
							<input type="text" name="NEW_THUMBNAIL_WIDTH" value="<?php echo $THUMBNAIL_WIDTH; ?>" maxlength="4" required>
							<span class="input_label right"><?php echo /* I18N: the suffix to a media size */ WT_I18N::translate('pixels'); ?></span>
							<span class="help_content">
								<?php echo WT_I18N::translate('This is the width (in pixels) that the program will use when automatically generating thumbnails.  The default setting is 100.'); ?>
							</span>
						</div>
					</div>
					<div class="tree_config odd">
						<label><?php echo WT_I18N::translate('Use silhouettes'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_USE_SILHOUETTE', get_gedcom_setting(WT_GED_ID, 'USE_SILHOUETTE')); ?>
							<span class="help_content">
								<?php echo WT_I18N::translate('Use silhouette images when no highlighted image for that individual has been specified. The images used are specific to the gender of the individual in question and may also vary according to the theme you use.'); ?>
							</span>
						</div>
					</div>
					<div class="tree_config even">
						<label><?php echo WT_I18N::translate('Show highlight images in people boxes'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_SHOW_HIGHLIGHT_IMAGES', get_gedcom_setting(WT_GED_ID, 'SHOW_HIGHLIGHT_IMAGES')); ?>
							<span class="help_content">
								<a href="http://kiwitrees.net/highlighted-images/" target="_blank">
									<?php echo WT_I18N::translate('Click here to view more information about highlight images on the kiwitrees.net website FAQs'); ?>
								</a>
							</span>
						</div>
					</div>
					<div class="tree_config odd">
						<label><?php echo WT_I18N::translate('Add watermarks to thumbnails'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_WATERMARK_THUMB', get_gedcom_setting(WT_GED_ID, 'WATERMARK_THUMB')); ?>
							<span class="help_content">
								<?php echo WT_I18N::translate('A watermark is text that is added to an image to discourage others from copying it without permission. If you select yes further options will be available.'); ?>
							</span>
						</div>
					</div>
					<div id="watermarks"> <!-- this div is hidden / displayed by js -->
						<div class="tree_config even">
							<label><?php echo WT_I18N::translate('Store watermarked full size images on server?'); ?></label>
							<div class="input_group">
								<?php echo edit_field_yes_no('NEW_SAVE_WATERMARK_IMAGE', get_gedcom_setting(WT_GED_ID, 'SAVE_WATERMARK_IMAGE')); ?>
								<span class="help_content">
									<?php echo WT_I18N::translate('Watermarks can be slow to generate for large images. Busy sites may prefer to generate them once and store the watermarked image on the server.'); ?>
								</span>
							</div>
						</div>
						<div class="tree_config odd">
							<label><?php echo WT_I18N::translate('Store watermarked thumbnails on server'); ?></label>
							<div class="input_group">
								<?php echo edit_field_yes_no('NEW_SAVE_WATERMARK_THUMB', get_gedcom_setting(WT_GED_ID, 'SAVE_WATERMARK_THUMB')); ?>
							</div>
						</div>
						<div class="tree_config everyone">
							<label><?php echo WT_I18N::translate('Images without watermarks'); ?></label>
							<div class="input_group">
								<?php echo edit_field_access_level("NEW_SHOW_NO_WATERMARK", $SHOW_NO_WATERMARK); ?>
								<span class="help_content">
									<?php echo WT_I18N::translate('Watermarks are optional and normally shown just to visitors.'); ?>
								</span>
							</div>
						</div>
					</div>
				</div>
				<!-- LAYOUT -->
				<h3 class="accordion"><?php echo WT_I18N::translate('Layout'); ?></h3>
				<div id="layout-options">
					<h4 class="accepted"><?php echo WT_I18N::translate('Names'); ?></h4>
					<div class="tree_config odd">
						<label><?php echo WT_I18N::translate('Min. no. of occurrences to be a "common surname"'); ?></label>
						<div class="input_group">
							<input type="text" name="NEW_COMMON_NAMES_THRESHOLD" value="<?php echo get_gedcom_setting(WT_GED_ID, 'COMMON_NAMES_THRESHOLD'); ?>" maxlength="5" required>
							<span class="help_content">
							   <?php echo WT_I18N::translate('This is the number of times a surname must occur before it shows up in the Common Surname list on the "Statistics block".'); ?>
						   </span>
					    </div>
					</div>
					<div class="tree_config even">
						<label><?php echo WT_I18N::translate('Names to add to common surnames'); ?></label>
						<div class="input_group">
							<input type="text" name="NEW_COMMON_NAMES_ADD" dir="ltr" value="<?php echo get_gedcom_setting(WT_GED_ID, 'COMMON_NAMES_ADD'); ?>" maxlength="255">
							<span class="help_content">
							   <?php echo WT_I18N::translate('If the number of times that a certain surname occurs is lower than the threshold, it will not appear in the list. It can be added here manually. If more than one surname is entered, they must be separated by a comma. <b>Surnames are case-sensitive.</b>'); ?>
						   </span>
					    </div>
					</div>
					<div class="tree_config odd">
						<label><?php echo WT_I18N::translate('Names to remove from common surnames (comma separated)'); ?></label>
						<div class="input_group">
							<input type="text" name="NEW_COMMON_NAMES_REMOVE" dir="ltr" value="<?php echo get_gedcom_setting(WT_GED_ID, 'COMMON_NAMES_REMOVE'); ?>" maxlength="255">
							<span class="help_content">
							   <?php echo WT_I18N::translate('If you want to remove a surname from the Common Surname list without increasing the threshold value, you can do that by entering the surname here. If more than one surname is entered, they must be separated by a comma. <b>Surnames are case-sensitive.</b> Surnames entered here will also be removed from the Top-10 list on the Home Page.'); ?>
						   </span>
					    </div>
					</div>
					<div class="tree_config even">
						<label><?php echo WT_I18N::translate('Display surnames in all CAPS'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_ALL_CAPS', get_gedcom_setting(WT_GED_ID, 'ALL_CAPS')); ?>
					    </div>
					</div>
					<h4 class="accepted"><?php echo WT_I18N::translate('Lists'); ?></h4>
					<div class="tree_config odd">
						<label><?php echo WT_I18N::translate('Surname list style'); ?></label>
						<div class="input_group">
							<select name="NEW_SURNAME_LIST_STYLE">
								<option value="style1" <?php if ($SURNAME_LIST_STYLE=="style1") echo "selected=\"selected\""; ?>><?php echo WT_I18N::translate('list'); ?></option>
								<option value="style2" <?php if ($SURNAME_LIST_STYLE=="style2") echo "selected=\"selected\""; ?>><?php echo WT_I18N::translate('table'); ?></option>
								<option value="style3" <?php if ($SURNAME_LIST_STYLE=="style3") echo "selected=\"selected\""; ?>><?php echo WT_I18N::translate('tag cloud'); ?></option>
							</select>
					    </div>
					</div>
					<div class="tree_config even">
						<label><?php echo WT_I18N::translate('Maximum number of surnames on individual list'); ?></label>
						<div class="input_group">
							<input type="text" name="NEW_SUBLIST_TRIGGER_I" value="<?php echo get_gedcom_setting(WT_GED_ID, 'SUBLIST_TRIGGER_I'); ?>" maxlength="5" required>
							<span class="help_content">
							   <?php echo WT_I18N::translate('Long lists of people with the same surname can be broken into smaller sub-lists according to the first letter of the individual\'s given name.<br><br>This option determines when sub-listing of surnames will occur. To disable sub-listing completely, set this option to zero.'); ?>
						   </span>
					    </div>
					</div>
					<div class="tree_config odd">
						<label><?php echo WT_I18N::translate('Estimated dates for birth and death'); ?></label>
						<div class="input_group">
							<?php echo radio_buttons('NEW_SHOW_EST_LIST_DATES', array(false=>WT_I18N::translate('hide'), true=>WT_I18N::translate('show')), get_gedcom_setting(WT_GED_ID, 'SHOW_EST_LIST_DATES')); ?>
							<span class="help_content">
							   <?php echo WT_I18N::translate('This option controls whether or not to show estimated dates for birth and death instead of leaving blanks on individual lists and charts for individuals whose dates are not known.'); ?>
						   </span>
					    </div>
					</div>
					<div class="tree_config even">
						<label><?php echo WT_I18N::translate('The date and time of the last update'); ?></label>
						<div class="input_group">
							<?php echo radio_buttons('NEW_SHOW_LAST_CHANGE', array(false=>WT_I18N::translate('hide'), true=>WT_I18N::translate('show')), $SHOW_LAST_CHANGE); ?>
					    </div>
					</div>
					<h4 class="accepted"><?php echo WT_I18N::translate('Charts'); ?></h4>
					<div class="tree_config odd">
						<label><?php echo WT_I18N::translate('Default pedigree chart layout'); ?></label>
						<div class="input_group">
							<select name="NEW_PEDIGREE_LAYOUT">
								<option value="yes" <?php if ($PEDIGREE_LAYOUT) echo "selected=\"selected\""; ?>><?php echo WT_I18N::translate('Landscape'); ?></option>
								<option value="no" <?php if (!$PEDIGREE_LAYOUT) echo "selected=\"selected\""; ?>><?php echo WT_I18N::translate('Portrait'); ?></option>
							</select>
							<span class="help_content">
							   <?php echo /* I18N: Help text for the “Default pedigree chart layout” tree configuration setting */ WT_I18N::translate('This option indicates whether the Pedigree chart should be generated in landscape or portrait mode.'); ?>
						   </span>
					    </div>
					</div>
					<div class="tree_config even">
						<label><?php echo WT_I18N::translate('Default pedigree generations'); ?></label>
						<div class="input_group">
							<input type="text" name="NEW_DEFAULT_PEDIGREE_GENERATIONS" value="<?php echo $DEFAULT_PEDIGREE_GENERATIONS; ?>" maxlength="3" required>
							<span class="help_content">
							   <?php echo /* I18N: Help text for the “Default pedigree chart layout” tree configuration setting */ WT_I18N::translate('Set the default number of generations to display on Descendancy and Pedigree charts.'); ?>
						   </span>
					    </div>
					</div>
					<div class="tree_config odd">
						<label><?php echo WT_I18N::translate('Maximum pedigree generations'); ?></label>
						<div class="input_group">
							<input type="text" name="NEW_MAX_PEDIGREE_GENERATIONS" value="<?php echo $MAX_PEDIGREE_GENERATIONS; ?>" maxlength="3">
							<span class="help_content">
							   <?php echo /* I18N: Help text for the “Maximum pedigree generations” tree configuration setting */ WT_I18N::translate('Set the maximum number of generations to display on Pedigree charts.'); ?>
						   </span>
					    </div>
					</div>
					<div class="tree_config even">
						<label><?php echo WT_I18N::translate('Maximum descendancy generations'); ?></label>
						<div class="input_group">
							<input type="text" name="NEW_MAX_DESCENDANCY_GENERATIONS" value="<?php echo $MAX_DESCENDANCY_GENERATIONS; ?>" maxlength="3">
							<span class="help_content">
							   <?php echo /* I18N: Help text for the “Maximum descendancy generations” tree configuration setting */ WT_I18N::translate('Set the maximum number of generations to display on Descendancy charts.'); ?>
						   </span>
					    </div>
					</div>
					<h4 class="accepted"><?php echo WT_I18N::translate('Individual pages'); ?></h4>
					<div class="tree_config odd">
						<label><?php echo WT_I18N::translate('Show events of close relatives on individual page'); ?></label>
						<input type="hidden" name="NEW_SHOW_RELATIVES_EVENTS" value="<?php echo $SHOW_RELATIVES_EVENTS; ?>">
						<div class="input_group">
							<table id="relatives">
								<?php
								$rel_events=array(
									array('_BIRT_GCHI', '_MARR_GCHI', '_DEAT_GCHI'),
									array('_BIRT_CHIL', '_MARR_CHIL', '_DEAT_CHIL'),
									array('_BIRT_SIBL', '_MARR_SIBL', '_DEAT_SIBL'),
									array(null,         null,         '_DEAT_SPOU'),
									array(null,         '_MARR_PARE', '_DEAT_PARE'),
									array(null,         null,         '_DEAT_GPAR'),
								);
								$n = 1;
								foreach ($rel_events as $row) {
									echo '<tr class="' . ($n % 2 == 0 ? 'even' : 'odd') . '">';
									foreach ($row as $col) {
										echo '<td>';
										$n++;
										if (is_null($col)) {
											echo '&nbsp;';
										} else {
											echo "<input type=\"checkbox\" name=\"SHOW_RELATIVES_EVENTS_checkbox\" value=\"".$col."\"";
											if (strstr($SHOW_RELATIVES_EVENTS, $col)) {
												echo " checked=\"checked\"";
											}
											echo " onchange=\"var old=document.configform.NEW_SHOW_RELATIVES_EVENTS.value; if (this.checked) old+=','+this.value; else old=old.replace(/".$col."/g,''); old=old.replace(/[,]+/gi,','); old=old.replace(/^[,]/gi,''); old=old.replace(/[,]$/gi,''); document.configform.NEW_SHOW_RELATIVES_EVENTS.value=old\"> ";
											echo WT_Gedcom_Tag::getLabel($col);
										}
										echo '</td>';
									}
									echo '</tr>';
								}
								?>
							</table>
					    </div>
					</div>
					<div class="tree_config even">
						<label><?php echo WT_I18N::translate('Automatically expand list of events of close relatives'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_EXPAND_RELATIVES_EVENTS', get_gedcom_setting(WT_GED_ID, 'EXPAND_RELATIVES_EVENTS')); ?>
							<span class="help_content">
							   <?php echo WT_I18N::translate('This option controls whether or not to automatically expand the <i>Events of close relatives</i> list.'); ?>
						   </span>
					    </div>
					</div>
					<?php if (file_exists(WT_Site::preference('INDEX_DIRECTORY').'histo.'.WT_LOCALE.'.php')) { ?>
						<div class="tree_config odd">
							<label><?php echo WT_I18N::translate('Automatically expand list of historic events'); ?></label>
							<div class="input_group">
								<?php echo edit_field_yes_no('NEW_EXPAND_HISTO_EVENTS', get_gedcom_setting(WT_GED_ID, 'EXPAND_HISTO_EVENTS')); ?>
						    </div>
						</div>
					<?php } ?>
					<h4 class="accepted"><?php echo WT_I18N::translate('Places'); ?></h4>
					<div class="tree_config even">
						<label><?php echo WT_I18N::translate('Abbreviate place names'); ?></label>
						<div class="input_group">
							<?php
							echo /* I18N: The placeholders are edit controls.  Show the [first/last] [1/2/3/4/5] parts of a place name */ WT_I18N::translate(
								'Show the %1$s %2$s parts of a place name.',
								select_edit_control('NEW_SHOW_PEDIGREE_PLACES_SUFFIX',
									array(
										false=>WT_I18N::translate_c('Show the [first/last] [N] parts of a place name.', 'first'),
										true =>WT_I18N::translate_c('Show the [first/last] [N] parts of a place name.', 'last')
									),
									null,
									get_gedcom_setting(WT_GED_ID, 'SHOW_PEDIGREE_PLACES_SUFFIX')
								),
								select_edit_control('NEW_SHOW_PEDIGREE_PLACES',
									array(
										1=>WT_I18N::number(1),
										2=>WT_I18N::number(2),
										3=>WT_I18N::number(3),
										4=>WT_I18N::number(4),
										5=>WT_I18N::number(5),
										6=>WT_I18N::number(6),
										7=>WT_I18N::number(7),
										8=>WT_I18N::number(8),
										9=>WT_I18N::number(9),
									),
									null,
									get_gedcom_setting(WT_GED_ID, 'SHOW_PEDIGREE_PLACES')
								)
							);
							?>
							<span class="help_content">
							   <?php echo WT_I18N::translate('Place names are frequently too long to fit on charts, lists, etc.  They can be abbreviated by showing just the first few parts of the name, such as <i>village, county</i>, or the last few part of it, such as <i>region, country</i>.'); ?>
						   </span>
					    </div>
					</div>
				</div>
				<!-- HIDE & SHOW -->
				<h3 class="accordion"><?php echo WT_I18N::translate('Hide &amp; show'); ?></h3>
				<div id="hide-show">
							<?php echo WT_I18N::translate('Charts'); ?>
								<?php echo WT_I18N::translate('Abbreviate chart labels'), help_link('ABBREVIATE_CHART_LABELS'); ?>
								<?php echo edit_field_yes_no('NEW_ABBREVIATE_CHART_LABELS', get_gedcom_setting(WT_GED_ID, 'ABBREVIATE_CHART_LABELS')); ?>
								<?php echo WT_I18N::translate('Show chart details by default'), help_link('PEDIGREE_FULL_DETAILS'); ?>
								<?php echo edit_field_yes_no('NEW_PEDIGREE_FULL_DETAILS', get_gedcom_setting(WT_GED_ID, 'PEDIGREE_FULL_DETAILS')); ?>
								<?php echo WT_I18N::translate('Gender icon on charts'), help_link('PEDIGREE_SHOW_GENDER'); ?>
								<?php echo radio_buttons('NEW_PEDIGREE_SHOW_GENDER', array(false=>WT_I18N::translate('hide'), true=>WT_I18N::translate('show')), $PEDIGREE_SHOW_GENDER); ?>
								<?php echo WT_I18N::translate('Age of parents next to child\'s birthdate'), help_link('SHOW_PARENTS_AGE'); ?>
								<?php echo radio_buttons('NEW_SHOW_PARENTS_AGE', array(false=>WT_I18N::translate('hide'), true=>WT_I18N::translate('show')), $SHOW_PARENTS_AGE); ?>
								<?php echo WT_I18N::translate('LDS ordinance codes in chart boxes'), help_link('SHOW_LDS_AT_GLANCE'); ?>
								<?php echo radio_buttons('NEW_SHOW_LDS_AT_GLANCE', array(false=>WT_I18N::translate('hide'), true=>WT_I18N::translate('show')), $SHOW_LDS_AT_GLANCE); ?>
								<?php echo WT_I18N::translate('Other facts to show in charts'), help_link('CHART_BOX_TAGS'); ?>
								<input type="text" id="NEW_CHART_BOX_TAGS" name="NEW_CHART_BOX_TAGS" value="<?php echo $CHART_BOX_TAGS; ?>" dir="ltr" size="50" maxlength="255"><?php echo print_findfact_link('NEW_CHART_BOX_TAGS'); ?>
							<?php echo WT_I18N::translate('Individual pages'); ?>
								<?php echo WT_I18N::translate('Fact icons'), help_link('SHOW_FACT_ICONS'); ?>
								<?php echo radio_buttons('NEW_SHOW_FACT_ICONS', array(false=>WT_I18N::translate('hide'), true=>WT_I18N::translate('show')), $SHOW_FACT_ICONS); ?>
								<?php echo WT_I18N::translate('Automatically expand notes'), help_link('EXPAND_NOTES'); ?>
								<?php echo edit_field_yes_no('NEW_EXPAND_NOTES', get_gedcom_setting(WT_GED_ID, 'EXPAND_NOTES')); ?>
								<?php echo WT_I18N::translate('Automatically expand sources'), help_link('EXPAND_SOURCES'); ?>
								<?php echo edit_field_yes_no('NEW_EXPAND_SOURCES', get_gedcom_setting(WT_GED_ID, 'EXPAND_SOURCES')); ?>
								<?php echo WT_I18N::translate('Show all notes and source references on notes and sources tabs'), help_link('SHOW_LEVEL2_NOTES'); ?>
								<?php echo edit_field_yes_no('NEW_SHOW_LEVEL2_NOTES', get_gedcom_setting(WT_GED_ID, 'SHOW_LEVEL2_NOTES')); ?>
								<?php echo WT_I18N::translate('Date differences'), help_link('SHOW_AGE_DIFF'); ?>
								<?php echo radio_buttons('NEW_SHOW_AGE_DIFF', array(false=>WT_I18N::translate('hide'), true=>WT_I18N::translate('show')), $SHOW_AGE_DIFF); ?>
							<?php echo WT_I18N::translate('General'); ?>
								<?php echo WT_I18N::translate('Allow users to see raw GEDCOM records'), help_link('SHOW_GEDCOM_RECORD'); ?>
								<?php echo edit_field_yes_no('NEW_SHOW_GEDCOM_RECORD', get_gedcom_setting(WT_GED_ID, 'SHOW_GEDCOM_RECORD')); ?>
								<?php echo WT_I18N::translate('GEDCOM errors'), help_link('HIDE_GEDCOM_ERRORS'); ?>
								<?php echo radio_buttons('NEW_HIDE_GEDCOM_ERRORS', array(true=>WT_I18N::translate('hide'), false=>WT_I18N::translate('show')), $HIDE_GEDCOM_ERRORS); /* Note: name of object is reverse of description */ ?>
								<?php echo WT_I18N::translate('Hit counters'), help_link('SHOW_COUNTER'); ?>
								<?php echo radio_buttons('NEW_SHOW_COUNTER', array(false=>WT_I18N::translate('hide'), true=>WT_I18N::translate('show')), $SHOW_COUNTER); ?>
								<?php echo WT_I18N::translate('Execution statistics'), help_link('SHOW_STATS'); ?>
								<?php echo radio_buttons('NEW_SHOW_STATS', array(false=>WT_I18N::translate('hide'), true=>WT_I18N::translate('show')), get_gedcom_setting(WT_GED_ID, 'SHOW_STATS')); ?>
				</div>
				<!-- EDIT -->
				<h3 class="accordion"><?php echo WT_I18N::translate('Edit options'); ?></h3>
				<div id="edit-options">
					<table>
					<tr>
						<th colspan="2">
							<?php echo WT_I18N::translate('Facts for Individual records'); ?>
						</th>
					</tr>
					<tr>
						<td>
							<?php echo WT_I18N::translate('All individual facts'), help_link('INDI_FACTS_ADD'); ?>
						</td>
						<td>
							<input type="text" id="NEW_INDI_FACTS_ADD" name="NEW_INDI_FACTS_ADD" value="<?php echo get_gedcom_setting(WT_GED_ID, 'INDI_FACTS_ADD'); ?>" size="60" maxlength="255" dir="ltr"><?php echo print_findfact_link('NEW_INDI_FACTS_ADD'); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo WT_I18N::translate('Unique individual facts'), help_link('INDI_FACTS_UNIQUE'); ?>
						</td>
						<td>
							<input type="text" id="NEW_INDI_FACTS_UNIQUE" name="NEW_INDI_FACTS_UNIQUE" value="<?php echo get_gedcom_setting(WT_GED_ID, 'INDI_FACTS_UNIQUE'); ?>" size="60" maxlength="255" dir="ltr"><?php echo print_findfact_link('NEW_INDI_FACTS_UNIQUE'); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo WT_I18N::translate('Facts for new individuals'), help_link('QUICK_REQUIRED_FACTS'); ?>
						</td>
						<td>
							<input type="text" id="NEW_QUICK_REQUIRED_FACTS" name="NEW_QUICK_REQUIRED_FACTS" value="<?php echo $QUICK_REQUIRED_FACTS; ?>" size="60" maxlength="255" dir="ltr"><?php echo print_findfact_link('NEW_QUICK_REQUIRED_FACTS'); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo WT_I18N::translate('Quick individual facts'), help_link('INDI_FACTS_QUICK'); ?>
						</td>
						<td>
							<input type="text" id="NEW_INDI_FACTS_QUICK" name="NEW_INDI_FACTS_QUICK" value="<?php echo get_gedcom_setting(WT_GED_ID, 'INDI_FACTS_QUICK'); ?>" size="60" maxlength="255" dir="ltr"><?php echo print_findfact_link('NEW_INDI_FACTS_QUICK'); ?>
						</td>
					</tr>
					<tr>
						<th colspan="2">
							<?php echo WT_I18N::translate('Facts for Family records'); ?>
						</th>
					</tr>
					<tr>
						<td>
							<?php echo WT_I18N::translate('All family facts'), help_link('FAM_FACTS_ADD'); ?>
						</td>
						<td>
							<input type="text" id="NEW_FAM_FACTS_ADD" name="NEW_FAM_FACTS_ADD" value="<?php echo get_gedcom_setting(WT_GED_ID, 'FAM_FACTS_ADD'); ?>" size="60" maxlength="255" dir="ltr"><?php echo print_findfact_link('NEW_FAM_FACTS_ADD'); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo WT_I18N::translate('Unique family facts'), help_link('FAM_FACTS_UNIQUE'); ?>
						</td>
						<td>
							<input type="text" id="NEW_FAM_FACTS_UNIQUE" name="NEW_FAM_FACTS_UNIQUE" value="<?php echo get_gedcom_setting(WT_GED_ID, 'FAM_FACTS_UNIQUE'); ?>" size="60" maxlength="255" dir="ltr"><?php echo print_findfact_link('NEW_FAM_FACTS_UNIQUE'); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo WT_I18N::translate('Facts for new families'), help_link('QUICK_REQUIRED_FAMFACTS'); ?>
						</td>
						<td>
							<input type="text" id="NEW_QUICK_REQUIRED_FAMFACTS" name="NEW_QUICK_REQUIRED_FAMFACTS" value="<?php echo $QUICK_REQUIRED_FAMFACTS; ?>" size="60" maxlength="255" dir="ltr"><?php echo print_findfact_link('NEW_QUICK_REQUIRED_FAMFACTS'); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo WT_I18N::translate('Quick family facts'), help_link('FAM_FACTS_QUICK'); ?>
						</td>
						<td>
							<input type="text" id="NEW_FAM_FACTS_QUICK" name="NEW_FAM_FACTS_QUICK" value="<?php echo get_gedcom_setting(WT_GED_ID, 'FAM_FACTS_QUICK'); ?>" size="60" maxlength="255" dir="ltr"><?php echo print_findfact_link('NEW_FAM_FACTS_QUICK'); ?>
						</td>
					</tr>
					<tr>
						<th colspan="2">
							<?php echo WT_I18N::translate('Facts for Source records'); ?>
						</th>
					</tr>
					<tr>
						<td>
							<?php echo WT_I18N::translate('All source facts'), help_link('SOUR_FACTS_ADD'); ?>
						</td>
						<td>
							<input type="text" id="NEW_SOUR_FACTS_ADD" name="NEW_SOUR_FACTS_ADD" value="<?php echo get_gedcom_setting(WT_GED_ID, 'SOUR_FACTS_ADD'); ?>" size="60" maxlength="255" dir="ltr"><?php echo print_findfact_link('NEW_SOUR_FACTS_ADD'); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo WT_I18N::translate('Unique source facts'), help_link('SOUR_FACTS_UNIQUE'); ?>
						</td>
						<td>
							<input type="text" id="NEW_SOUR_FACTS_UNIQUE" name="NEW_SOUR_FACTS_UNIQUE" value="<?php echo get_gedcom_setting(WT_GED_ID, 'SOUR_FACTS_UNIQUE'); ?>" size="60" maxlength="255" dir="ltr"><?php echo print_findfact_link('NEW_SOUR_FACTS_UNIQUE'); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo WT_I18N::translate('Quick source facts'), help_link('SOUR_FACTS_QUICK'); ?>
						</td>
						<td>
							<input type="text" id="NEW_SOUR_FACTS_QUICK" name="NEW_SOUR_FACTS_QUICK" value="<?php echo get_gedcom_setting(WT_GED_ID, 'SOUR_FACTS_QUICK'); ?>" size="60" maxlength="255" dir="ltr"><?php echo print_findfact_link('NEW_SOUR_FACTS_QUICK'); ?>
						</td>
					</tr>
					<tr>
						<th colspan="2">
							<?php echo WT_I18N::translate('Facts for Repository records'); ?>
						</th>
					</tr>
					<tr>
						<td>
							<?php echo WT_I18N::translate('All repository facts'), help_link('REPO_FACTS_ADD'); ?>
						</td>
						<td>
							<input type="text" id="NEW_REPO_FACTS_ADD" name="NEW_REPO_FACTS_ADD" value="<?php echo get_gedcom_setting(WT_GED_ID, 'REPO_FACTS_ADD'); ?>" size="60" maxlength="255" dir="ltr"><?php echo print_findfact_link('NEW_REPO_FACTS_ADD'); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo WT_I18N::translate('Unique repository facts'), help_link('REPO_FACTS_UNIQUE'); ?>
						</td>
						<td>
							<input type="text" id="NEW_REPO_FACTS_UNIQUE" name="NEW_REPO_FACTS_UNIQUE" value="<?php echo get_gedcom_setting(WT_GED_ID, 'REPO_FACTS_UNIQUE'); ?>" size="60" maxlength="255" dir="ltr"><?php echo print_findfact_link('NEW_REPO_FACTS_UNIQUE'); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo WT_I18N::translate('Quick repository facts'), help_link('REPO_FACTS_QUICK'); ?>
						</td>
						<td>
							<input type="text" id="NEW_REPO_FACTS_QUICK" name="NEW_REPO_FACTS_QUICK" value="<?php echo get_gedcom_setting(WT_GED_ID, 'REPO_FACTS_QUICK'); ?>" size="60" maxlength="255" dir="ltr"><?php echo print_findfact_link('NEW_REPO_FACTS_QUICK'); ?>
						</td>
					</tr>
					<tr>
						<th colspan="2">
							<?php echo WT_I18N::translate('Advanced fact settings'); ?>
						</th>
					</tr>
					<tr>
						<td>
							<?php echo WT_I18N::translate('Advanced name facts'), help_link('ADVANCED_NAME_FACTS'); ?>
						</td>
						<td>
							<input type="text" id="NEW_ADVANCED_NAME_FACTS" name="NEW_ADVANCED_NAME_FACTS" value="<?php echo $ADVANCED_NAME_FACTS; ?>" size="40" maxlength="255" dir="ltr"><?php echo print_findfact_link('NEW_ADVANCED_NAME_FACTS'); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo WT_I18N::translate('Advanced place name facts'), help_link('ADVANCED_PLAC_FACTS'); ?>
						</td>
						<td>
							<input type="text" id="NEW_ADVANCED_PLAC_FACTS" name="NEW_ADVANCED_PLAC_FACTS" value="<?php echo $ADVANCED_PLAC_FACTS; ?>" size="40" maxlength="255" dir="ltr"><?php echo print_findfact_link('NEW_ADVANCED_PLAC_FACTS'); ?>
						</td>
					</tr>
					<tr>
						<th colspan="2">
							<?php echo WT_I18N::translate('Other settings'); ?>
						</th>
					</tr>
					<tr>
						<td>
							<?php echo WT_I18N::translate('Surname tradition'), help_link('SURNAME_TRADITION'); ?>
						</td>
						<td>
							<?php echo select_edit_control('NEW_SURNAME_TRADITION', array('paternal'=>WT_I18N::translate_c('Surname tradition', 'paternal'), 'patrilineal'=>WT_I18N::translate('patrilineal'), 'matrilineal'=>WT_I18N::translate('matrilineal'), 'spanish'=>WT_I18N::translate_c('Surname tradition', 'Spanish'), 'portuguese'=>WT_I18N::translate_c('Surname tradition', 'Portuguese'), 'icelandic'=>WT_I18N::translate_c('Surname tradition', 'Icelandic'), 'polish'=>WT_I18N::translate_c('Surname tradition', 'Polish'), 'lithuanian'=>WT_I18N::translate_c('Surname tradition', 'Lithuanian'), 'none'=>WT_I18N::translate_c('Surname tradition', 'none')), null, get_gedcom_setting(WT_GED_ID, 'SURNAME_TRADITION')); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo WT_I18N::translate('Use full source citations'), help_link('FULL_SOURCES'); ?>
						</td>
						<td>
							<?php echo edit_field_yes_no('NEW_FULL_SOURCES', get_gedcom_setting(WT_GED_ID, 'FULL_SOURCES')); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo WT_I18N::translate('Source type'), help_link('PREFER_LEVEL2_SOURCES'); ?>
						</td>
						<td>
							<?php echo select_edit_control('NEW_PREFER_LEVEL2_SOURCES', array(0=>WT_I18N::translate('none'), 1=>WT_I18N::translate('facts'), 2=>WT_I18N::translate('records')), null, get_gedcom_setting(WT_GED_ID, 'PREFER_LEVEL2_SOURCES')); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo WT_I18N::translate('Use GeoNames database for autocomplete on places'), help_link('USE_GEONAMES'); ?>
						</td>
						<td>
							<?php echo edit_field_yes_no('NEW_USE_GEONAMES', get_gedcom_setting(WT_GED_ID, 'USE_GEONAMES')); ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo WT_I18N::translate('Do not update the “last change” record'), help_link('no_update_CHAN'); ?>
						</td>
						<td>
							<?php echo edit_field_yes_no('NEW_NO_UPDATE_CHAN', get_gedcom_setting(WT_GED_ID, 'NO_UPDATE_CHAN')); ?>
						</td>
					</tr>
					</table>
				</div>
				<!-- THEME OPTIONS -->
				<h3 class="accordion"><?php echo WT_I18N::translate('Theme'); ?></h3>
				<div id="theme">
					<table>
						<tr>
							<td>
								<h3><?php echo $tree->tree_title_html, ' - ', WT_I18N::translate('Theme'); ?></h3>
							</td>
						</tr>
						<tr>
							<td>
								<?php
									$current_themedir = get_gedcom_setting(WT_GED_ID, 'THEME_DIR');
									foreach (get_theme_names() as $themename=>$themedir) {
										echo
										'<div ', ($current_themedir == $themedir ? 'class = "current_theme theme_box"' : 'class = "theme_box"'), '>
											<label for="radio_' ,$themedir, '">
												<img src="themes/', $themedir, '/images/screenshot_' ,$themedir, '.png" alt="' ,$themename, ' title="' ,$themename, '">
												<p>
													<input type="radio" id="radio_' ,$themedir, '" name="NEW_THEME_DIR" value="', $themedir, '" ', ($current_themedir == $themedir ? ' checked="checked"' : ''), '/>
													', $themename, '
												</p>
												<p class="custom_files">';
													$html = WT_I18N::translate('Customized');
													$files_found = false;
													foreach ($custom_files as $file) {
														$path = WT_ROOT . WT_THEMES_DIR . $themedir . '/' . $file;
														if (file_exists($path)) {
															$files_found = true;
															$html .= '&nbsp;' . $file . '&nbsp;';
														}
													}
													echo ($files_found ? $html : '');
												echo '</p>
											</label>
										</div>';
									}
									include WT_ROOT.'themes/colors/theme.php';
									echo color_palette();
								?>
						</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		<p>
			<button class="btn btn-primary" type="submit">
			<i class="fa fa-floppy-o"></i>
				<?php echo WT_I18N::translate('save'); ?>
			</button>
		</p>
	</form>
</div>
