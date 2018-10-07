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

class report_marriages_KT_Module extends KT_Module implements KT_Module_Report {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module. */ KT_I18N::translate('Marriages');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of “Marriages” module */ KT_I18N::translate('A report of individuals who were married in a given time or place.');
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'show':
			$this->show();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement KT_Module_Report
	public function getReportMenus() {
		global $controller;

		$indi_xref = $controller->getSignificantIndividual()->getXref();

		$menus	= array();
		$menu	= new KT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;ged=' . KT_GEDURL,
			'menu-report-' . $this->getName()
		);
		$menus[] = $menu;

		return $menus;
	}

	// Implement class KT_Module_Report
	public function show() {
		global $controller;
		require KT_ROOT.'includes/functions/functions_resource.php';
		require KT_ROOT.'includes/functions/functions_edit.php';

		$controller = new KT_Controller_Individual();
		$controller
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
			->addInlineJavascript('
				autocomplete();
				// check that at least one filter has been used
				function checkform() {
				    if (
						document.resource.name.value == "" &&
						document.resource.place.value == "" &&
						document.resource.m_from.value == "" &&
						document.resource.m_to.value == ""
					) {
						if (confirm("' . KT_I18N::translate('You have not set any filters. Kiwitrees will try to list records for every individual in your tree. Is this what you want to do?') . '")){
						    document.resource.submit(); // OK
						} else {
						    return false; // Cancel
						}
				    }
				}
			');

		init_calendar_popup();

		//Configuration settings ===== //
	    $action	= KT_Filter::post('action');
		$reset	= KT_Filter::post('reset');
		$name	= KT_Filter::post('name', '');
	    $m_from	= KT_Filter::post('m_from', '');
	    $m_to	= KT_Filter::post('m_to', '');
		$place	= KT_Filter::post('place', '');

		// dates for calculations
		$m_fromJD = (new KT_Date($m_from))->minJD();
		$m_toJD = (new KT_Date($m_to))->minJD();

		// reset all variables
	    if ($reset == 'reset') {
			$action	= '';
			$name	= '';
		    $m_from	= '';
		    $m_to	= '';
			$place	= '';
	    }

		?>
		<div id="page" class="marriages">
			<h2><?php echo $this->getTitle(); ?></h2>
			<div class="noprint">
				<div class="help_text">
					<div class="help_content">
						<h5><?php echo $this->getDescription(); ?></h5>
						<a href="#" class="more noprint"><i class="fa fa-question-circle-o icon-help"></i></a>
						<div class="hidden">
							<?php echo /* I18N: help for report facts and events module */ KT_I18N::translate('Date filters can be full (04 APR 1842) or 4-digit year only (1823). Name and place can be any string of characters you expect to find in those data fields. Autocomplete will find any given or surname that contains the characters you enter. To include all names or all places leave those fields empty.'); ?>
						</div>
					</div>
				</div>
				<form name="resource" id="resource" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;ged=<?php echo KT_GEDURL; ?>">
					<input type="hidden" name="action" value="go">
					<div class="chart_options">
						<label for="NAME"><?php echo KT_Gedcom_Tag::getLabel('NAME'); ?></label>
						<input data-autocomplete-type="NAME" type="text" name="name" id="NAME" value="<?php echo KT_Filter::escapeHtml($name); ?>" dir="auto" placeholder="<?php echo /*I18N:placeholder for a name selection field */ KT_I18N::translate('Enter all or part of any name'); ?>">
					</div>
					<div class="chart_options">
						<label for = "PLAC"><?php echo KT_Gedcom_Tag::getLabel('PLAC'); ?></label>
						<input data-autocomplete-type="PLAC" type="text" name="place" id="PLAC" value="<?php echo KT_Filter::escapeHtml($place); ?>" dir="auto" placeholder="<?php echo /*I18N:placeholder for a place selection field */ KT_I18N::translate('Enter all or part of any place'); ?>">
					</div>
					<div class="chart_options">
		              <label for = "DATE1"><?php echo KT_I18N::translate('Marriage date - from'); ?></label>
		              <input type="text" name="m_from" id="DATE1" value="<?php echo $m_from; ?>" onblur="valid_date(this);" onmouseout="valid_date(this);"><?php echo print_calendar_popup("DATE1"); ?>
		            </div>
		            <div class="chart_options">
		              <label for = "DATE2"><?php echo KT_I18N::translate('Marriage date - to'); ?></label>
		              <input type="text" name="m_to" id="DATE2" value="<?php echo $m_to; ?>" onblur="valid_date(this);" onmouseout="valid_date(this);"><?php echo print_calendar_popup("DATE2"); ?>
		            </div>
	 				<button class="btn btn-primary" type="submit" value="<?php echo KT_I18N::translate('show'); ?>" onclick="return checkform()">
						<i class="fa fa-eye"></i>
						<?php echo KT_I18N::translate('show'); ?>
					</button>
					<button class="btn btn-primary" type="submit" name="reset" value="reset">
		                <i class="fa fa-refresh"></i>
						<?php echo KT_I18N::translate('reset'); ?>
		            </button>
				</form>
			</div>
			<hr class="noprint" style="clear:both;">
			<!-- end of form -->
			<?php if ($action == 'go') {
				$controller
					->addExternalJavascript(KT_JQUERY_DATATABLES_URL)
					->addExternalJavascript(KT_JQUERY_DT_HTML5)
					->addExternalJavascript(KT_JQUERY_DT_BUTTONS)
					->addInlineJavascript('
						jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
						jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
						jQuery("#marriages").dataTable({
							dom: \'<"H"pBf<"dt-clear">irl>t<"F"pl>\',
							' . KT_I18N::datatablesI18N() . ',
							buttons: [{extend: "csv", exportOptions: {columns: ":visible"}}],
							autoWidth: false,
							paging: true,
							pagingType: "full_numbers",
							lengthChange: true,
							filter: true,
							info: true,
							jQueryUI: true,
							sorting: [0,"asc"],
							displayLength: 20,
							"aoColumns": [
								/* 0-name */			null,
								/* 1-marriage date */	{ dataSort: 2 },
								/* 2-MARR:DATE */		{ visible: false },
								/* 3-place */			null,
								/* 4-source */			{width : "40%"},
							]
						});
						jQuery("#marriages").css("visibility", "visible");
						jQuery(".loading-image").css("display", "none");
					');

				($name) ? $filter1 = '<p>' . /* I18N: A filter on the Marriages report page */ KT_I18N::translate('Names containing <span>%1s</span>', $name) . '</p>' : $filter1 = '';
				($place) ? $filter2 = '<p>' . /* I18N: A filter on the Marriages report page */ KT_I18N::translate('Place names containing <span>%1s</span>', $place) . '</p>' : $filter2 = '';
				($m_from && !$m_to) ? $filter3 = '<p>' . /* I18N: A filter on the Marriages report page */ KT_I18N::translate('Marriages from <span>%1s</span>', $m_from) . '</p>' : $filter3 = '';
				(!$m_from && $m_to) ? $filter4 = '<p>' . /* I18N: A filter on the Marriages report page */ KT_I18N::translate('Marriages to <span>%1s</span>', $m_to) . '</p>' : $filter4 = '';
				($m_from && $m_to) ? $filter5 = '<p>' . /* I18N: A filter on the Marriages report page */ KT_I18N::translate('Marriages between <span>%1s</span> and <span>%2s</span> ', $m_from, $m_to) . '</p>' : $filter5 = '';

				$filter_list = $filter1 . $filter2 . $filter3 . $filter4 . $filter5;

				$list = report_marriages($name, $place, $m_fromJD, $m_toJD, KT_GED_ID);

				// output display
				?>
				<div id="report_header">
					<h4><?php echo KT_I18N::translate('Listing individuals based on these filters'); ?></h4>
					<p><?php echo $filter_list; ?></p>
				</div>
				<div class="loading-image">&nbsp;</div>
				<table id="marriages" class="width100" <style="visibility:hidden;">
					<thead>
						<tr>
							<th><?php echo KT_I18N::translate('Name'); ?></th>
							<th><?php echo KT_I18N::translate('Date'); ?></th>
							<th><?php //SORT_MARR ?></th>
							<th><?php echo KT_I18N::translate('Place'); ?></th>
							<th><?php echo KT_I18N::translate('Source'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($list as $family) {
							if ($family->canDisplayDetails()) {
								?>
								<tr>
									<td>
										<a href="<?php echo $family->getHtmlUrl(); ?>"><?php echo str_replace(' + ', '<br>&nbsp;&nbsp;&nbsp;&nbsp;', $family->getFullName()); ?></a>
									</td>
									<td>
										<?php echo $family->getMarriageDate()->Display(); ?>
									</td>
									<td><?php echo $family->getMarriageDate()->JD(); ?></td><!-- used for sorting only -->
									<td>
										<?php echo $family->getMarriagePlace(); ?>
									</td>
									<td>
										<?php foreach (report_sources($family->getMarriage(), 2, true) as $key => $value) { ?>
											<p><?php echo $value; ?></p>
										<?php } ?>
									</td>
								</tr>
							<?php  }
						}
						?>
			 		</tbody>
				</table>
			<?php }
		}
}
