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
    public $fanDegree = 210;

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
        $this->fanDegree   = WT_Filter::getInteger('fanDegree', 180, 360, 210);
        $this->generations = WT_Filter::getInteger('generations', 2, 9, $defaultGenerations);
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
     * Get the theme defined chart font color.
     *
     * @return string HTML color code
     */
    public function getChartFontColor() {
		global $fanChart;
        return $fanChart['color'];
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
            'name'       => html_entity_decode(strip_tags($person->getShortName())),
            'sex'        => $person->getSex(),
            'born'       => $person->getBirthYear(),
            'died'       => $person->getDeathYear(),
            'color'      => $this->getColor($person),        );
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
     * A list of options for the chart style.
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

}
