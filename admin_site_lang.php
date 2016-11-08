<?php
// Restrict/allow site access based on IP address and user-agent string
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
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

define('WT_SCRIPT_NAME', 'admin_site_lang.php');
require './includes/session.php';
require WT_ROOT.'includes/functions/functions_edit.php';

$controller = new WT_Controller_Page();
$controller
	->restrictAccess(WT_USER_IS_ADMIN)
	->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
	->addExternalJavascript(WT_JQUERY_JEDITABLE_URL)
	->setPageTitle(WT_I18N::translate('Custom translation'))
	->pageHeader();

$action				= WT_Filter::post('action');
$language			= WT_Filter::post('language');
$custom_text_edits	= WT_Filter::postArray('custom_text_edit');
$new_standard_text	= WT_Filter::post('new_standard_text');
$new_custom_text	= WT_Filter::post('new_custom_text');
$delete				= safe_GET('delete');

if ($custom_text_edits) {
	foreach ($custom_text_edits as $key => $value) {
		WT_DB::exec("UPDATE `##custom_lang` SET `custom_text` = '{$value}' WHERE `custom_lang_id` = {$key}");
	}
}

if ($new_standard_text || $new_custom_text) {
	WT_DB::exec("INSERT INTO `##custom_lang` (`language`, `standard_text`, `custom_text`) VALUES ('{$language}','{$new_standard_text}','{$new_custom_text}')");
}

if ($delete == 'delete_item') {
	$custom_lang_id	= safe_GET('custom_lang_id');
	$action			= safe_GET('action');
	$language		= safe_GET('language');
	WT_DB::exec("DELETE FROM `##custom_lang` WHERE `custom_lang_id` = {$custom_lang_id}");
}

$code_list = WT_Site::preference('LANGUAGES');
if ($code_list) {
	$languages = explode(',', $code_list);
} else {
	$languages = array(
		'ar', 'bg', 'ca', 'cs', 'da', 'de', 'el', 'en_GB', 'en_US', 'es',
		'et', 'fi', 'fr', 'he', 'hr', 'hu', 'is', 'it', 'ka', 'lt', 'nb',
		'nl', 'nn', 'pl', 'pt', 'ru', 'sk', 'sv', 'tr', 'uk', 'vi', 'zh',
	);
}

function custom_texts($language) {
	$texts = WT_DB::prepare("SELECT * FROM `##custom_lang` WHERE language = ?")
		->execute(array($language))
		->fetchAll();
	return	$texts;
}
$custom_lang = custom_texts($language);

if (WT_USER_IS_ADMIN) { ?>
	<div id="custom-language">
		<h2><?php echo WT_I18N::translate('Manage custom translations'); ?></h2>
		<a class="current faq_link" href="http://kiwitrees.net/kiwi-blog/custom-translations/" target="_blank" title="'. WT_I18N::translate('View FAQ for this page.'). '"><?php echo WT_I18N::translate('View FAQ for this page.'); ?><i class="fa fa-comments-o"></i></a>
		<!-- SELECT LANGUAGE -->
		<form method="post" action="">
			<input type="hidden" name="action" value="translate">
			<?php echo WT_I18N::translate('Select language'); ?>
			<select id="nav-select" name="language" onchange="this.form.submit();">
				<option value=''></option>
				<?php
				foreach (WT_I18N::installed_languages() as $code=>$name) {
					$style = ($code == $language ? ' selected=selected ' : '');
					if (in_array($code, $languages)) {
						echo '<option' . $style . ' value="' . $code . '">' . WT_I18N::translate($name) . '</option>';
					}
				}
				?>
			</select>
		</form>
		<?php if ($action == 'translate') { ?>
			<!-- ADD NEW TRANSLATION -->
			<form method="post" action="">
				<input type="hidden" name="action" value="translate">
				<input type="hidden" name="language" value=<?php echo $language; ?>>
				<h3><?php echo WT_I18N::translate('Add a new translation'); ?></h3>
				<div class="row">
					<div class="text-header"><?php echo WT_I18N::translate('Standard text'); ?></div>
					<div class="symbol">=></div>
					<div class="text-header"><?php echo WT_I18N::translate('Custom translation'); ?></div>
					<div class="trash"><i class="fa fa-trash"></i></div>
				</div>
				<div class="row">
					<textarea  name="new_standard_text" placeholder="<?php echo WT_I18N::translate('Paste the standard text (US  English) here'); ?>"></textarea>
					<div class="symbol">=></div>
					<textarea  name="new_custom_text" placeholder="<?php echo WT_I18N::translate('Add your custom translation here'); ?>"></textarea>
					<div class="trash"><i class="fa fa-trash"></i></div>
				</div>
				<p>
					<button type="submit">
						<i class="fa fa-save"></i>
						<?php echo WT_I18N::translate('Save'); ?>
					</button>
				</p>
			</form>
			<hr class="clearfloat">
			<!-- EDIT TRANSLATIONS -->
			<form method="post" action="">
				<input type="hidden" name="action" value="translate">
				<input type="hidden" name="language" value=<?php echo $language; ?>>
				<h3><?php echo WT_I18N::translate('Edit existing translations'); ?></h3>
				<div class="row">
					<div class="text-header"><?php echo WT_I18N::translate('Standard text'); ?></div>
					<div class="symbol">=></div>
					<div class="text-header"><?php echo WT_I18N::translate('Custom translation'); ?></div>
					<div class="trash"><i class="fa fa-trash"></i></div>
				</div>
				<?php foreach ($custom_lang as $key => $value){ ?>
					<div class="row">
						<div class="update"><?php echo WT_I18N::translate('Last updated ') . htmlspecialchars($value->updated) ; ?></div>
						<textarea readonly><?php echo htmlspecialchars($value->standard_text); ?></textarea>
						<div class="symbol">=></div>
						<textarea name="custom_text_edit[<?php echo $value->custom_lang_id; ?>]"><?php echo htmlspecialchars($value->custom_text); ?></textarea>
						<div class="trash"><?php echo '<i class="fa fa-trash" onclick="if (confirm(\''.htmlspecialchars(WT_I18N::translate('Are you sure you want to delete this translation?')).'\')) { document.location=\''.WT_SCRIPT_NAME.'?delete=delete_item&amp;custom_lang_id='.$value->custom_lang_id.'&amp;action=translate&amp;language=' . $language . '\'; }"></i>'; ?></div>
					</div>
				<?php } ?>
				<p>
					<button type="submit">
						<i class="fa fa-save"></i>
						<?php echo WT_I18N::translate('Save'); ?>
					</button>
				</p>
			</form>
		<?php } ?>
	</div>
<?php } ?>
