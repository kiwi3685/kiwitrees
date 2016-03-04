<?php
// Classes and libraries for module system
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2010 John Finlay
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

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class resource_occupation_WT_Module extends WT_Module implements WT_Module_Resources {

	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module. Tasks that need further research. */ WT_I18N::translate('Occupation report');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of “Research tasks” module */ WT_I18N::translate('A report of individuals who had a given occupation.');
	}

	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'show':
			$this->show();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Extend class WT_Module
	public function defaultAccessLevel() {
		return WT_PRIV_PUBLIC;
	}

	// Implement WT_Module_Resources
	public function defaultMenuOrder() {
		return 26;
	}

	// Implement WT_Module_Menu
	public function MenuType() {
		return 'main';
	}

	// Implement WT_Module_Menu
	public function getMenu() {
		return false;
	}

	// Implement WT_Module_Resources
	public function getResourceMenus() {
		global $controller;

		$menus	= array();
		$menu	= new WT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;ged=' . WT_GEDURL,
			'menu-resources-' . $this->getName()
		);
		$menus[] = $menu;

		return $menus;
	}

	// Implement class WT_Module_Resources
	public function show() {
		global $controller, $GEDCOM;
		require WT_ROOT.'includes/functions/functions_resource.php';
		require WT_ROOT.'includes/functions/functions_edit.php';

		$table_id = 'ID'.(int)(microtime()*1000000); // create a unique ID

		$controller = new WT_Controller_Individual();
		$controller
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addExternalJavascript(WT_STATIC_URL . 'js/autocomplete.js')
			->addInlineJavascript('autocomplete();')
				->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
				->addInlineJavascript('
					jQuery("#' .$table_id. '").dataTable( {
						dom: \'<"H"pf<"dt-clear">irl>t<"F"pl>\',
						' . WT_I18N::datatablesI18N() . ',
						autoWidth: false,
						paging: true,
						pagingType: "full_numbers",
						lengthChange: true,
						filter: true,
						info: true,
						jQueryUI: true,
						sorting: [[0,"asc"]],
						displayLength: 20,
						"aoColumns": [
							/* 0-BIRT_DATE */  	{ "bVisible": false },
							/* 1-Name */		{},
							/* 2-DoB */			{ "iDataSort": 0 },
							/* 3-Occupation */ 	{},
							/* 4-OCCU_DATE */  	{ "bVisible": false },
							/* 5-Date */ 		{ "iDataSort": 4 },
							/* 6-Place */ 		{},
							/* 7-Note */		{}
						]
					});
				jQuery("#' .$table_id. '").css("visibility", "visible");
				jQuery(".loading-image").css("display", "none");
			');


		session_write_close();

		//-- args
		$go 			= WT_Filter::post('go');
		$occupation 	= WT_Filter::post('occupation');
		$ged			= WT_Filter::post('ged') ? WT_Filter::post('ged') : $GEDCOM;

		?>
		<div id="resource-page" class="occupation_report">
			<h2><?php echo $this->getTitle(); ?></h2>
			<h5><?php echo $this->getDescription(); ?></h5>
			<div class="noprint">
				<form name="resource" id="resource" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;ged=<?php echo WT_GEDURL; ?>">
					<input type="hidden" name="go" value="1">
					<div class="chart_options">
						<label for = "occupation"><?php echo WT_I18N::translate('Occupation'); ?></label>
						<input data-autocomplete-type="OCCU" type="text" id="occupation" name="occupation" value="<?php echo $occupation; ?>">
					</div>
	 				<button class="btn btn-primary" type="submit" value="<?php echo WT_I18N::translate('show'); ?>">
						<i class="fa fa-eye"></i>
						<?php echo WT_I18N::translate('show'); ?>
					</button>
				</form>
			</div>
			<hr style="clear:both;">
			<!-- end of form -->
			<?php
			if ($go == 1) { ?>
				<div class="loading-image">&nbsp;</div>
				<table id="<?php echo $table_id; ?>"style="visibility:hidden; width:100%;">
					<thead>
						<tr>
							<th>BIRT_DATE</th>
							<th><?php echo WT_I18N::translate('Name'); ?></th>
							<th><?php echo WT_Gedcom_Tag::getLabel('BIRT:DATE'); ?></th>
							<th><?php echo WT_Gedcom_Tag::getLabel('OCCU'); ?></th>
							<th>OCCU_DATE</th>
							<th><?php echo WT_Gedcom_Tag::getLabel('DATE'); ?></th>
							<th><?php echo WT_Gedcom_Tag::getLabel('PLAC'); ?></th>
							<th><?php echo WT_Gedcom_Tag::getLabel('NOTE'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$rows = resource_occu($occupation);
						foreach ($rows as $row) { ?>
							<tr>
								<?php
								$person = WT_Person::getInstance($row->xref);
								if ($person->canDisplayDetails()) { ?>
									<td><?php echo $person->getBirthDate()->JD(); ?></td>
									<td>
										<a href="<?php echo $person->getHtmlUrl(); ?>" target="_blank"><?php echo $person->getFullName(); ?></a>
									</td>
									<td>
										<?php echo $person->getBirthDate()->Display(); ?>
									</td>
									<td>
										<?php echo htmlspecialchars($person->getFactByType('OCCU')->getDetail()); ?>
									</td>
									<td><?php echo $person->getFactByType('OCCU')->getDate()->JD(); ?></td>
									<td>
										<?php echo $person->getFactByType('OCCU')->getDate()->Display(); ?>
									</td>
									<td>
										<?php echo $person->getFactByType('OCCU')->getPlace(); ?>
									</td>
									<td>
										<?php
										print_fact_notes($person->getFactByType('OCCU')->getGedcomRecord(), 2, false);
										?>
									</td>
								<?php } ?>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			<?php } ?>
		</div>
	<?php }

}
