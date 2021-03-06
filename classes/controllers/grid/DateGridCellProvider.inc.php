<?php

/**
 * @file classes/controllers/grid/DateGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DateGridCellProvider
 * @ingroup controllers_grid
 *
 * @brief Wraps date formatting support around a provided DataProvider.
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class DateGridCellProvider extends GridCellProvider {
	/** @var $_dataProvider The actual data provider to wrap */
	var $_dataProvider;

	/** @var $_format The format to use; see strftime */
	var $_format;

	/**
	 * Constructor
	 * @param $dataProvider DataProvider The object to wrap
	 * @param $format string See strftime
	 */
	function DateGridCellProvider($dataProvider, $format) {
		parent::GridCellProvider();
		$this->_dataProvider = $dataProvider;
		$this->_format = $format;
	}

	//
	// Template methods from GridCellProvider
	//
	/**
	 * Fetch a value from the provided DataProvider (in constructor)
	 * and format it as a date.
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn($row, $column) {
		$v = $this->_dataProvider->getTemplateVarsFromRowColumn($row, $column);
		$v['label'] = strftime($this->_format, strtotime($v['label']));
		return $v;
	}
}

?>
