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

class resource_related_indi_WT_Module extends WT_Module implements WT_Module_Resources {

	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module. */ WT_I18N::translate('Related individuals');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of “Related individuals” module */ WT_I18N::translate('A report of individuals closely related to a selected individual.');
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
	public function getResourceMenus() {
		global $controller;

		$indi_xref = $controller->getSignificantIndividual()->getXref();

		$menus	= array();
		$menu	= new WT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;rootid=' . $indi_xref . '&amp;ged=' . WT_GEDURL,
			'menu-resources-' . $this->getName()
		);
		$menus[] = $menu;

		return $menus;
	}

	// Implement class WT_Module_Resources
	public function show() {
		global $controller, $GEDCOM, $MAX_DESCENDANCY_GENERATIONS;
		require WT_ROOT.'includes/functions/functions_resource.php';
		require WT_ROOT.'includes/functions/functions_edit.php';

		$controller = new WT_Controller_Individual();
		$controller
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addExternalJavascript(WT_STATIC_URL . 'js/autocomplete.js')
			->addInlineJavascript('autocomplete();');

		session_write_close();

		//-- args
		$go 				= WT_Filter::post('go');
		$rootid 			= WT_Filter::get('rootid');
		$root_id			= WT_Filter::post('root_id');
		$rootid				= empty($root_id) ? $rootid : $root_id;
		$choose_relatives	= WT_Filter::post('choose_relatives') ? WT_Filter::post('choose_relatives') : 'child-family';
		$ged				= WT_Filter::post('ged') ? WT_Filter::post('ged') : $GEDCOM;
		$select = array(
			'child-family'		=> WT_I18N::translate('Parents and siblings'),
			'spouse-family'		=> WT_I18N::translate('Spouses and children'),
			'direct-ancestors'	=> WT_I18N::translate('Direct line ancestors'),
			'ancestors'			=> WT_I18N::translate('Direct line ancestors and their families'),
			'descendants'		=> WT_I18N::translate('Descendants'),
			'all'				=> WT_I18N::translate('All')
		);

		?>
		<div id="resource-page" class="individual_report">
			<div class="noprint">
				<h2><?php echo $this->getTitle(); ?></h2>
				<h5><?php echo $this->getDescription(); ?></h5>
				<form name="resource" id="resource" method="post" action="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;rootid=<?php echo $rootid; ?>&amp;ged=<?php echo WT_GEDURL; ?>">
					<input type="hidden" name="go" value="1">
					<div class="chart_options">
						<label for = "rootid"><?php echo WT_I18N::translate('Individual'); ?></label>
						<input data-autocomplete-type="INDI" type="text" id="root_id" name="root_id" value="<?php echo $rootid; ?>">
					</div>
					<div class="chart_options">
						<label for = "choose_relatives"><?php echo WT_I18N::translate('Choose relatives'); ?></label>
						<?php echo select_edit_control('choose_relatives', $select,	null, $choose_relatives); ?>
					</div>
	 				<button class="btn btn-primary" type="submit" value="<?php echo WT_I18N::translate('show'); ?>">
						<i class="fa fa-eye"></i>
						<?php echo WT_I18N::translate('show'); ?>
					</button>
				</form>
			</div>
			<hr  class="noprint" style="clear:both;">
			<!-- end of form -->
			<?php if ($go == 1) {
				$controller
					->addExternalJavascript(WT_JQUERY_DATATABLES_URL)
					->addExternalJavascript(WT_JQUERY_DT_HTML5)
					->addExternalJavascript(WT_JQUERY_DT_BUTTONS)
					->addInlineJavascript('
						jQuery.fn.dataTableExt.oSort["unicode-asc" ]=function(a,b) {return a.replace(/<[^<]*>/, "").localeCompare(b.replace(/<[^<]*>/, ""))};
						jQuery.fn.dataTableExt.oSort["unicode-desc"]=function(a,b) {return b.replace(/<[^<]*>/, "").localeCompare(a.replace(/<[^<]*>/, ""))};
						jQuery("#related_individuals").dataTable({
						dom: \'<"H"pBf<"dt-clear">irl>t<"F"pl>\',
						' . WT_I18N::datatablesI18N() . ',
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
							/* 0 id */				{"bSortable": true, "sClass": "center"},
							/* 1-relationship */	null,
							/* 2-name */			null,
							/* 3-birth date */		{ dataSort: 4 },
							/* 4-BIRT:DATE */		{ visible: false },
							/* 5-birth place */		{ type: "unicode" },
							/* 6-marriage */		{"bSortable": false},
							/* 7-death date */		{ dataSort: 7 },
							/* 8-DEAT:DATE */		{ visible: false },
							/* 9-death place */		{ type: "unicode" },
							/* 10-father */			null,
							/* 11-mother */			null
						]
						});
						jQuery("#related_individuals").css("visibility", "visible");
						jQuery(".loading-image").css("display", "none");
					');

				$person = WT_Person::getInstance($rootid);
				if ($person && $person->canDisplayDetails()) { ; ?>
					<h2><?php echo /* I18N heading for report on related individuals */ WT_I18N::translate('%1s related to %2s ', $select[$choose_relatives], $person->getLifespanName()); ?></h2>

					<?php
					// collect list of relatives
					$related_individuals = array();
					$i = 0;
					switch ($choose_relatives) {
						case 'child-family':
							$families = $person->getChildFamilies();
							foreach ($families as $family) {
								$husband	= $family->getHusband();
								$wife		= $family->getWife();
								$children	= $family->getChildren();
								$marriage	= $family->getMarriage();
								if (!empty($husband)) {
									$i++;
									$related_individuals[$i]['relationship']	= WT_I18N::translate('Father');
									$related_individuals[$i]['name']			= $husband->getFullName();
									$related_individuals[$i]['birth']			= $husband->getBirthDate()->Display();
									$related_individuals[$i]['bdate']			= $husband->getBirthDate()->JD();
									$related_individuals[$i]['bplac']			= $husband->getBirthPlace();
									$related_individuals[$i]['marr']			= $family->getMarriageDate()->Display();
									$related_individuals[$i]['death']			= $husband->getDeathDate()->Display();
									$related_individuals[$i]['ddate']			= $husband->getDeathDate()->JD();
									$related_individuals[$i]['dplac']			= $husband->getDeathPlace();
									$related_individuals[$i]['father']			= $husband->getPrimaryChildFamily() ? $husband->getPrimaryChildFamily()->getHusband()->getLifespanName() : '';
									$related_individuals[$i]['mother']			= $husband->getPrimaryChildFamily() ? $husband->getPrimaryChildFamily()->getWife()->getLifespanName() : '';
								}
								if (!empty($wife)) {
									$i++;
									$related_individuals[$i]['relationship']	= WT_I18N::translate('Mother');
									$related_individuals[$i]['name']			= $wife->getFullName();
									$related_individuals[$i]['birth']			= $wife->getBirthDate()->Display();
									$related_individuals[$i]['bdate']			= $wife->getBirthDate()->JD();
									$related_individuals[$i]['bplac']			= $wife->getBirthPlace();
									$related_individuals[$i]['marr']			= $family->getMarriageDate()->Display();
									$related_individuals[$i]['death']			= $wife->getDeathDate()->Display();
									$related_individuals[$i]['ddate']			= $wife->getDeathDate()->JD();
									$related_individuals[$i]['dplac']			= $wife->getDeathPlace();
									$related_individuals[$i]['father']			= $wife->getPrimaryChildFamily() ? $wife->getPrimaryChildFamily()->getHusband()->getLifespanName() : '';
									$related_individuals[$i]['mother']			= $wife->getPrimaryChildFamily() ? $wife->getPrimaryChildFamily()->getWife()->getLifespanName() : '';
								}
								foreach ($children as $child) {
									if (!empty($child) && $child != $person) {
										$i++;
										$related_individuals[$i]['relationship']	= get_relationship_name(get_relationship($person, $child));
										$related_individuals[$i]['name']			= $child->getFullName();
										$related_individuals[$i]['birth']			= $child->getBirthDate()->Display();
										$related_individuals[$i]['bdate']			= $child->getBirthDate()->JD();
										$related_individuals[$i]['bplac']			= $child->getBirthPlace();
										$related_individuals[$i]['marr']			= '';
										$related_individuals[$i]['death']			= $child->getDeathDate()->Display();
										$related_individuals[$i]['ddate']			= $child->getDeathDate()->JD();
										$related_individuals[$i]['dplac']			= $child->getDeathPlace();
										$related_individuals[$i]['father']			= $husband->getLifespanName();
										$related_individuals[$i]['mother']			= $wife->getLifespanName();
									}
								}
							}
							break;
						case 'spouse-family':
							$return = add_resource_descendancy($i, $person, true);
							$related_individuals	= $return[0];
							$i						= $return[1];
						break;
						case 'descendants':
							$return = add_resource_descendancy($i, $person, false);
							$related_individuals	= $return[0];
							$i						= $return[1];
						break;
					}


					// output display
					?>
					<div class="loading-image">&nbsp;</div>
					<table id="related_individuals" class="width100" style="visibility:hidden;">
						<thead>
							<tr>
								<th><?php echo /*I18N short abbreviation for "Number" */ WT_I18N::translate('No.'); ?></th>
								<th><?php echo WT_I18N::translate('Relationship'); ?></th>
								<th><?php echo WT_I18N::translate('Name'); ?></th>
								<th><?php echo WT_I18N::translate('Birth'); ?></th>
								<th><?php //SORT_BIRT ?></th>
								<th><?php echo WT_I18N::translate('Place'); ?></th>
								<th><?php echo WT_I18N::translate('Marriage'); ?></th>
								<th><?php echo WT_I18N::translate('Death'); ?></th>
								<th><?php //SORT_DEAT ?></th>
								<th><?php echo WT_I18N::translate('Place'); ?></th>
								<th><?php echo WT_I18N::translate('Father'); ?></th>
								<th><?php echo WT_I18N::translate('Mother'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php for ($x = 1; $x <= $i; $x++) {
								echo '
									<tr>
										<td>' . $x . '</td>
										<td>' . $related_individuals[$x]['relationship'] . '</td>
										<td>' . $related_individuals[$x]['name'] . '</td>
										<td>' . $related_individuals[$x]['birth'] . '</td>
										<td>' . $related_individuals[$x]['bdate'] . '</td>
										<td>' . $related_individuals[$x]['bplac'] . '</td>
										<td>' . $related_individuals[$x]['marr'] . '</td>
										<td>' . $related_individuals[$x]['death'] . '</td>
										<td>' . $related_individuals[$x]['ddate'] . '</td>
										<td>' . $related_individuals[$x]['dplac'] . '</td>
										<td>' . $related_individuals[$x]['father'] . '</td>
										<td>' . $related_individuals[$x]['mother'] . '</td>
									</tr>
								';
							} ?>
						</tbody>
					</table>
				<?php
				}
			}; ?>
		</div>
	<?php }
}
