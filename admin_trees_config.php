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

define('KT_SCRIPT_NAME', 'admin_trees_config.php');

require './includes/session.php';
require KT_ROOT . 'includes/functions/functions_edit.php';

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Family tree configuration'))
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
		jQuery(function() {
			jQuery("div.config_options:odd").addClass("odd");
			jQuery("div.config_options:even").addClass("even");
		});
	');

$PRIVACY_CONSTANTS = array(
	'none'			=> KT_I18N::translate('Show to visitors'),
	'privacy'		=> KT_I18N::translate('Show to members'),
	'confidential'	=> KT_I18N::translate('Show to managers'),
	'hidden'		=> KT_I18N::translate('Hide from everyone')
);

$privacy = array(
	KT_PRIV_PUBLIC => KT_I18N::translate('Show to visitors'),
	KT_PRIV_USER   => KT_I18N::translate('Show to members'),
	KT_PRIV_NONE   => KT_I18N::translate('Show to managers'),
	KT_PRIV_HIDE   => KT_I18N::translate('Hide from everyone')
);

// List custom theme files that might exist
$custom_files = array(
		'mystyle.css',
		'mytheme.php',
		'myheader.php',
		'myfooter.php'
	);

// Set active tab based on view parameter from url
$view = KT_Filter::get('view');
switch ($view) {
	case 'file-options':	$active = 0; break;
	case 'contact':			$active = 1; break;
	case 'website':			$active = 2; break;
	case 'privacy':			$active = 3; break;
	case 'config-media':	$active = 4; break;
	case 'layout-options':	$active = 5; break;
	case 'hide-show':		$active = 6; break;
	case 'edit-options':	$active = 7; break;
	case 'theme':			$active = 8; break;
	default:				$active = 0; break;
}
switch (KT_Filter::post('action')) {
case 'delete':
	if (!KT_Filter::checkCsrf()) {
		break;
	}
	KT_DB::prepare(
		"DELETE FROM `##default_resn` WHERE default_resn_id=?"
	)->execute(array(KT_Filter::post('default_resn_id')));
	// Reload the page, so that the new privacy restrictions are reflected in the header
	header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '?view=privacy');
	exit;
case 'add':
	if (!KT_Filter::checkCsrf()) {
		break;
	}
	if ((KT_Filter::post('xref') || KT_Filter::post('tag_type')) && KT_Filter::post('resn')) {
		if (KT_Filter::post('xref') === '') {
			KT_DB::prepare(
				"DELETE FROM `##default_resn` WHERE gedcom_id=? AND tag_type=? AND xref IS NULL"
			)->execute(array(KT_GED_ID, KT_Filter::post('tag_type')));
		}
		if (KT_Filter::post('tag_type') === '') {
			KT_DB::prepare(
				"DELETE FROM `##default_resn` WHERE gedcom_id=? AND xref=? AND tag_type IS NULL"
			)->execute(array(KT_GED_ID, KT_Filter::post('xref')));
		}
		KT_DB::prepare(
			"REPLACE INTO `##default_resn` (gedcom_id, xref, tag_type, resn) VALUES (?, NULLIF(?, ''), NULLIF(?, ''), ?)"
		)->execute(array(KT_GED_ID, KT_Filter::post('xref'), KT_Filter::post('tag_type'), KT_Filter::post('resn')));
	}
	// Reload the page, so that the new privacy restrictions are reflected in the header
	header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '?view=privacy');
	exit;
case 'update':
	if (!KT_Filter::checkCsrf()) {
		break;
	}
	set_gedcom_setting(KT_GED_ID, 'ABBREVIATE_CHART_LABELS',		KT_Filter::postBool('NEW_ABBREVIATE_CHART_LABELS'));
	set_gedcom_setting(KT_GED_ID, 'ADVANCED_NAME_FACTS',			KT_Filter::post('NEW_ADVANCED_NAME_FACTS'));
	set_gedcom_setting(KT_GED_ID, 'ADVANCED_PLAC_FACTS',			KT_Filter::post('NEW_ADVANCED_PLAC_FACTS'));
	// For backwards compatibility with we store the two calendar formats in one variable
	// e.g. "gregorian_and_jewish"
	set_gedcom_setting(KT_GED_ID, 'CALENDAR_FORMAT', implode('_and_', array_unique(array(
		KT_Filter::post('NEW_CALENDAR_FORMAT0', 'gregorian|julian|french|jewish|hijri|jalali', 'none'),
		KT_Filter::post('NEW_CALENDAR_FORMAT1', 'gregorian|julian|french|jewish|hijri|jalali', 'none')
	))));
	set_gedcom_setting(KT_GED_ID, 'ALL_CAPS',						KT_Filter::postBool('NEW_ALL_CAPS'));
	set_gedcom_setting(KT_GED_ID, 'CHART_BOX_TAGS',					KT_Filter::post('NEW_CHART_BOX_TAGS'));
	set_gedcom_setting(KT_GED_ID, 'COMMON_NAMES_ADD',				str_replace(' ', '', KT_Filter::post('NEW_COMMON_NAMES_ADD')));
	set_gedcom_setting(KT_GED_ID, 'COMMON_NAMES_REMOVE',			str_replace(' ', '', KT_Filter::post('NEW_COMMON_NAMES_REMOVE')));
	set_gedcom_setting(KT_GED_ID, 'COMMON_NAMES_THRESHOLD',			KT_Filter::post('NEW_COMMON_NAMES_THRESHOLD', KT_REGEX_INTEGER, 40));
	set_gedcom_setting(KT_GED_ID, 'CONTACT_USER_ID',				KT_Filter::post('NEW_CONTACT_USER_ID'));
	set_gedcom_setting(KT_GED_ID, 'DEFAULT_PEDIGREE_GENERATIONS',	KT_Filter::post('NEW_DEFAULT_PEDIGREE_GENERATIONS'));
	set_gedcom_setting(KT_GED_ID, 'EXPAND_NOTES',					KT_Filter::postBool('NEW_EXPAND_NOTES'));
	set_gedcom_setting(KT_GED_ID, 'EXPAND_SOURCES',					KT_Filter::postBool('NEW_EXPAND_SOURCES'));
	set_gedcom_setting(KT_GED_ID, 'FAM_FACTS_ADD',					str_replace(' ', '', KT_Filter::post('NEW_FAM_FACTS_ADD')));
	set_gedcom_setting(KT_GED_ID, 'FAM_FACTS_QUICK',				str_replace(' ', '', KT_Filter::post('NEW_FAM_FACTS_QUICK')));
	set_gedcom_setting(KT_GED_ID, 'FAM_FACTS_UNIQUE',				str_replace(' ', '', KT_Filter::post('NEW_FAM_FACTS_UNIQUE')));
	set_gedcom_setting(KT_GED_ID, 'FAM_ID_PREFIX',					KT_Filter::post('NEW_FAM_ID_PREFIX'));
	set_gedcom_setting(KT_GED_ID, 'FULL_SOURCES',					KT_Filter::postBool('NEW_FULL_SOURCES'));
	set_gedcom_setting(KT_GED_ID, 'GEDCOM_ID_PREFIX',				KT_Filter::post('NEW_GEDCOM_ID_PREFIX'));
	set_gedcom_setting(KT_GED_ID, 'GEDCOM_MEDIA_PATH',				KT_Filter::post('NEW_GEDCOM_MEDIA_PATH'));
	set_gedcom_setting(KT_GED_ID, 'GENERATE_UIDS',					KT_Filter::postBool('NEW_GENERATE_UIDS'));
	set_gedcom_setting(KT_GED_ID, 'HIDE_GEDCOM_ERRORS',				KT_Filter::postBool('NEW_HIDE_GEDCOM_ERRORS'));
	set_gedcom_setting(KT_GED_ID, 'HIDE_LIVE_PEOPLE',				KT_Filter::postBool('NEW_HIDE_LIVE_PEOPLE'));
	set_gedcom_setting(KT_GED_ID, 'IMAGE_EDITOR',					KT_Filter::post('NEW_IMAGE_EDITOR'));
	set_gedcom_setting(KT_GED_ID, 'INDI_FACTS_ADD',					str_replace(' ', '', KT_Filter::post('NEW_INDI_FACTS_ADD')));
	set_gedcom_setting(KT_GED_ID, 'INDI_FACTS_QUICK',				str_replace(' ', '', KT_Filter::post('NEW_INDI_FACTS_QUICK')));
	set_gedcom_setting(KT_GED_ID, 'INDI_FACTS_UNIQUE',				str_replace(' ', '', KT_Filter::post('NEW_INDI_FACTS_UNIQUE')));
	set_gedcom_setting(KT_GED_ID, 'KEEP_ALIVE_YEARS_BIRTH',			KT_Filter::post('KEEP_ALIVE_YEARS_BIRTH', KT_REGEX_INTEGER, 0));
	set_gedcom_setting(KT_GED_ID, 'KEEP_ALIVE_YEARS_DEATH',			KT_Filter::post('KEEP_ALIVE_YEARS_DEATH', KT_REGEX_INTEGER, 0));
	set_gedcom_setting(KT_GED_ID, 'KIWITREES_EMAIL',				KT_Filter::post('NEW_KIWITREES_EMAIL'));
	set_gedcom_setting(KT_GED_ID, 'LANGUAGE',						KT_Filter::post('GEDCOMLANG'));
	set_gedcom_setting(KT_GED_ID, 'MAX_ALIVE_AGE',					KT_Filter::post('MAX_ALIVE_AGE', KT_REGEX_INTEGER, 100));
	set_gedcom_setting(KT_GED_ID, 'MAX_DESCENDANCY_GENERATIONS',	KT_Filter::post('NEW_MAX_DESCENDANCY_GENERATIONS'));
	set_gedcom_setting(KT_GED_ID, 'MAX_PEDIGREE_GENERATIONS',		KT_Filter::post('NEW_MAX_PEDIGREE_GENERATIONS'));
	set_gedcom_setting(KT_GED_ID, 'MEDIA_ID_PREFIX',				KT_Filter::post('NEW_MEDIA_ID_PREFIX'));
	set_gedcom_setting(KT_GED_ID, 'MEDIA_UPLOAD',					KT_Filter::post('NEW_MEDIA_UPLOAD'));
	set_gedcom_setting(KT_GED_ID, 'META_DESCRIPTION',				KT_Filter::post('NEW_META_DESCRIPTION'));
	set_gedcom_setting(KT_GED_ID, 'META_TITLE',						KT_Filter::post('NEW_META_TITLE'));
	set_gedcom_setting(KT_GED_ID, 'NOTE_ID_PREFIX',					KT_Filter::post('NEW_NOTE_ID_PREFIX'));
	set_gedcom_setting(KT_GED_ID, 'NO_UPDATE_CHAN',					KT_Filter::postBool('NEW_NO_UPDATE_CHAN'));
	set_gedcom_setting(KT_GED_ID, 'PEDIGREE_FULL_DETAILS',			KT_Filter::postBool('NEW_PEDIGREE_FULL_DETAILS'));
	set_gedcom_setting(KT_GED_ID, 'PEDIGREE_LAYOUT',				KT_Filter::postBool('NEW_PEDIGREE_LAYOUT'));
	set_gedcom_setting(KT_GED_ID, 'PEDIGREE_ROOT_ID',				KT_Filter::post('NEW_PEDIGREE_ROOT_ID', KT_REGEX_XREF));
	set_gedcom_setting(KT_GED_ID, 'PEDIGREE_SHOW_GENDER',			KT_Filter::postBool('NEW_PEDIGREE_SHOW_GENDER'));
	set_gedcom_setting(KT_GED_ID, 'PREFER_LEVEL2_SOURCES',			KT_Filter::post('NEW_PREFER_LEVEL2_SOURCES'));
	set_gedcom_setting(KT_GED_ID, 'QUICK_REQUIRED_FACTS',			KT_Filter::post('NEW_QUICK_REQUIRED_FACTS'));
	set_gedcom_setting(KT_GED_ID, 'QUICK_REQUIRED_FAMFACTS',		KT_Filter::post('NEW_QUICK_REQUIRED_FAMFACTS'));
	set_gedcom_setting(KT_GED_ID, 'REPO_FACTS_ADD',					str_replace(' ', '', KT_Filter::post('NEW_REPO_FACTS_ADD')));
	set_gedcom_setting(KT_GED_ID, 'REPO_FACTS_QUICK',				str_replace(' ', '', KT_Filter::post('NEW_REPO_FACTS_QUICK')));
	set_gedcom_setting(KT_GED_ID, 'REPO_FACTS_UNIQUE',				str_replace(' ', '', KT_Filter::post('NEW_REPO_FACTS_UNIQUE')));
	set_gedcom_setting(KT_GED_ID, 'REPO_ID_PREFIX',					KT_Filter::post('NEW_REPO_ID_PREFIX'));
	set_gedcom_setting(KT_GED_ID, 'SAVE_WATERMARK_IMAGE',			KT_Filter::postBool('NEW_SAVE_WATERMARK_IMAGE'));
	set_gedcom_setting(KT_GED_ID, 'SAVE_WATERMARK_THUMB',			KT_Filter::postBool('NEW_SAVE_WATERMARK_THUMB'));
	set_gedcom_setting(KT_GED_ID, 'SHOW_COUNTER',					KT_Filter::postBool('NEW_SHOW_COUNTER'));
	set_gedcom_setting(KT_GED_ID, 'SHOW_DEAD_PEOPLE',				KT_Filter::post('SHOW_DEAD_PEOPLE'));
	set_gedcom_setting(KT_GED_ID, 'SHOW_EST_LIST_DATES',			KT_Filter::postBool('NEW_SHOW_EST_LIST_DATES'));
	set_gedcom_setting(KT_GED_ID, 'SHOW_FACT_ICONS',				KT_Filter::postBool('NEW_SHOW_FACT_ICONS'));
	set_gedcom_setting(KT_GED_ID, 'SHOW_GEDCOM_RECORD',				KT_Filter::postBool('NEW_SHOW_GEDCOM_RECORD'));
	set_gedcom_setting(KT_GED_ID, 'SHOW_HIGHLIGHT_IMAGES',			KT_Filter::postBool('NEW_SHOW_HIGHLIGHT_IMAGES'));
	set_gedcom_setting(KT_GED_ID, 'SHOW_LAST_CHANGE',				KT_Filter::postBool('NEW_SHOW_LAST_CHANGE'));
	set_gedcom_setting(KT_GED_ID, 'SHOW_LDS_AT_GLANCE',				KT_Filter::postBool('NEW_SHOW_LDS_AT_GLANCE'));
	set_gedcom_setting(KT_GED_ID, 'SHOW_LIVING_NAMES',				KT_Filter::post('SHOW_LIVING_NAMES'));
	set_gedcom_setting(KT_GED_ID, 'SHOW_MEDIA_DOWNLOAD',			KT_Filter::postBool('NEW_SHOW_MEDIA_DOWNLOAD'));
	set_gedcom_setting(KT_GED_ID, 'SHOW_NO_WATERMARK',				KT_Filter::post('NEW_SHOW_NO_WATERMARK'));
	set_gedcom_setting(KT_GED_ID, 'SHOW_PARENTS_AGE',				KT_Filter::postBool('NEW_SHOW_PARENTS_AGE'));
	set_gedcom_setting(KT_GED_ID, 'SHOW_PEDIGREE_PLACES',			KT_Filter::post('NEW_SHOW_PEDIGREE_PLACES'));
	set_gedcom_setting(KT_GED_ID, 'SHOW_PEDIGREE_PLACES_SUFFIX',	KT_Filter::postBool('NEW_SHOW_PEDIGREE_PLACES_SUFFIX'));
	set_gedcom_setting(KT_GED_ID, 'SHOW_PRIVATE_RELATIONSHIPS',		KT_Filter::post('SHOW_PRIVATE_RELATIONSHIPS'));
	set_gedcom_setting(KT_GED_ID, 'SHOW_RELATIVES_EVENTS',			KT_Filter::post('NEW_SHOW_RELATIVES_EVENTS'));
	set_gedcom_setting(KT_GED_ID, 'SOURCE_ID_PREFIX',				KT_Filter::post('NEW_SOURCE_ID_PREFIX'));
	set_gedcom_setting(KT_GED_ID, 'SOUR_FACTS_ADD',					str_replace(' ', '', KT_Filter::post('NEW_SOUR_FACTS_ADD')));
	set_gedcom_setting(KT_GED_ID, 'SOUR_FACTS_QUICK',				str_replace(' ', '', KT_Filter::post('NEW_SOUR_FACTS_QUICK')));
	set_gedcom_setting(KT_GED_ID, 'SOUR_FACTS_UNIQUE',				str_replace(' ', '', KT_Filter::post('NEW_SOUR_FACTS_UNIQUE')));
	set_gedcom_setting(KT_GED_ID, 'SUBLIST_TRIGGER_I',				KT_Filter::post('NEW_SUBLIST_TRIGGER_I', KT_REGEX_INTEGER, 200));
	set_gedcom_setting(KT_GED_ID, 'SURNAME_LIST_STYLE',				KT_Filter::post('NEW_SURNAME_LIST_STYLE'));
	set_gedcom_setting(KT_GED_ID, 'SURNAME_TRADITION',				KT_Filter::post('NEW_SURNAME_TRADITION'));
	set_gedcom_setting(KT_GED_ID, 'THEME_DIR',						KT_Filter::post('NEW_THEME_DIR'));
	set_gedcom_setting(KT_GED_ID, 'COLOR_PALETTE',					KT_Filter::post('NEW_COLOR_PALETTE'));
	set_gedcom_setting(KT_GED_ID, 'THUMBNAIL_WIDTH',				KT_Filter::post('NEW_THUMBNAIL_WIDTH'));
	set_gedcom_setting(KT_GED_ID, 'USE_GEONAMES',					KT_Filter::postBool('NEW_USE_GEONAMES'));
	set_gedcom_setting(KT_GED_ID, 'USE_RIN',						KT_Filter::postBool('NEW_USE_RIN'));
	set_gedcom_setting(KT_GED_ID, 'USE_SILHOUETTE',					KT_Filter::postBool('NEW_USE_SILHOUETTE'));
	set_gedcom_setting(KT_GED_ID, 'WATERMARK_THUMB',				KT_Filter::postBool('NEW_WATERMARK_THUMB'));
	set_gedcom_setting(KT_GED_ID, 'WEBMASTER_USER_ID',				KT_Filter::post('NEW_WEBMASTER_USER_ID'));
	set_gedcom_setting(KT_GED_ID, 'subtitle',						KT_Filter::post('new_subtitle', KT_REGEX_UNSAFE));
	if (KT_Filter::post('gedcom_title')) {
		set_gedcom_setting(KT_GED_ID, 'title', KT_Filter::post('gedcom_title'));
	}

	// Only accept valid folders for NEW_MEDIA_DIRECTORY
	$NEW_MEDIA_DIRECTORY = preg_replace('/[\/\\\\]+/', '/', KT_Filter::post('NEW_MEDIA_DIRECTORY') . '/');
	if (substr($NEW_MEDIA_DIRECTORY, 0, 1) == '/') {
		$NEW_MEDIA_DIRECTORY = substr($NEW_MEDIA_DIRECTORY, 1);
	}

	if ($NEW_MEDIA_DIRECTORY) {
		if (is_dir(KT_DATA_DIR . $NEW_MEDIA_DIRECTORY)) {
			set_gedcom_setting(KT_GED_ID, 'MEDIA_DIRECTORY', $NEW_MEDIA_DIRECTORY);
		} elseif (@mkdir(KT_DATA_DIR . $NEW_MEDIA_DIRECTORY, 0755, true)) {
			set_gedcom_setting(KT_GED_ID, 'MEDIA_DIRECTORY', $NEW_MEDIA_DIRECTORY);
			KT_FlashMessages::addMessage(KT_I18N::translate('The folder %s was created.', KT_DATA_DIR . $NEW_MEDIA_DIRECTORY));
		} else {
			KT_FlashMessages::addMessage(KT_I18N::translate('The folder %s does not exist, and it could not be created.', KT_DATA_DIR . $NEW_MEDIA_DIRECTORY));
		}
	}

	$gedcom = KT_Filter::post('gedcom');
	if ($gedcom && $gedcom != KT_GEDCOM) {
		try {
			KT_DB::prepare("UPDATE `##gedcom` SET gedcom_name = ? WHERE gedcom_id = ?")->execute(array($gedcom, KT_GED_ID));
			KT_DB::prepare("UPDATE `##site_setting` SET setting_value = ? WHERE setting_name='DEFAULT_GEDCOM' AND setting_value = ?")->execute(array($gedcom, KT_GEDCOM));
		} catch (Exception $ex) {
			// Probably a duplicate name.
			$gedcom = KT_GEDCOM;
		}
	}

	// Reload the page, so that the settings take effect immediately.
	Zend_Session::writeClose();
	header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME . '?ged=' . $gedcom);
	exit;
}

$controller
	->pageHeader()
	->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
	->addInlineJavascript('
		autocomplete();

	 	// run test on initial page load
		 checkSize();
		 // run test on resize of the window
		 jQuery(window).resize(checkSize);
		//Function to the css rule
		function checkSize(){
			 if (jQuery("h3.accordion").css("display") == "block" ){
				jQuery("#accordion").accordion({event: "click", collapsible: true, heightStyle: "content"});
			 } else {
				jQuery("#tabs").tabs({ active: ' . $active . ' });
			}
		}

		 jQuery("#tabs").css("visibility", "visible");
	');
?>

<div id="family_tree_config">
	<h2><?php echo KT_I18N::translate('Family tree configuration'); ?></h2>
	<form method="post" id="configform" name="configform" action="<?php echo KT_SCRIPT_NAME; ?>">
		<?php echo KT_Filter::getCsrf(); ?>
		<input type="hidden" name="action" value="update">
		<input type="hidden" name="ged" value="<?php echo htmlspecialchars(KT_GEDCOM); ?>">
		<div id="tabs" style="visibility: hidden;">
			<ul>
				<li><a href="#file-options"><span><?php echo KT_I18N::translate('General'); ?></span></a></li>
				<li><a href="#contact"><span><?php echo KT_I18N::translate('Contact information'); ?></span></a></li>
				<li><a href="#website"><span><?php echo KT_I18N::translate('Website'); ?></span></a></li>
				<li><a href="#privacy"><span><?php echo KT_I18N::translate('Privacy'); ?></span></a></li>
				<li><a href="#config-media"><span><?php echo KT_I18N::translate('Media'); ?></span></a></li>
				<li><a href="#layout-options"><span><?php echo KT_I18N::translate('Layout'); ?></span></a></li>
				<li><a href="#hide-show"><span><?php echo KT_I18N::translate('Hide &amp; show'); ?></span></a></li>
				<li><a href="#edit-options"><span><?php echo KT_I18N::translate('Edit options'); ?></span></a></li>
				<li><a href="#theme"><span><?php echo KT_I18N::translate('Theme'); ?></span></a></li>
			</ul>
			<div id="accordion">
				<!-- GENERAL -->
				<h3 class="accordion"><?php echo KT_I18N::translate('General'); ?></h3>
				<div id="file-options">
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Family tree title'); ?></label>
						<div class="input_group">
							<input type="text" name="gedcom_title" dir="ltr" value="<?php echo KT_Filter::escapeHtml(get_gedcom_setting(KT_GED_ID, 'title')); ?>" required maxlength="255">
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Family tree subtitle'); ?></label>
						<div class="input_group">
							<input type="text" name="new_subtitle"dir="ltr" value="<?php echo KT_Filter::escapeHtml(get_gedcom_setting(KT_GED_ID, 'subtitle')); ?>" maxlength="255">
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('URL'); ?></label>
						<div class="input_group">
							<span class="input_label left"><?php echo KT_SERVER_NAME, KT_SCRIPT_PATH ?>index.php?ged=</span>
							<input type="text" name="gedcom" dir="ltr" value="<?php echo KT_Filter::escapeHtml(KT_GEDCOM); ?>" required maxlength="255">
							<div class="helpcontent">
								<?php /*I18N: Help text for family tree URL */ echo KT_I18N::translate('Avoid spaces and punctuation. A family name might be a good choice.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Language'); ?></label>
						<div class="input_group">
							<?php echo edit_field_language('GEDCOMLANG', $LANGUAGE); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('If a visitor to the site has not specified a preferred language in their browser configuration, or they have specified an unsupported language, then this language will be used. Typically, this setting applies to search engines.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label> <?php echo KT_I18N::translate('Default individual'); ?></label>
						<div class="input_group">
							<input data-autocomplete-type="INDI" type="text" dir="ltr" name="NEW_PEDIGREE_ROOT_ID" id="NEW_PEDIGREE_ROOT_ID" value="<?php echo get_gedcom_setting(KT_GED_ID, 'PEDIGREE_ROOT_ID'); ?>" maxlength="20">
							<span class="input_label right">
								<?php
								$person = KT_Person::getInstance(get_gedcom_setting(KT_GED_ID, 'PEDIGREE_ROOT_ID'));
								if ($person) {
									echo ' <span class="list_item">' . $person->getFullName() . ' ' . $person->getLifeSpan() . '</span>';
								} else {
									echo ' <span class="error">' . KT_I18N::translate('Unable to find record with ID') . '</span>';
								}
								?>
							</span>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This individual will be selected by default when viewing charts and reports.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Calendar conversion'); ?></label>
						<div class="input_group">
							<div class="sub_input_group">
								<select id="NEW_CALENDAR_FORMAT0" name="NEW_CALENDAR_FORMAT0">
									<?php
									$CALENDAR_FORMATS=explode('_and_', $CALENDAR_FORMAT);
									if (count($CALENDAR_FORMATS)==1) {
										$CALENDAR_FORMATS[]='none';
									}
									foreach (array(
										'none'		=> KT_I18N::translate('No calendar conversion'),
										'gregorian'	=> KT_Date_Gregorian::calendarName(),
										'julian'	=> KT_Date_Julian::calendarName(),
										'french'	=> KT_Date_French::calendarName(),
										'jewish'	=> KT_Date_Jewish::calendarName(),
										'hijri'		=> KT_Date_Hijri::calendarName(),
										'jalali'	=> KT_Date_Jalali::calendarName(),
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
										'none'		=> KT_I18N::translate('No calendar conversion'),
										'gregorian'	=> KT_Date_Gregorian::calendarName(),
										'julian'	=> KT_Date_Julian::calendarName(),
										'french'	=> KT_Date_French::calendarName(),
										'jewish'	=> KT_Date_Jewish::calendarName(),
										'hijri'		=> KT_Date_Hijri::calendarName(),
										'jalali'	=> KT_Date_Jalali::calendarName(),
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
							<div class="helpcontent">
								<?php
									$d1 = new KT_Date('22 SEP 1792'); $d1 = $d1->Display(false, null, array());
									$d2 = new KT_Date('31 DEC 1805'); $d2 = $d2->Display(false, null, array());
									$d3 = new KT_Date('15 OCT 1582'); $d3 = $d3->Display(false, null, array());
									echo KT_I18N::translate('Different calendar systems are used in different parts of the world and many other calendar systems have been used in the past. Where possible you should enter dates using the calendar in which the event was originally recorded. You can then specify a conversion to show these dates in a more familiar calendar. If you regularly use two calendars you can specify two conversions and dates will be converted to both the selected calendars.') . '
									<p><b>' . KT_I18N::translate('The following calendars are supported') . '</b></p>
									<ul>
										<li>' . KT_Date_Gregorian::calendarName() . '</li>
										<li>' . KT_Date_Julian::calendarName() . '</li>
										<li>' . KT_Date_Jewish::calendarName() . '</li>
										<li>' . KT_Date_French::calendarName() . '</li>
										<li>' . KT_Date_Hijri::calendarName() . '</li>
										<li>' . KT_Date_Jalali::calendarName() . '</li>
									</ul>
									<p>' . /* I18N: The three place holders are all dates. */ KT_I18N::translate('Dates are only converted if they are valid for the calendar. For example, only dates between %1$s and %2$s will be converted to the French calendar and only dates after %3$s will be converted to the Gregorian calendar.', $d1, $d2, $d3) . '</p>
									<p>' . KT_I18N::translate('In some calendars days start at midnight. In other calendars days start at sunset. The conversion process does not take account of the time so for any event that occurs between sunset and midnight, the conversion between these types of calendar will be one day out.') . '</p>';
								?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Use RIN number instead of GEDCOM ID'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_USE_RIN', get_gedcom_setting(KT_GED_ID, 'USE_RIN')); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('Set to <b>Yes</b> to use the RIN number instead of the GEDCOM ID when asked for Individual IDs in configuration files, user settings, and charts. This is useful for genealogy programs that do not consistently export GEDCOMs with the same ID assigned to each individual but always use the same RIN.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Automatically create globally unique IDs'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_GENERATE_UIDS', get_gedcom_setting(KT_GED_ID, 'GENERATE_UIDS')); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('<b>GUID</b> in this context is an acronym for «Globally Unique ID».<br>GUIDs are intended to help identify each individual in a manner that is repeatable, so that central organizations such as the Family History Center of the LDS Church in Salt Lake City, or even compatible programs running on your own server, can determine whether they are dealing with the same person no matter where the GEDCOM originates. The goal of the Family History Center is to have a central repository of genealogical data and expose it through web services. This will enable any program to access the data and update their data within it.<br><br>If you do not intend to share this GEDCOM with anyone else, you do not need to let kiwitrees create these GUIDs; however, doing so will do no harm other than increasing the size of your GEDCOM.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('XREF prefixes'); ?></label>
						<div class="input_group">
							<div class="sm_input_group">
								<span class="input_label"><?php echo KT_I18N::translate('Individual'); ?></span>
								<input type="text" name="NEW_GEDCOM_ID_PREFIX" dir="ltr" value="<?php echo $GEDCOM_ID_PREFIX; ?>" size="5" maxlength="20">
							</div>
							<div class="sm_input_group">
								<span class="input_label"><?php echo KT_I18N::translate('Family'); ?></span>
								<input type="text" name="NEW_FAM_ID_PREFIX" dir="ltr" value="<?php echo $FAM_ID_PREFIX; ?>" size="5" maxlength="20">
							</div>
							<div class="sm_input_group">
								<span class="input_label"><?php echo KT_I18N::translate('Source'); ?></span>
								<input type="text" name="NEW_SOURCE_ID_PREFIX" dir="ltr" value="<?php echo $SOURCE_ID_PREFIX; ?>" size="5" maxlength="20">
							</div>
							<div class="sm_input_group">
								<span class="input_label"><?php echo KT_I18N::translate('Repository'); ?></span>
								<input type="text" name="NEW_REPO_ID_PREFIX" dir="ltr" value="<?php echo $REPO_ID_PREFIX; ?>" size="5" maxlength="20">
							</div>
							<div class="sm_input_group">
								<span class="input_label"><?php echo KT_I18N::translate('Media'); ?></span>
								<input type="text" name="NEW_MEDIA_ID_PREFIX" dir="ltr" value="<?php echo $MEDIA_ID_PREFIX; ?>" size="5" maxlength="20">
							</div>
							<div class="sm_input_group">
								<span class="input_label"><?php echo KT_I18N::translate('Note'); ?></span>
								<input type="text" name="NEW_NOTE_ID_PREFIX" dir="ltr" value="<?php echo $NOTE_ID_PREFIX; ?>" size="5" maxlength="20">
							</div>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('In a family tree, each record has an internal reference number (called an “XREF”) such as “F123” or “R14”.	You can choose the prefix that will be used whenever <b>new</b> XREFs are created.'); ?>
							</div>
						</div>
					</div>
				</div>
				<!-- CONTACT -->
				<h3 class="accordion"><?php echo KT_I18N::translate('Contact information'); ?></h3>
				<div id="contact">
					<div class="config_options">
						<?php if (empty($KIWITREES_EMAIL)) {
							$KIWITREES_EMAIL = "kiwitrees-noreply@".preg_replace("/^www\./i", "", $_SERVER["SERVER_NAME"]);
						} ?>
						<label><?php echo KT_I18N::translate('Kiwitrees reply address'); ?></label>
						<div class="input_group">
							<input type="text" name="NEW_KIWITREES_EMAIL" required value="<?php echo $KIWITREES_EMAIL; ?>" size="50" maxlength="255" dir="ltr">
							<div class="helpcontent">
								<?php echo KT_I18N::translate('Email address to be used in the “From:” field of emails that kiwitrees creates automatically.<br>Kiwitrees can automatically create emails to notify administrators of changes that need to be reviewed. Kiwitrees also sends notification emails to users who have requested an account.<br><br>Usually, the “From:” field of these automatically created emails is something like From: kiwitrees-noreply@yoursite to show that no response to the email is required. To guard against spam or other email abuse, some email systems require each message’s “From:” field to reflect a valid email account and will not accept messages that are apparently from account kiwitrees-noreply.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Genealogy contact'); ?></label>
						<div class="input_group">
							<select name="NEW_CONTACT_USER_ID">
								<?php $CONTACT_USER_ID = get_gedcom_setting(KT_GED_ID, 'CONTACT_USER_ID');
								echo '<option value="" ';
									if ($CONTACT_USER_ID == '') echo ' selected="selected"';
								echo '>'. KT_I18N::translate('none'), '</option>';
								foreach (get_all_users() as $user_id=>$user_name) {
									if (get_user_setting($user_id, 'verified_by_admin')) {
										echo '<option value="' . $user_id . '"';
										if ($CONTACT_USER_ID == $user_id) echo ' selected="selected"';
										echo '>' . getUserFullName($user_id) . ' - ' . $user_name . '</option>';
									}
								} ?>
							</select>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('The person to contact about the genealogical data on this site.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Technical help contact'); ?></label>
						<div class="input_group">
							<select name="NEW_WEBMASTER_USER_ID">
							<?php
								$WEBMASTER_USER_ID=get_gedcom_setting(KT_GED_ID, 'WEBMASTER_USER_ID');
								echo '<option value="" ';
								if ($WEBMASTER_USER_ID=='') echo ' selected="selected"';
								echo '>'. KT_I18N::translate('none'), '</option>';
								foreach (get_all_users() as $user_id=>$user_name) {
									if (userIsAdmin($user_id)) {
										echo '<option value="'.$user_id.' "';
										if ($WEBMASTER_USER_ID==$user_id) echo ' selected="selected"';
										echo '>'.getUserFullName($user_id).' - '.$user_name.'</option>';
									}
								}
							?>
							</select>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('The person to be contacted about technical questions or errors encountered on your site.'); ?>
							</div>
						</div>
					</div>
				</div>
				<!-- WEBSITE -->
				<h3 class="accordion"><?php echo KT_I18N::translate('Website'); ?></h3>
				<div id="website">
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Add to TITLE header tag'); ?></label>
						<div class="input_group">
							<input type="text" dir="ltr" name="NEW_META_TITLE" value="<?php echo htmlspecialchars(get_gedcom_setting(KT_GED_ID, 'META_TITLE')); ?>" size="40" maxlength="255">
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This text will be appended to each page title. It will be shown in the browser’s title bar, bookmarks, etc.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Description META tag'); ?></label>
						<div class="input_group">
							<input type="text" dir="ltr" name="NEW_META_DESCRIPTION" value="<?php echo get_gedcom_setting(KT_GED_ID, 'META_DESCRIPTION'); ?>" size="40" maxlength="255">
							<div class="helpcontent">
								<?php echo KT_I18N::translate('The value to place in the “meta description” tag in the HTML page header. Leave this field empty to use the name of the currently active family tree.'); ?>
							</div>
						</div>
					</div>
				</div>
				<!-- PRIVACY OPTIONS -->
				<h3 class="accordion"><?php echo KT_I18N::translate('Privacy'); ?></h3>
				<div id="privacy">
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Enable privacy'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_HIDE_LIVE_PEOPLE', $HIDE_LIVE_PEOPLE); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This option will enable all privacy settings and hide the details of living people, as defined or modified below. If privacy is not enabled kiwitrees will ignore all the other settings on this page.'); ?>
								<?php echo KT_I18N::plural('<b>Note:</b> "living" is defined (if no death or burial is known) as ending %d year after birth or estimated birth.','<b>Note:</b> "living" is defined (if no death or burial is known) as ending %d years after birth or estimated birth.', get_gedcom_setting(KT_GED_ID, 'MAX_ALIVE_AGE'), get_gedcom_setting(KT_GED_ID, 'MAX_ALIVE_AGE')); ?>
								<br>
								<?php echo KT_I18N::translate('The length of time after birth can be set using the option "Age at which to assume a person is dead".'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Show dead people'); ?></label>
						<div class="input_group">
							<?php echo edit_field_access_level("SHOW_DEAD_PEOPLE", get_gedcom_setting(KT_GED_ID, 'SHOW_DEAD_PEOPLE')); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('Set the privacy access level for all dead people.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Age at which to assume a person is dead'); ?></label>
						<div class="input_group">
							<input type="text" name="MAX_ALIVE_AGE" value="<?php echo get_gedcom_setting(KT_GED_ID, 'MAX_ALIVE_AGE'); ?>" size="5" maxlength="3">
							<?php echo KT_I18N::translate('years'); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('If this person has any events other than death, burial, or cremation more recent than this number of years, they are considered to be "alive". Children\'s birth dates are considered to be such events for this purpose.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php /* I18N: ... [who were] born in the last XX years or died in the last YY years */ echo KT_I18N::translate('Extend privacy of dead people'); ?></label>
						<div class="input_group">
							<?php echo /* I18N: ... Extend privacy to dead people [who were] ... */ KT_I18N::translate(
									'born in the last %1$s years or died in the last %2$s years',
									'<input type="text" name="KEEP_ALIVE_YEARS_BIRTH" value="'.get_gedcom_setting(KT_GED_ID, 'KEEP_ALIVE_YEARS_BIRTH').'" size="5" maxlength="3">',
									'<input type="text" name="KEEP_ALIVE_YEARS_DEATH" value="'.get_gedcom_setting(KT_GED_ID, 'KEEP_ALIVE_YEARS_DEATH').'" size="5" maxlength="3">'
								); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('In some countries privacy laws apply not only to living people but also to those who have died recently. This option allows you to extend the privacy rules for living people to those who were born or died within a specified number of years. Leave these values at zero to disable this feature.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Names of private individuals'); ?></label>
						<div class="input_group">
							<?php echo edit_field_access_level("SHOW_LIVING_NAMES", get_gedcom_setting(KT_GED_ID, 'SHOW_LIVING_NAMES')); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This option will show the names (but no other details) of private individuals. Individuals are private if they are still alive or if a privacy restriction has been added to their individual record. To hide a specific name, add a privacy restriction to that name record.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Show private relationships'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('SHOW_PRIVATE_RELATIONSHIPS', get_gedcom_setting(KT_GED_ID, 'SHOW_PRIVATE_RELATIONSHIPS')); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This option will retain family links in private records. This means you will see empty "private" boxes on the pedigree chart and on other charts with private people.'); ?>
							</div>
						</div>
					</div>
					<hr>
					<h3><?php echo KT_I18N::translate('Privacy restrictions'); ?></h3>
					<div class="helpcontent">
						<?php echo KT_I18N::translate('You can set the access for a specific record, fact, or event by adding a restriction to it. If a record, fact, or event does not have a restriction the following default restrictions will be used.'); ?>
					</div>
					<div class="config_options">
						<?php
						$all_tags	= array();
						$tags		= array_unique(array_merge(
							explode(',', get_gedcom_setting(KT_GED_ID, 'INDI_FACTS_ADD')), explode(',', get_gedcom_setting(KT_GED_ID, 'INDI_FACTS_UNIQUE')),
							explode(',', get_gedcom_setting(KT_GED_ID, 'FAM_FACTS_ADD' )), explode(',', get_gedcom_setting(KT_GED_ID, 'FAM_FACTS_UNIQUE' )),
							explode(',', get_gedcom_setting(KT_GED_ID, 'NOTE_FACTS_ADD')), explode(',', get_gedcom_setting(KT_GED_ID, 'NOTE_FACTS_UNIQUE')),
							explode(',', get_gedcom_setting(KT_GED_ID, 'SOUR_FACTS_ADD')), explode(',', get_gedcom_setting(KT_GED_ID, 'SOUR_FACTS_UNIQUE')),
							explode(',', get_gedcom_setting(KT_GED_ID, 'REPO_FACTS_ADD')), explode(',', get_gedcom_setting(KT_GED_ID, 'REPO_FACTS_UNIQUE')),
							array('SOUR', 'REPO', 'OBJE', '_PRIM', 'NOTE', 'SUBM', 'SUBN', '_UID', 'CHAN')
						));

						foreach ($tags as $tag) {
							if ($tag) {
								$all_tags[$tag] = KT_Gedcom_Tag::getLabel($tag);
							}
						}

						uasort($all_tags, 'utf8_strcasecmp');
						?>
						<table>
							<thead>
								<tr>
									<th><?php echo KT_I18N::translate('Record'); ?></th>
									<th><?php echo KT_I18N::translate('Fact or event'); ?></th>
									<th><?php echo KT_I18N::translate('Access level'); ?></th>
									<th><?php echo KT_I18N::translate('Action'); ?></th>
								</tr>
							</thead>
							<tbody>
								<tr class="even">
									<td><input data-autocomplete-type="IFSRO" type="text" class="pedigree_form" name="xref" id="xref" dir="ltr" maxlength="20" placeholder="<?php echo /* I18N: a placeholder for input of all or any record type */ KT_I18N::translate('All records'); ?>"></td>
									<td><?php echo select_edit_control('tag_type', $all_tags, '', null, null); ?></td>
									<td><?php echo select_edit_control('resn', $PRIVACY_CONSTANTS, null, 'privacy', null); ?></td>
									<td>
										<button class="btn btn-primary" type="submit" onClick="document.configform.elements['action'].value='add';document.configform.submit();">
											<i class="fa fa-plus"></i>
											<?php echo KT_I18N::translate('add'); ?>
										</button>
										<input type="hidden" name="default_resn_id" value=""><!-- value set by JS -->
									</td>
								</tr>
								<?php
								$rows = KT_DB::prepare(
									"SELECT default_resn_id, tag_type, xref, resn".
									" FROM `##default_resn`".
									" LEFT JOIN `##name` ON (gedcom_id=n_file AND xref=n_id AND n_num=0)".
									" WHERE gedcom_id=?".
									" ORDER BY xref IS NULL, n_sort, xref, tag_type"
								)->execute(array(KT_GED_ID))->fetchAll();
								$n = 1;
								foreach ($rows as $row) { ?>
									<tr class="<?php echo ($n % 2 == 0 ? 'even' : 'odd'); ?>">
										<td>
											<?php
											$n++;
											if ($row->xref) {
												$record = KT_GedcomRecord::getInstance($row->xref);
												if ($record) {
													echo '<a href="', $record->getHtmlUrl(), '">', $record->getFullName(), '</a>';
												} else {
													echo KT_I18N::translate('this record does not exist');
												}
											} else {
												echo '&nbsp;';
											} ?>
										</td>
										<td>
											<?php if ($row->tag_type) {
												// I18N: e.g. Marriage (MARR)
												echo KT_Gedcom_Tag::getLabel($row->tag_type);
											} else {
												echo '&nbsp;';
											} ?>
										</td>
										<td>
											<?php echo $PRIVACY_CONSTANTS[$row->resn]; ?>
										</td>
										<td>
											<button class="btn btn-primary" type="submit" onClick="document.configform.elements['action'].value='delete';if (confirm('<?php echo htmlspecialchars(KT_I18N::translate('Are you sure you want to delete “%s”?', KT_Gedcom_Tag::getLabel($row->tag_type))); ?>')) { document.configform.elements['default_resn_id'].value='<?php echo $row->default_resn_id; ?>';document.configform.submit();}">
												<i class="fa fa-trash-o"></i>
												<?php echo KT_I18N::translate('delete'); ?>
											</button>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
				<!-- MEDIA -->
				<h3 class="accordion"><?php echo KT_I18N::translate('Media'); ?></h3>
				<div id="config-media">
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Media folder'); ?></label>
						<div class="input_group">
							<span class="input_label left"><?php echo KT_DATA_DIR; ?></span>
							<input type="text" name="NEW_MEDIA_DIRECTORY" value="<?php echo $MEDIA_DIRECTORY; ?>" dir="ltr" maxlength="255">
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This folder will be used to store the media files for this family tree. If you select a different folder you must also move any media files from the existing folder to the new one. If two family trees use the same media folder they will be able to share media files. If they use different media folders their media files will be kept separate.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Who can upload new media files'); ?></label>
						<div class="input_group">
							<?php echo select_edit_control('NEW_MEDIA_UPLOAD', $privacy, null, get_gedcom_setting(KT_GED_ID, 'MEDIA_UPLOAD')); ?>
							 <div class="helpcontent">
								<?php echo KT_I18N::translate('If you are concerned that users might upload inappropriate images, you can restrict media uploads to managers only.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Show download link in media viewer'); ?></label>
						<div class="input_group">
							<?php echo select_edit_control('NEW_SHOW_MEDIA_DOWNLOAD',$privacy, null, get_gedcom_setting(KT_GED_ID, 'SHOW_MEDIA_DOWNLOAD')); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('The media viewer can show a link which when clicked will download the media file to the local PC.<br><br>You may want to hide the download link for security reasons.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Width of generated thumbnails'); ?></label>
						<div class="input_group">
							<input type="text" name="NEW_THUMBNAIL_WIDTH" value="<?php echo $THUMBNAIL_WIDTH; ?>" maxlength="4" required>
							<span class="input_label right"><?php echo /* I18N: the suffix to a media size */ KT_I18N::translate('pixels'); ?></span>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This is the width (in pixels) that the program will use when automatically generating thumbnails. The default setting is 100.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Use silhouettes'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_USE_SILHOUETTE', get_gedcom_setting(KT_GED_ID, 'USE_SILHOUETTE')); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('Use silhouette images when no highlighted image for that individual has been specified. The images used are specific to the gender of the individual in question and may also vary according to the theme you use.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Show highlight images in people boxes'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_SHOW_HIGHLIGHT_IMAGES', get_gedcom_setting(KT_GED_ID, 'SHOW_HIGHLIGHT_IMAGES')); ?>
							<div class="helpcontent">
								<a href="http://kiwitrees.net/highlighted-images/" target="_blank" rel="noopener noreferrer">
									<?php echo KT_I18N::translate('Click here to view more information about highlight images on the kiwitrees.net website FAQs'); ?>
								</a>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Full size images without watermarks'); ?></label>
						<div class="input_group">
							<?php echo edit_field_access_level("NEW_SHOW_NO_WATERMARK", $SHOW_NO_WATERMARK); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('Watermarks are optional and normally shown just to visitors.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Add watermarks to thumbnails'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_WATERMARK_THUMB', get_gedcom_setting(KT_GED_ID, 'WATERMARK_THUMB')); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('A watermark is text that is added to an image to discourage others from copying it without permission. If you select yes further options will be available.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Store watermarked full size images on server?'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_SAVE_WATERMARK_IMAGE', get_gedcom_setting(KT_GED_ID, 'SAVE_WATERMARK_IMAGE')); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('Watermarks can be slow to generate for large images. Busy sites may prefer to generate them once and store the watermarked image on the server.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Store watermarked thumbnails on server'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_SAVE_WATERMARK_THUMB', get_gedcom_setting(KT_GED_ID, 'SAVE_WATERMARK_THUMB')); ?>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('External image editor'); ?></label>
						<div class="input_group">
							<input type="url" name="NEW_IMAGE_EDITOR" required value="<?php echo $IMAGE_EDITOR; ?>" size="50" maxlength="255" dir="ltr">
							<div class="helpcontent">
								<?php echo KT_I18N::translate('Preferred URL link to an external image editor provided for members use when uploading media images. The default link is %s>', $IMAGE_EDITOR); ?>
							</div>
						</div>
					</div>
				</div>
				<!-- LAYOUT -->
				<h3 class="accordion"><?php echo KT_I18N::translate('Layout'); ?></h3>
				<div id="layout-options">
					<h4 class="accepted"><?php echo KT_I18N::translate('Names'); ?></h4>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Min. no. of occurrences to be a "common surname"'); ?></label>
						<div class="input_group">
							<input type="text" name="NEW_COMMON_NAMES_THRESHOLD" value="<?php echo get_gedcom_setting(KT_GED_ID, 'COMMON_NAMES_THRESHOLD'); ?>" maxlength="5" required>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This is the number of times a surname must occur before it shows up in the Common Surname list on the "Statistics block".'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Names to add to common surnames'); ?></label>
						<div class="input_group">
							<input type="text" name="NEW_COMMON_NAMES_ADD" dir="ltr" value="<?php echo get_gedcom_setting(KT_GED_ID, 'COMMON_NAMES_ADD'); ?>" maxlength="255">
							<div class="helpcontent">
								<?php echo KT_I18N::translate('If the number of times that a certain surname occurs is lower than the threshold, it will not appear in the list. It can be added here manually. If more than one surname is entered, they must be separated by a comma. <b>Surnames are case-sensitive.</b>'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Names to remove from common surnames (comma separated)'); ?></label>
						<div class="input_group">
							<input type="text" name="NEW_COMMON_NAMES_REMOVE" dir="ltr" value="<?php echo get_gedcom_setting(KT_GED_ID, 'COMMON_NAMES_REMOVE'); ?>" maxlength="255">
							<div class="helpcontent">
								<?php echo KT_I18N::translate('If you want to remove a surname from the Common Surname list without increasing the threshold value, you can do that by entering the surname here. If more than one surname is entered, they must be separated by a comma. <b>Surnames are case-sensitive.</b> Surnames entered here will also be removed from the Top-10 list on the Home Page.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Display surnames in all CAPS'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_ALL_CAPS', get_gedcom_setting(KT_GED_ID, 'ALL_CAPS')); ?>
						 </div>
					</div>
					<h4 class="accepted"><?php echo KT_I18N::translate('Lists'); ?></h4>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Surname list style'); ?></label>
						<div class="input_group">
							<select name="NEW_SURNAME_LIST_STYLE">
								<option value="style1" <?php if ($SURNAME_LIST_STYLE=="style1") echo "selected=\"selected\""; ?>><?php echo KT_I18N::translate('list'); ?></option>
								<option value="style2" <?php if ($SURNAME_LIST_STYLE=="style2") echo "selected=\"selected\""; ?>><?php echo KT_I18N::translate('table'); ?></option>
								<option value="style3" <?php if ($SURNAME_LIST_STYLE=="style3") echo "selected=\"selected\""; ?>><?php echo KT_I18N::translate('tag cloud'); ?></option>
							</select>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Maximum number of surnames on individual list'); ?></label>
						<div class="input_group">
							<input type="text" name="NEW_SUBLIST_TRIGGER_I" value="<?php echo get_gedcom_setting(KT_GED_ID, 'SUBLIST_TRIGGER_I'); ?>" maxlength="5" required>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('Long lists of people with the same surname can be broken into smaller sub-lists according to the first letter of the individual\'s given name.<br>This option determines when sub-listing of surnames will occur. To disable sub-listing completely, set this option to zero.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Estimated dates for birth and death'); ?></label>
						<div class="input_group">
							<?php echo radio_buttons('NEW_SHOW_EST_LIST_DATES', array(false=>KT_I18N::translate('hide'), true=>KT_I18N::translate('show')), get_gedcom_setting(KT_GED_ID, 'SHOW_EST_LIST_DATES'), 'class="radio_inline"'); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This option controls whether or not to show estimated dates for birth and death instead of leaving blanks on individual lists and charts for individuals whose dates are not known.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('The date and time of the last update'); ?></label>
						<div class="input_group">
							<?php echo radio_buttons('NEW_SHOW_LAST_CHANGE', array(false=>KT_I18N::translate('hide'), true=>KT_I18N::translate('show')), $SHOW_LAST_CHANGE, 'class="radio_inline"'); ?>
						</div>
					</div>
					<h4 class="accepted"><?php echo KT_I18N::translate('Charts'); ?></h4>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Default pedigree chart layout'); ?></label>
						<div class="input_group">
							<select name="NEW_PEDIGREE_LAYOUT">
								<option value="yes" <?php if ($PEDIGREE_LAYOUT) echo "selected=\"selected\""; ?>><?php echo KT_I18N::translate('Landscape'); ?></option>
								<option value="no" <?php if (!$PEDIGREE_LAYOUT) echo "selected=\"selected\""; ?>><?php echo KT_I18N::translate('Portrait'); ?></option>
							</select>
							<div class="helpcontent">
								<?php echo /* I18N: Help text for the “Default pedigree chart layout” tree configuration setting */ KT_I18N::translate('This option indicates whether the Pedigree chart should be generated in landscape or portrait mode.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Default pedigree generations'); ?></label>
						<div class="input_group">
							<input type="text" name="NEW_DEFAULT_PEDIGREE_GENERATIONS" value="<?php echo $DEFAULT_PEDIGREE_GENERATIONS; ?>" maxlength="3" required>
							<div class="helpcontent">
								<?php echo /* I18N: Help text for the “Default pedigree chart layout” tree configuration setting */ KT_I18N::translate('Set the default number of generations to display on Descendancy and Pedigree charts.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Maximum pedigree generations'); ?></label>
						<div class="input_group">
							<input type="text" name="NEW_MAX_PEDIGREE_GENERATIONS" value="<?php echo $MAX_PEDIGREE_GENERATIONS; ?>" maxlength="3">
							<div class="helpcontent">
								<?php echo /* I18N: Help text for the “Maximum pedigree generations” tree configuration setting */ KT_I18N::translate('Set the maximum number of generations to display on Pedigree charts.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Maximum descendancy generations'); ?></label>
						<div class="input_group">
							<input type="text" name="NEW_MAX_DESCENDANCY_GENERATIONS" value="<?php echo $MAX_DESCENDANCY_GENERATIONS; ?>" maxlength="3">
							<div class="helpcontent">
								<?php echo /* I18N: Help text for the “Maximum descendancy generations” tree configuration setting */ KT_I18N::translate('Set the maximum number of generations to display on Descendancy charts.'); ?>
							</div>
						 </div>
					</div>
					<h4 class="accepted"><?php echo KT_I18N::translate('Individual pages'); ?></h4>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Show events of close relatives on individual page'); ?></label>
						<input type="hidden" name="NEW_SHOW_RELATIVES_EVENTS" value="<?php echo $SHOW_RELATIVES_EVENTS; ?>">
						<div class="input_group">
							<table id="relatives">
								<?php
								$rel_events=array(
									array('_BIRT_GCHI', '_MARR_GCHI', '_DEAT_GCHI'),
									array('_BIRT_CHIL', '_MARR_CHIL', '_DEAT_CHIL'),
									array('_BIRT_SIBL', '_MARR_SIBL', '_DEAT_SIBL'),
									array(null,			null,			'_DEAT_SPOU'),
									array(null,			'_MARR_PARE', '_DEAT_PARE'),
									array(null,			null,			'_DEAT_GPAR'),
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
											echo " onchange=\"var old=document.configform.NEW_SHOW_RELATIVES_EVENTS.value; if (this.checked) old+=','+this.value; else old=old.replace(/" . $col . "/g,''); old=old.replace(/[,]+/gi,','); old=old.replace(/^[,]/gi,''); old=old.replace(/[,]$/gi,''); document.configform.NEW_SHOW_RELATIVES_EVENTS.value=old\"> ";
											echo KT_Gedcom_Tag::getLabel($col);
										}
										echo '</td>';
									}
									echo '</tr>';
								}
								?>
							</table>
						 </div>
					</div>
					<h4 class="accepted"><?php echo KT_I18N::translate('Places'); ?></h4>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Abbreviate place names'); ?></label>
						<div class="input_group">
							<?php
							echo /* I18N: The placeholders are edit controls. Show the [first/last] [1/2/3/4/5] parts of a place name */ KT_I18N::translate(
								'Show the %1$s %2$s parts of a place name.',
								select_edit_control('NEW_SHOW_PEDIGREE_PLACES_SUFFIX',
									array(
										false=>KT_I18N::translate_c('Show the [first/last] [N] parts of a place name.', 'first'),
										true =>KT_I18N::translate_c('Show the [first/last] [N] parts of a place name.', 'last')
									),
									null,
									get_gedcom_setting(KT_GED_ID, 'SHOW_PEDIGREE_PLACES_SUFFIX')
								),
								select_edit_control('NEW_SHOW_PEDIGREE_PLACES',
									array(
										1=>KT_I18N::number(1),
										2=>KT_I18N::number(2),
										3=>KT_I18N::number(3),
										4=>KT_I18N::number(4),
										5=>KT_I18N::number(5),
										6=>KT_I18N::number(6),
										7=>KT_I18N::number(7),
										8=>KT_I18N::number(8),
										9=>KT_I18N::number(9),
									),
									null,
									get_gedcom_setting(KT_GED_ID, 'SHOW_PEDIGREE_PLACES')
								)
							);
							?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('Place names are frequently too long to fit on charts, lists, etc. They can be abbreviated by showing just the first few parts of the name, such as <i>village, county</i>, or the last few part of it, such as <i>region, country</i>.'); ?>
							</div>
						 </div>
					</div>
				</div>
				<!-- HIDE & SHOW -->
				<h3 class="accordion"><?php echo KT_I18N::translate('Hide &amp; show'); ?></h3>
				<div id="hide-show">
					<h4 class="accepted"><?php echo KT_I18N::translate('Charts'); ?></h4>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Abbreviate chart labels'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_ABBREVIATE_CHART_LABELS', get_gedcom_setting(KT_GED_ID, 'ABBREVIATE_CHART_LABELS')); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This option controls whether or not to abbreviate labels like <b>Birth</b> on charts with just the first letter like <b>B</b>.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Show chart details by default'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_PEDIGREE_FULL_DETAILS', get_gedcom_setting(KT_GED_ID, 'PEDIGREE_FULL_DETAILS')); ?>
							<div class="helpcontent">
								<?php echo /* I18N: Help text for the “Show chart details by default” tree configuration setting */ KT_I18N::translate('This is the initial setting for the “show details” option on the charts.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Gender icon on charts'); ?></label>
						<div class="input_group">
							<?php echo radio_buttons('NEW_PEDIGREE_SHOW_GENDER', array(false=>KT_I18N::translate('hide'), true=>KT_I18N::translate('show')), $PEDIGREE_SHOW_GENDER, 'class="radio_inline"'); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This option controls whether or not to show the individual\'s gender icon on charts.<br>Since the gender is also indicated by the color of the box, this option doesn\'t conceal the gender. The option simply removes some duplicate information from the box.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Age of parents next to child\'s birth date'); ?></label>
						<div class="input_group">
							<?php echo radio_buttons('NEW_SHOW_PARENTS_AGE', array(false=>KT_I18N::translate('hide'), true=>KT_I18N::translate('show')), $SHOW_PARENTS_AGE, 'class="radio_inline"'); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This option controls whether or not to show age of father and mother next to child\'s birth date on charts.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('LDS ordinance codes in chart boxes'); ?></label>
						<div class="input_group">
							<?php echo radio_buttons('NEW_SHOW_LDS_AT_GLANCE', array(false=>KT_I18N::translate('hide'), true=>KT_I18N::translate('show')), $SHOW_LDS_AT_GLANCE, 'class="radio_inline"'); ?>
							<div class="helpcontent">
								<?php echo /* I18N: Help for LDS ordinances show/hide option */ KT_I18N::translate('Setting this option to <b>Yes</b> will show status codes for LDS ordinances in all chart boxes.<ul><li><b>B</b> - Baptism</li><li><b>E</b> - Endowed</li><li><b>S</b> - Sealed to spouse</li><li><b>P</b> - Sealed to parents</li></ul>A person who has all of the ordinances done will have <b>BESP</b> printed after their name. Missing ordinances are indicated by <b>_</b> in place of the corresponding letter code. For example, <b>BE__</b> indicates missing <b>S</b> and <b>P</b> ordinances.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Other facts to show in charts'); ?></label>
						<div class="input_group">
							<input type="text" id="NEW_CHART_BOX_TAGS" name="NEW_CHART_BOX_TAGS" value="<?php echo $CHART_BOX_TAGS; ?>" dir="ltr" maxlength="255">
							<span class="input_label right">
								<?php echo print_findfact_edit_link('NEW_CHART_BOX_TAGS'); ?>
							</span>
							<div class="helpcontent">
								<?php echo /* I18N: Help for Other facts to show in charts */ KT_I18N::translate('This should be a comma or space separated list of facts, in addition to Birth and Death, that you want to appear in chart boxes such as the Pedigree chart. This list requires you to use fact tags as defined in the GEDCOM 5.5.1 Standard. For example, if you wanted the occupation to show up in the box, you would add "OCCU" to this field. Either enter the tags manually here or use the edit selector.'); ?>
							</div>
						 </div>
					</div>
					<h4 class="accepted"><?php echo KT_I18N::translate('Individual pages'); ?></h4>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Fact icons'); ?></label>
						<div class="input_group">
							<?php echo radio_buttons('NEW_SHOW_FACT_ICONS', array(false=>KT_I18N::translate('hide'), true=>KT_I18N::translate('show')), $SHOW_FACT_ICONS, 'class="radio_inline"'); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('Set this to <b>Yes</b> to display icons near Fact names on the Personal Facts and Details page. Fact icons will be displayed only if they exist in the <i>images/facts</i> directory of the current theme.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Automatically expand notes'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_EXPAND_NOTES', get_gedcom_setting(KT_GED_ID, 'EXPAND_NOTES')); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This option controls whether or not to automatically display content of a <i>Note</i> record on the Individual page.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Automatically expand sources'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_EXPAND_SOURCES', get_gedcom_setting(KT_GED_ID, 'EXPAND_SOURCES')); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This option controls whether or not to automatically display content of a <i>Source</i> record on the Individual page.'); ?>
							</div>
						 </div>
					</div>
					<h4 class="accepted"><?php echo KT_I18N::translate('General'); ?></h4>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Allow users to see raw GEDCOM records'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_SHOW_GEDCOM_RECORD', get_gedcom_setting(KT_GED_ID, 'SHOW_GEDCOM_RECORD')); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('Setting this to <b>Yes</b> will place links on individuals, sources, and families page menus to let users bring up another window containing the raw data in GEDCOM file format.<br>Administrators always see these links regardless of this setting.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('GEDCOM errors'); ?></label>
						<div class="input_group">
							<?php echo radio_buttons('NEW_HIDE_GEDCOM_ERRORS', array(true=>KT_I18N::translate('hide'), false=>KT_I18N::translate('show')), $HIDE_GEDCOM_ERRORS, 'class="radio_inline"'); /* Note: name of object is reverse of description */ ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('Many genealogy programs create GEDCOM files with custom tags, and kiwitrees understands most of them. When unrecognised tags are found, this option lets you choose whether to ignore them or display a warning message.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Hit counters'); ?></label>
						<div class="input_group">
							<?php echo radio_buttons('NEW_SHOW_COUNTER', array(false=>KT_I18N::translate('hide'), true=>KT_I18N::translate('show')), $SHOW_COUNTER, 'class="radio_inline"'); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('Show hit counters on the Home and Individual pages.'); ?>
							</div>
						 </div>
					</div>
				</div>
				<!-- EDIT -->
				<h3 class="accordion"><?php echo KT_I18N::translate('Edit options'); ?></h3>
				<div id="edit-options">
					<h4 class="accepted"><?php echo KT_I18N::translate('Facts for Individual records'); ?></h4>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('All individual facts'); ?></label>
						<div class="input_group">
							<input type="text" id="NEW_INDI_FACTS_ADD" name="NEW_INDI_FACTS_ADD" value="<?php echo get_gedcom_setting(KT_GED_ID, 'INDI_FACTS_ADD'); ?>" maxlength="255" dir="ltr">
							<span class="input_label right">
								<?php echo print_findfact_edit_link('NEW_INDI_FACTS_ADD'); ?>
							</span>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This is the list of GEDCOM facts that your users can add to individuals. You can modify this list by removing or adding fact names, even custom ones, as necessary. <span style="color: #ff0000;">Fact names that appear in this list must not also appear in the <b>Unique individual facts</b> list.</span> Either enter the tags manually here or use the edit selector.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Unique individual facts'); ?></label>
						<div class="input_group">
							<input type="text" id="NEW_INDI_FACTS_UNIQUE" name="NEW_INDI_FACTS_UNIQUE" value="<?php echo get_gedcom_setting(KT_GED_ID, 'INDI_FACTS_UNIQUE'); ?>" maxlength="255" dir="ltr">
							<span class="input_label right">
								<?php echo print_findfact_edit_link('NEW_INDI_FACTS_UNIQUE'); ?>
							</span>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This is the list of GEDCOM facts that your users can only add <u>once</u> to individuals. For example, if BIRT is in this list, users will not be able to add more than one BIRT record to an individual. <span style="color: #ff0000;">Fact names that appear in this list must not also appear in the <b>All individual facts</b> list.</span> Either enter the tags manually here or use the edit selector.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Facts for new individuals'); ?></label>
						<div class="input_group">
							<input type="text" id="NEW_QUICK_REQUIRED_FACTS" name="NEW_QUICK_REQUIRED_FACTS" value="<?php echo get_gedcom_setting(KT_GED_ID, 'QUICK_REQUIRED_FACTS'); ?>" maxlength="255" dir="ltr">
							<span class="input_label right">
								<?php echo print_findfact_edit_link('NEW_QUICK_REQUIRED_FACTS'); ?>
							</span>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This is a comma separated list of GEDCOM fact tags that will be shown when adding a new person. For example, if BIRT is in the list, fields for birth date and birth place will be shown on the form. Either enter the tags manually here or use the edit selector.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Quick individual facts'); ?></label>
						<div class="input_group">
							<input type="text" id="NEW_INDI_FACTS_QUICK" name="NEW_INDI_FACTS_QUICK" value="<?php echo get_gedcom_setting(KT_GED_ID, 'INDI_FACTS_QUICK'); ?>" maxlength="255" dir="ltr">
							<span class="input_label right">
								<?php echo print_findfact_edit_link('NEW_INDI_FACTS_QUICK'); ?>
							</span>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This is the short list of GEDCOM individual facts that appears next to the full list that can be added with a single click. Either enter the tags manually here or use the edit selector.'); ?>
							</div>
						 </div>
					</div>
					<h4 class="accepted"><?php echo KT_I18N::translate('Facts for Family records'); ?></h4>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('All family facts'); ?></label>
						<div class="input_group">
							<input type="text" id="NEW_FAM_FACTS_ADD" name="NEW_FAM_FACTS_ADD" value="<?php echo get_gedcom_setting(KT_GED_ID, 'FAM_FACTS_ADD'); ?>" maxlength="255" dir="ltr">
							<span class="input_label right">
								<?php echo print_findfact_edit_link('NEW_FAM_FACTS_ADD'); ?>
							</span>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This is the list of GEDCOM facts that your users can add to families. You can modify this list by removing or adding fact names, even custom ones, as necessary. <span style="color: #ff0000;">Fact names that appear in this list must not also appear in the <b>Unique Family Facts</b> list</span>. Either enter the tags manually here or use the edit selector.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Unique family facts'); ?></label>
						<div class="input_group">
							<input type="text" id="NEW_FAM_FACTS_UNIQUE" name="NEW_FAM_FACTS_UNIQUE" value="<?php echo get_gedcom_setting(KT_GED_ID, 'FAM_FACTS_UNIQUE'); ?>" maxlength="255" dir="ltr">
							<span class="input_label right">
								<?php echo print_findfact_edit_link('NEW_FAM_FACTS_UNIQUE'); ?>
							</span>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This is the list of GEDCOM facts that your users can only add <u>once</u> to families. For example, if MARR is in this list, users will not be able to add more than one MARR record to a family. <span style="color: #ff0000;">Fact names that appear in this list must not also appear in the <i>Family Add Facts</i> list.</span> Either enter the tags manually here or use the edit selector.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Facts for new families'); ?></label>
						<div class="input_group">
							<input type="text" id="NEW_QUICK_REQUIRED_FAMFACTS" name="NEW_QUICK_REQUIRED_FAMFACTS" value="<?php echo $QUICK_REQUIRED_FAMFACTS; ?>" maxlength="255" dir="ltr">
							<span class="input_label right">
								<?php echo print_findfact_edit_link('NEW_QUICK_REQUIRED_FAMFACTS'); ?>
							</span>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This is a comma separated list of GEDCOM fact tags that will be shown when adding a new family. For example, if MARR is in the list, then fields for marriage date and marriage place will be shown on the form. Either enter the tags manually here or use the edit selector.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Quick family facts'); ?></label>
						<div class="input_group">
							<input type="text" id="NEW_FAM_FACTS_QUICK" name="NEW_FAM_FACTS_QUICK" value="<?php echo get_gedcom_setting(KT_GED_ID, 'FAM_FACTS_QUICK'); ?>" maxlength="255" dir="ltr">
							<span class="input_label right">
								<?php echo print_findfact_edit_link('NEW_FAM_FACTS_QUICK'); ?>
							</span>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This is the short list of GEDCOM family facts that appears next to the full list and can be added with a single click. Either enter the tags manually here or use the edit selector.'); ?>
							</div>
						 </div>
					</div>
					<h4 class="accepted"><?php echo KT_I18N::translate('Facts for Source records'); ?></h4>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('All source facts'); ?></label>
						<div class="input_group">
							<input type="text" id="NEW_SOUR_FACTS_ADD" name="NEW_SOUR_FACTS_ADD" value="<?php echo get_gedcom_setting(KT_GED_ID, 'SOUR_FACTS_ADD'); ?>" maxlength="255" dir="ltr">
							<span class="input_label right">
								<?php echo print_findfact_edit_link('NEW_SOUR_FACTS_ADD'); ?>
							</span>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This is the list of GEDCOM facts that your users can add to sources. You can modify this list by removing or adding fact names, even custom ones, as necessary. <span style="color: #ff0000;">Fact names that appear in this list must not also appear in the <i>Unique Source Facts</i> list.</span> Either enter the tags manually here or use the edit selector.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Unique source facts'); ?></label>
						<div class="input_group">
							<input type="text" id="NEW_SOUR_FACTS_UNIQUE" name="NEW_SOUR_FACTS_UNIQUE" value="<?php echo get_gedcom_setting(KT_GED_ID, 'SOUR_FACTS_UNIQUE'); ?>" maxlength="255" dir="ltr">
							<span class="input_label right">
								<?php echo print_findfact_edit_link('NEW_SOUR_FACTS_UNIQUE'); ?>
							</span>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This is the list of GEDCOM facts that your users can only add <u>once</u> to sources. For example, if TITL is in this list, users will not be able to add more than one TITL record to a source. <span style="color: #ff0000;">Fact names that appear in this list must not also appear in the <i>Source Add Facts</i> list.</span> Either enter the tags manually here or use the edit selector.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Quick source facts'); ?></label>
						<div class="input_group">
							<input type="text" id="NEW_SOUR_FACTS_QUICK" name="NEW_SOUR_FACTS_QUICK" value="<?php echo get_gedcom_setting(KT_GED_ID, 'SOUR_FACTS_QUICK'); ?>" maxlength="255" dir="ltr">
							<span class="input_label right">
								<?php echo print_findfact_edit_link('NEW_SOUR_FACTS_QUICK'); ?>
							</span>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This is the short list of GEDCOM source facts that appear next to the full list and can be added with a single click. Either enter the tags manually here or use the edit selector.'); ?>
							</div>
						 </div>
					</div>
					<h4 class="accepted"><?php echo KT_I18N::translate('Facts for Repository records'); ?></h4>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('All repository facts'); ?></label>
						<div class="input_group">
							<input type="text" id="NEW_REPO_FACTS_ADD" name="NEW_REPO_FACTS_ADD" value="<?php echo get_gedcom_setting(KT_GED_ID, 'REPO_FACTS_ADD'); ?>" maxlength="255" dir="ltr">
							<span class="input_label right">
								<?php echo print_findfact_edit_link('NEW_REPO_FACTS_ADD'); ?>
							</span>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This is the list of GEDCOM facts that your users can add to repositories. You can modify this list by removing or adding fact names, even custom ones, as necessary. <span style="color: #ff0000;">Fact names that appear in this list must not also appear in the <i>Unique Repository Facts</i> list.</span> Either enter the tags manually here or use the edit selector.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Unique repository facts'); ?></label>
						<div class="input_group">
							<input type="text" id="NEW_REPO_FACTS_UNIQUE" name="NEW_REPO_FACTS_UNIQUE" value="<?php echo get_gedcom_setting(KT_GED_ID, 'REPO_FACTS_UNIQUE'); ?>" maxlength="255" dir="ltr">
							<span class="input_label right">
								<?php echo print_findfact_edit_link('NEW_REPO_FACTS_UNIQUE'); ?>
							</span>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This is the list of GEDCOM facts that your users can only add <u>once</u> to repositories. For example, if NAME is in this list, users will not be able to add more than one NAME record to a repository. <span style="color: #ff0000;">Fact names that appear in this list must not also appear in the <i>Repository Add Facts</i> list.</span> Either enter the tags manually here or use the edit selector.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Quick repository facts'); ?></label>
						<div class="input_group">
							<input type="text" id="NEW_REPO_FACTS_QUICK" name="NEW_REPO_FACTS_QUICK" value="<?php echo get_gedcom_setting(KT_GED_ID, 'REPO_FACTS_QUICK'); ?>" maxlength="255" dir="ltr">
							<span class="input_label right">
								<?php echo print_findfact_edit_link('NEW_REPO_FACTS_QUICK'); ?>
							</span>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This is the short list of GEDCOM repository facts that appear next to the full list and can be added with a single click. Either enter the tags manually here or use the edit selector.'); ?>
							</div>
						 </div>
					</div>
					<h4 class="accepted"><?php echo KT_I18N::translate('Advanced fact settings'); ?></h4>
						<div class="config_options">
							<label><?php echo KT_I18N::translate('Advanced name facts'); ?></label>
							<div class="input_group">
							<input type="text" id="NEW_ADVANCED_NAME_FACTS" name="NEW_ADVANCED_NAME_FACTS" value="<?php echo $ADVANCED_NAME_FACTS; ?>" size="40" maxlength="255" dir="ltr">
							<span class="input_label right">
								<?php echo print_findfact_edit_link('NEW_ADVANCED_NAME_FACTS'); ?>
							</span>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This is a comma separated list of GEDCOM fact tags that will be shown on the add/edit name form. If you use non-Latin alphabets such as Hebrew, Greek, Cyrillic or Arabic, you may want to add tags such as _HEB, ROMN, FONE, etc. to allow you to store names in several different alphabets. Either enter the tags manually here or use the edit selector.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Advanced place name facts'); ?></label>
						<div class="input_group">
							<input type="text" id="NEW_ADVANCED_PLAC_FACTS" name="NEW_ADVANCED_PLAC_FACTS" value="<?php echo $ADVANCED_PLAC_FACTS; ?>" size="40" maxlength="255" dir="ltr">
							<span class="input_label right">
								<?php echo print_findfact_edit_link('NEW_ADVANCED_PLAC_FACTS'); ?>
							</span>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('This is a comma separated list of GEDCOM fact tags that will be shown when you add or edit place names. If you use non-Latin alphabets such as Hebrew, Greek, Cyrillic or Arabic, you may want to add tags such as _HEB, ROMN, FONE, etc. to allow you to store place names in several different alphabets. Either enter the tags manually here or use the edit selector.'); ?>
							</div>
						 </div>
					</div>
					<h4 class="accepted"><?php echo KT_I18N::translate('Other settings'); ?></h4>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Surname tradition'); ?></label>
						<div class="input_group">
							<?php echo radio_buttons('NEW_SURNAME_TRADITION', surnameDescriptions(), get_gedcom_setting(KT_GED_ID, 'SURNAME_TRADITION'), 'class="radio_inline"'); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('When you Add a family member, a default surname can be provided. This surname will depend on the local tradition.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Use full source citations'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_FULL_SOURCES', get_gedcom_setting(KT_GED_ID, 'FULL_SOURCES')); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('Source citations can include fields to record the quality of the data (primary, secondary, etc.) and the date the event was recorded in the source. If you don\'t use these fields, you can disable them when creating new source citations.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Source type'); ?></label>
						<div class="input_group">
							<?php echo select_edit_control('NEW_PREFER_LEVEL2_SOURCES', array(0=>KT_I18N::translate('none'), 1=>KT_I18N::translate('facts'), 2=>KT_I18N::translate('records')), null, get_gedcom_setting(KT_GED_ID, 'PREFER_LEVEL2_SOURCES')); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('When adding new close relatives, you can add source citations to the records (e.g. INDI, FAM) or the facts (BIRT, MARR, DEAT). This option controls which checkboxes are ticked by default.'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Use GeoNames database for autocomplete on places'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_USE_GEONAMES', get_gedcom_setting(KT_GED_ID, 'USE_GEONAMES')); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('Should the GeoNames database (http://www.geonames.org/) be used to provide more suggestions for place names?<p>When this option is set to <b>Yes</b>, the GeoNames database will be queried to supply suggestions for the place name being entered. When set to <b>No</b>, only existing places in the current family tree database will be searched. As you enter more of the place name, the suggestion will become more precise. This option can slow down data entry, particularly if your Internet connection is slow.</p><p>The GeoNames geographical database is accessible free of charge. It currently contains over 8,000,000 geographical names.</p>'); ?>
							</div>
						 </div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Do not update the “last change” record'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_NO_UPDATE_CHAN', get_gedcom_setting(KT_GED_ID, 'NO_UPDATE_CHAN')); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('Administrators sometimes need to clean up and correct the data submitted by users.<br>When Administrators make such corrections information about the original change is replaced.<br>When this option is selected kiwitrees will retain the original change information instead of replacing it.'); ?>
							</div>
						 </div>
					</div>
				</div>
				<!-- THEME OPTIONS -->
				<h3 class="accordion"><?php echo KT_I18N::translate('Theme'); ?></h3>
				<div id="theme">
					<h3><?php echo $tree->tree_title_html, ' - ', KT_I18N::translate('Theme'); ?></h3>
					<div class="input_group">
						<?php
							$current_themedir = get_gedcom_setting(KT_GED_ID, 'THEME_DIR');
							foreach (get_theme_names() as $themename => $themedir) {
								echo
								'<div ', ($current_themedir == $themedir ? 'class = "current_theme theme_box"' : 'class = "theme_box"'), '>
									<label for="radio_' . $themedir . '">
										<img src="themes/' . $themedir . '/images/screenshot_' . $themedir . '.png" alt="' . $themename . ' title="' . $themename . '">
										<p>
											<input type="radio" id="radio_' . $themedir . '" name="NEW_THEME_DIR" value="' . $themedir . '" ' . ($current_themedir == $themedir ? ' checked="checked"' : '') . '>' .
											$themename . '
										</p>
										<div class="custom_files">';
											$html = '<h4>' . KT_I18N::translate('Customized') . '</h4>';
											$files_found = false;
											foreach ($custom_files as $file) {
												$path = KT_ROOT . KT_THEMES_DIR . $themedir . '/' . $file;
												if (file_exists($path)) {
													$files_found = true;
													$html .= '<p>' . $file . '</p>';
												}
											}
											echo ($files_found ? $html : '');
										echo '</div>
									</label>
								</div>';
							}
							include KT_ROOT.'themes/colors/theme.php';
							echo color_palette();
						?>
					</div>
					<div class="clearfloat"></div>
				</div>
			</div>
		</div>
		<p>
			<button class="btn btn-primary" type="submit">
			<i class="fa fa-floppy-o"></i>
				<?php echo KT_I18N::translate('Save'); ?>
			</button>
		</p>
	</form>
</div>
