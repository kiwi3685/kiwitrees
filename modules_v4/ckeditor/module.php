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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class ckeditor_KT_Module extends KT_Module {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module. CKEditor is a trademark. Do not translate it? http://ckeditor.com */ KT_I18N::translate('CKEditor™');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the “CKEditor” module. WYSIWYG = “what you see is what you get” */ KT_I18N::translate('Allow other modules to edit text using a “WYSIWYG” editor, instead of using HTML codes.');
	}

	// Convert <textarea class="html-edit"> fields to CKEditor fields
	public static function enableEditor($controller) {
        $themedir = 'themes/' . get_gedcom_setting(KT_GED_ID, 'THEME_DIR'); // get current theme dir for css
        $mystyles = file_exists($themedir . '/mystyle.css') ? $themedir . '/mystyle.css' : '';
		$rand     = rand(); // generate random prefix for footnotes
		$controller
			->addExternalJavascript(KT_MODULES_DIR . 'ckeditor/ckeditor.js')
			->addExternalJavascript(KT_MODULES_DIR . 'ckeditor/adapters/jquery.js')
			// Need to specify the path before we load the libary
			->addInlineJavascript('var CKEDITOR_BASEPATH="' . KT_MODULES_DIR . 'ckeditor/";', KT_Controller_Base::JS_PRIORITY_HIGH)
			// Activate the editor
			->addInlineJavascript('
				jQuery(".html-edit").ckeditor(function(){}, {
                    contentsCss: ["' . $themedir . '/style.css", "' . $mystyles . '"],
					skin: "moono-lisa",
					allowedContent: true,
					width: "100%",
					height: "400px",
					filebrowserImageBrowseUrl:	"' . KT_MODULES_DIR . 'ckeditor/kcfinder/browse.php?opener=ckeditor&type=images",
					filebrowserImageUploadUrl:	"' . KT_MODULES_DIR . 'ckeditor/kcfinder/upload.php?opener=ckeditor&type=images",
					extraPlugins: "slideshow,footnotes",
					scayt_autoStartup: true,
					footnotesPrefix: "' . $rand . '",
					removePlugins: "emoji",
					toolbarGroups: [
						{ name: "document", groups: [ "mode", "document", "doctools" ] },
						{ name: "clipboard", groups: [ "clipboard", "undo" ] },
						{ name: "editing", groups: [ "find", "selection", "spellchecker", "editing" ] },
						{ name: "forms", groups: [ "forms" ] },
						"/",
						{ name: "basicstyles", groups: [ "basicstyles", "cleanup" ] },
						{ name: "paragraph", groups: [ "list", "indent", "blocks", "align", "bidi", "paragraph" ] },
						{ name: "links", groups: [ "links" ] },
						"/",
						{ name: "styles", groups: [ "styles" ] },
						{ name: "colors", groups: [ "colors" ] },
						{ name: "tools", groups: [ "tools" ] },
						{ name: "insert", groups: [ "insert" ] },
						{ name: "others", groups: [ "others" ] },
						{ name: "about", groups: [ "about" ] }
					]
				});
			');
	}
	// Convert <textarea class="html-edit"> fields to CKEditor fields with basic settings only for messsaging
	public static function enableBasicEditor($controller) {
		$controller
			->addExternalJavascript(KT_MODULES_DIR . 'ckeditor/ckeditor.js')
			->addExternalJavascript(KT_MODULES_DIR . 'ckeditor/adapters/jquery.js')
			// Need to specify the path before we load the libary
			->addInlineJavascript('var CKEDITOR_BASEPATH="' . KT_MODULES_DIR . 'ckeditor/";', KT_Controller_Base::JS_PRIORITY_HIGH)
			// Activate the editor
			->addInlineJavascript('
				jQuery(".html-edit").ckeditor(function(){}, {
					skin : "moono-lisa",
					width: "100%",
					height: "150px",
					enterMode: CKEDITOR.ENTER_BR,
					autoParagraph: false,
					toolbar: [["Bold", "Italic", "Underline", "-", "Subscript", "Superscript", "-", "NumberedList", "BulletedList", "Outdent", "Indent", "Font", "FontSize", "TextColor"]]
				});
			');
	}
}
