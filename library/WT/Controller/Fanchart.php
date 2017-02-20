 <?php
 //	Controller for the fan chart
 //
 // Kiwitrees: Web based Family History software
 // Copyright (C) 2016 kiwitrees.net
 //
 // Derived from webtrees
 // Copyright (C) 2017  Rico Sonntag
 // Rico Sonntag <mail@ricosonntag.de>
 // https://github.com/magicsunday/ancestral-fan-chart/
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
 //
 if (!defined('WT_WEBTREES')) {
 	header('HTTP/1.0 403 Forbidden');
 	exit;
 }

 class WT_Controller_Fanchart extends WT_Controller_Chart {

    /**
     * Number of generations to display.
     *
     * @var int
     */
    public $generations = 5;

    /**
     * Style of fan chart. (2 = full circle, 3, three-quarter circle, 4 = half circle)
     *
     * @var int
     */
    public $fanDegree = 270;

    /**
     * Font size scaling factor in percent.
     *
     * @var int
     */
    public $fontScale = 100;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();

        // Get default number of generations to display
        $defaultGenerations = get_gedcom_setting(WT_GED_ID, 'DEFAULT_PEDIGREE_GENERATIONS');

        // Extract the request parameters
        $this->fanDegree   = WT_Filter::getInteger('fanDegree', 180, 360, 270);
        $this->generations = WT_Filter::getInteger('generations', 2, 10, $defaultGenerations);
        $this->fontScale   = WT_Filter::getInteger('fontScale', 0, 200, 100);

		// Create page title
        $title = WT_I18N::translate('Fan chart');
        if ($this->root && $this->root->canDisplayName()) {
            $title = WT_I18N::translate('Fan chart of %s', $this->root->getFullName());
		}

		$this->setPageTitle($title);
    }

    /**
     * Get the default colors based on the gender of an individual.
     *
     * @param Individual $person Individual instance
     *
     * @return string HTML color code
     */
    public function getColor(WT_Person $person = null) {
		global $fanChart;
        if ($person instanceof WT_Person) {
	        if ($person->getSex() === 'M') {
	            return $fanChart['bgMColor'];
	        } elseif ($person->getSex() === 'F') {
	            return $fanChart['bgFColor'];
	        }
		}
        return $fanChart['bgFColor'];
    }

    /**
     * Get the individual data required for display the chart.
     *
     * @param Individual $person Start person
     * @param int        $generation Generation the person belongs to
     *
     * @return array
     */
    public function getIndividualData(WT_Person $person, $generation) {
        return array(
			'id'         => $person->getXref(),
            'generation' => $generation,
            'name'       => WT_Filter::unescapeHtml($person->getShortName()),
            'sex'        => $person->getSex(),
            'born'       => $person->getBirthYear(),
            'died'       => $person->getDeathYear(),
            'color'      => $this->getColor($person),
        );
    }

    /**
     * Recursively build the data array of the individual ancestors.
     *
     * @param Individual $person     Start person
     * @param int        $generation Current generation
     *
     * @return array
     *
     * @todo Rebuild this to a iterative method
     */
    public function buildJsonTree( WT_Person $person = null, $generation = 1 ) {
		// Maximum generation reached
		if (($generation > $this->generations) || !($person instanceof WT_Person)) {
            return array();
        }

		$data   = $this->getIndividualData($person, $generation);
        $family = $person->getPrimaryChildFamily();

		if (!($family instanceof WT_Family)) {
            return $data;
        }

        // Recursively call the method for the parents of the individual
		$fatherTree = $this->buildJsonTree($family->getHusband(), $generation + 1);
        $motherTree = $this->buildJsonTree($family->getWife(), $generation + 1);

		// Add array of child nodes
        if ($fatherTree) {
            $data['children'][] = $fatherTree;
        }

        if ($motherTree) {
            $data['children'][] = $motherTree;
        }

        return $data;
    }

    /**
     * A list of options for the chart degrees.
     *
     * @return string[]
     */
    public function getFanDegrees() {
        return [
            180 => /* I18N: configuration option for fan chart */ WT_I18N::translate('180 degree'),
            210 => /* I18N: configuration option for fan chart */ WT_I18N::translate('210 degree'),
            240 => /* I18N: configuration option for fan chart */ WT_I18N::translate('240 degree'),
            270 => /* I18N: configuration option for fan chart */ WT_I18N::translate('270 degree'),
            300 => /* I18N: configuration option for fan chart */ WT_I18N::translate('300 degree'),
            330 => /* I18N: configuration option for fan chart */ WT_I18N::translate('330 degree'),
            360 => /* I18N: configuration option for fan chart */ WT_I18N::translate('360 degree'),
        ];
    }

	/**
     * Get the theme defined chart font color.
     *
     * @return string HTML color code
     */
    public function getChartFontColor() {
		global $fanChart;
        return $fanChart['color'];
    }

	/**
     * Returns the content HTML, including form and chart placeholder.
     *
     * @return string
     */
    public function getContentHtml() {
		require WT_ROOT . 'includes/functions/functions_edit.php';
        return '
			<div id="fanchart-page">
				<h2>' . $this->getPageTitle() . '</h2>
				<form name="people" id="people" method="get" action="?">
					<input type="hidden" name="ged" value="'. WT_GEDURL . '">
					<input type="hidden" name="mod" value="chart_fanchart">
					<div class="chart_options">
						<label for="rootid">' . WT_I18N::translate('Individual') . '</label>
						<input class="pedigree_form" data-autocomplete-type="INDI" type="text" name="rootid" id="rootid" value="' . $this->root->getXref() . '">
					</div>
					<div class="chart_options">
						<label for="generations">' . WT_I18N::translate('Generations') . '</label>
						' . edit_field_integers('generations', $this->generations, 2, 10) . '
					</div>
					<div class="chart_options">
						<label for="fanDegree">' . WT_I18N::translate('Degrees') . '</label>
						' . select_edit_control('fanDegree', $this->getFanDegrees(), null, $this->fanDegree) . '
					</div>
					<div class="chart_options">
						<label for="fontScale">' . WT_I18N::translate('Font size') . '</label>
						<input class="fontScale" type="text" name="fontScale" id="fontScale" value="' . $this->fontScale . '"> %
					</div>
					<button class="btn btn-primary show" type="submit">
						<i class="fa fa-eye"></i>
						' . WT_I18N::translate('Show') . '
					</button>
				</form>
				<hr style="clear:both;">
				<!-- end of form -->
				<div id="fan_chart"></div>
			</div>
		';
    }

    public function getUpdateUrl() {
        $queryData = array(
            'mod'         => 'chart_fanchart',
            'mod_action'  => 'update',
            'ged'         => WT_GEDURL,
            'generations' => $this->generations,
            'rootid'      => '',
        );

        return 'module.php?' . http_build_query($queryData);
    }

	/**
     * Render the fan chart form HTML and JSON data.
     *
     * @return string HTML snippet to include in page HTML
     */
    public function render() {
        // Encode chart parameters to json string
        $chartParams = json_encode(
            array(
				'fanDegree'    => $this->fanDegree,
				'generations'  => $this->generations,
				'defaultColor' => $this->getColor(),
				'fontScale'    => $this->fontScale,
				'fontColor'    => $this->getChartFontColor(),
				'data'         => $this->buildJsonTree($this->root),
				'updateUrl'    => $this->getUpdateUrl(),
            )
        );

        $this->addInlineJavascript('
			autocomplete();

			// Init widget
			if (typeof jQuery().ancestralFanChart === "function") {
			    jQuery("#fan_chart").ancestralFanChart(' . $chartParams . ');
			}
        ');

        return $this->getContentHtml();
    }

}
