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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

// Update database for version 1.5
try {
	KT_DB::updateSchema(KT_ROOT.KT_MODULES_DIR.'fancy_treeview_descendants/db_schema/', 'FTV_SCHEMA_VERSION', 8);
} catch (PDOException $ex) {
	// The schema update scripts should never fail.  If they do, there is no clean recovery.
	die($ex);
}

class fancy_treeview_descendants_KT_Module extends KT_Module implements KT_Module_Config, KT_Module_Menu, KT_Module_Report {

	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of the module */ KT_I18N::translate('Descendants');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the module */ KT_I18N::translate('A narrative report of the descendants of one family or individual');
	}

	// Implement KT_Module_Report
	public function getReportMenus() {
		global $controller;

		$indi_xref = $controller->getSignificantIndividual()->getXref();

		$menus	= array();
		$menu	= new KT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;rootid=' . $indi_xref . '&amp;ged=' . KT_GEDURL,
			'menu-report-' . $this->getName()
		);
		$menus[] = $menu;

		return $menus;
	}

	// Extend KT_Module_Config
	public function modAction($mod_action) {
		$ftv = new KT_Controller_FancyTreeView();

		switch($mod_action) {
		case 'admin_config':
			require KT_ROOT . KT_MODULES_DIR . $this->getName() . '/admin_fancy_treeview_descendants.php';
			break;
		case 'admin_reset':
			$ftv->ftv_reset($this->getName());
			require KT_ROOT . KT_MODULES_DIR . $this->getName() . '/admin_fancy_treeview_descendants.php';
			break;
		case 'admin_delete':
			$ftv->delete($this->getName());
			require KT_ROOT . KT_MODULES_DIR . $this->getName() . '/admin_fancy_treeview_descendants.php';
			break;
		case 'show':
			$this->show();
			break;
		// See mediafirewall.php
		case 'thumbnail':
			$tree = KT_TREE::getIdFromName(KT_Filter::get('ged'));
			if(empty($tree)) $tree = KT_GED_ID;

			$mid			 = KT_Filter::get('mid', KT_REGEX_XREF);
			$media			 = KT_Media::getInstance($mid, $tree);
			$mimetype		 = $media->mimeType();
			$cache_filename	 = $ftv->cacheFileName($media, $this->getName());
			$filetime		 = filemtime($cache_filename);
			$filetimeHeader	 = gmdate('D, d M Y H:i:s', $filetime) . ' GMT';
			$expireOffset	 = 3600 * 24 * 7; // tell browser to cache this image for 7 days
			$expireHeader	 = gmdate('D, d M Y H:i:s', KT_TIMESTAMP + $expireOffset) . ' GMT';
			$etag			 = $media->getEtag();
			$filesize		 = filesize($cache_filename);

			// parse IF_MODIFIED_SINCE header from client
			$if_modified_since = 'x';
			if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
				$if_modified_since = preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
			}

			// parse IF_NONE_MATCH header from client
			$if_none_match = 'x';
			if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
				$if_none_match = str_replace('"', '', $_SERVER['HTTP_IF_NONE_MATCH']);
			}

			// add caching headers.  allow browser to cache file, but not proxy
			header('Last-Modified: ' . $filetimeHeader);
			header('ETag: "' . $etag . '"');
			header('Expires: ' . $expireHeader);
			header('Cache-Control: max-age=' . $expireOffset . ', s-maxage=0, proxy-revalidate');

			// if this file is already in the user’s cache, don’t resend it
			// first check if the if_modified_since param matches
			if ($if_modified_since === $filetimeHeader) {
				// then check if the etag matches
				if ($if_none_match === $etag) {
					http_response_code(304);
					return;
				}
			}

			// send headers for the image
			header('Content-Type: ' . $mimetype);
			header('Content-Disposition: filename="' . basename($cache_filename) . '"');
			header('Content-Length: ' . $filesize);

			// Some servers disable fpassthru() and readfile()
			if (function_exists('readfile')) {
				readfile($cache_filename);
			} else {
				$fp = fopen($cache_filename, 'rb');
				if (function_exists('fpassthru')) {
					fpassthru($fp);
				} else {
					while (!feof($fp)) {
						echo fread($fp, 65536);
					}
				}
				fclose($fp);
			}
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Implement KT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod=' . $this->getName() . '&amp;mod_action=admin_config';
	}

	// ************************************************* START OF FRONT PAGE ********************************* //
	private function show() {
		$ftv = new KT_Controller_FancyTreeView();

		global $controller;
		$root			= KT_Filter::get('rootid', KT_REGEX_XREF); // the first pid
		$type			= KT_Filter::get('type'); // menu type "overview" or blank
		$root_person	= $ftv->getPerson($root);
		$controller		= new KT_Controller_Page;

		if($root_person && $root_person->canDisplayName()) {
			$controller
				->setPageTitle(/* I18N: %s is the surname of the root individual */ KT_I18N::translate('Descendants of %s', $root_person->getFullName()))
				->pageHeader()
				->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
				->addExternalJavascript(KT_FANCY_TREEVIEW_JS_URL)
				->addInlineJavascript('
					var RootID				= "' . $root . '";
					var ModuleName			= "' . $this->getName() . '";
					var OptionsNumBlocks	= ' . $ftv->options($this->getName(), 'numblocks') . ';
					var TextFollow			= "' . KT_I18N::translate('follow') . '";
					', KT_Controller_Base::JS_PRIORITY_HIGH
				)
				->addInlineJavascript('
					autocomplete();

					// submit form to change root id
				    jQuery( "form#change_root" ).submit(function(e) {
				        e.preventDefault();
				        var new_rootid = jQuery("form #new_rootid").val();
						var url = jQuery(location).attr("pathname") + "?mod=' . $this->getName() . '&mod_action=show&rootid=" + new_rootid;
				        jQuery.ajax({
				            url: url,
				            csrf: KT_CSRF_TOKEN,
				            success: function() {
				                window.location = url;
				            },
				            statusCode: {
				                404: function() {
				                    var msg = "' . KT_I18N::translate('This individual does not exist or you do not have permission to view it.') . '";
				                    jQuery("#error").text(msg).addClass("ui-state-error").show();
				                    setTimeout(function() {
				                        jQuery("#error").fadeOut("slow");
				                    }, 3000);
				                    jQuery("form #new_rootid")
				                        .val("")
				                        .focus();
				                }
				            }
				        });
				    });
				');

			// Start page content
			?>
			<div id="page">
				<?php if (KT_USER_ID || !$type) { ?>
					<h2><?php echo $this->getTitle(); ?></h2>
					<div class="chart_options noprint">
						<form id="change_root">
							<div class="chart_options">
								<label for = "new_rootid" class="label"><?php echo KT_I18N::translate('Individual'); ?></label>
								<input type="text" data-autocomplete-type="INDI" name="new_rootid" id="new_rootid" value="<?php echo $root; ?>">
							</div>
							<button class="btn btn-primary show" type="submit">
								<i class="fa fa-eye"></i>
								<?php echo KT_I18N::translate('show'); ?>
							</button>
						</form>
					</div>
					<hr class="noprint">
				<?php } ?>
				<div id="fancy_treeview_descendants-page">
					<div id="error"></div>
					<div id="page-header">
						<h2>
							<?php echo $controller->getPageTitle() ?>
							<?php if (KT_USER_IS_ADMIN) { ?>
								<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=admin_config" target="_blank" rel="noopener noreferrer" class="noprint">
									<i class="fa fa-cog"></i>
								</a>
							<?php } ?>
						</h2>
						<h5><?php echo $root_person->getLifeSpan(); ?></h5>
					</div>
					<div id="page-body">
						<ol id="fancy_treeview_descendants"><?php echo $ftv->printPage($this->getName(), $ftv->options($this->getName(), 'numblocks')); ?></ol>
						<div id="btn_next">
							<button class="btn btn-next" type="button" name="next" value="<?php echo KT_I18N::translate('next'); ?>" title="<?php echo KT_I18N::translate('Show more generations'); ?>">
								<i class="fa fa-arrow-down"></i>
								<?php echo KT_I18N::translate('next'); ?>
							</button>
						</div>
					</div>
				</div>
			</div>
		<?php } else {
			header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
				$controller->pageHeader(); ?>
				<p class="ui-state-error"><?php echo KT_I18N::translate('This individual does not exist or you do not have permission to view it.'); ?></p>
			<?php exit;
		}
	}

	// ************************************************* START OF MENU ********************************* //

	// Implement KT_Module_Menu
	public function defaultMenuOrder() {
		return 120;
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return KT_PRIV_USER;
	}

	// Implement KT_Module_Menu
	public function MenuType() {
		return 'main';
	}

	// Implement KT_Module_Menu
	public function getMenu() {
		$controller = new KT_Controller_FancyTreeView();

		$menu = null;
		if (empty($controller)) {
			return null;
		}

		if (method_exists($controller, 'getFTVMenu')) {
			$menu = $controller->getFTVMenu();
		}

		return $menu;
	}

}
