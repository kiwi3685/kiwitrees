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

 class KT_Controller_Fanchart extends KT_Controller_Chart {

    /**
     * Minimum number of displayable generations.
     *
     * @var int
     */
    const MIN_GENERATIONS = 2;

    /**
     * Maximum number of displayable generations.
     *
     * @var int
     */
    const MAX_GENERATIONS = 11;

    /**
     * Number of generations to display.
     *
     * @var int
     */
    public $generations = 6;

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
        $defaultGenerations = get_gedcom_setting(KT_GED_ID, 'DEFAULT_PEDIGREE_GENERATIONS');

        // Extract the request parameters
        $this->fanDegree   = KT_Filter::getInteger('fanDegree', 180, 360, 270);
        $this->generations = KT_Filter::getInteger('generations', self::MIN_GENERATIONS, self::MAX_GENERATIONS, $defaultGenerations);
        $this->fontScale   = KT_Filter::getInteger('fontScale', 0, 200, 100);

		// Create page title
        $title = KT_I18N::translate('Fanchart');
        if ($this->root && $this->root->canDisplayName()) {
            $title = KT_I18N::translate('Fanchart of %s', $this->root->getFullName());
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
    public function getColor(KT_Person $person = null) {
		global $fanChart;
        if ($person instanceof KT_Person) {
	        if ($person->getSex() === 'M') {
	            return $fanChart['bgMColor'];
	        } elseif ($person->getSex() === 'F') {
	            return $fanChart['bgFColor'];
	        }
		}
        return $fanChart['bgColor'];
    }

    /**
     * Get the individual data required for display the chart.
     *
     * @param Individual $person Start person
     * @param int        $generation Generation the person belongs to
     *
     * @return array
     */
    public function getIndividualData(KT_Person $person, $generation) {
        return array(
			'id'         => $person->getXref(),
            'generation' => $generation,
            'name'       => KT_Filter::unescapeHtml($person->getShortName()),
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
    public function buildJsonTree( KT_Person $person = null, $generation = 1 ) {
		// Maximum generation reached
		if (($generation > $this->generations) || !($person instanceof KT_Person)) {
            return array();
        }

		$data   = $this->getIndividualData($person, $generation);
        $family = $person->getPrimaryChildFamily();

		if (!($family instanceof KT_Family)) {
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
            180 => /* I18N: configuration option for fan chart */ KT_I18N::translate('180 degree'),
            210 => /* I18N: configuration option for fan chart */ KT_I18N::translate('210 degree'),
            240 => /* I18N: configuration option for fan chart */ KT_I18N::translate('240 degree'),
            270 => /* I18N: configuration option for fan chart */ KT_I18N::translate('270 degree'),
            300 => /* I18N: configuration option for fan chart */ KT_I18N::translate('300 degree'),
            330 => /* I18N: configuration option for fan chart */ KT_I18N::translate('330 degree'),
            360 => /* I18N: configuration option for fan chart */ KT_I18N::translate('360 degree'),
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
     * Get the raw update url. The "rootid" parameter must be the last one as
     * the url gets appended with the clicked individual id in order to load
     * the required chart data.
     *
     * @return string
     */
    public function getUpdateUrl() {
        $queryData = array(
            'mod'         => 'chart_fanchart',
            'mod_action'  => 'update',
            'ged'         => KT_GEDURL,
            'generations' => $this->generations,
            'rootid'      => '',
        );

        return 'module.php?' . http_build_query($queryData);
    }

	/**
     * Get the raw individual url. The "pid" parameter must be the last one as
     * the url gets appended with the clicked individual id in order to link
     * to the right individual page.
     *
     * @return string
     */
    public function getIndividualUrl() {
		$queryData = array(
			'ged' => KT_GEDURL,
			'pid' => '',
		);

		return 'individual.php?' . http_build_query($queryData);
    }

}
