<?php

/**
 * @file controllers/grid/users/stageParticipant/StageParticipantGridRow.inc.php
 *
 * Copyright (c) 2000-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StageParticipantGridRow
 * @ingroup controllers_grid_users_stageParticipant
 *
 * @brief StageParticipant grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class StageParticipantGridRow extends GridRow {
	/** @var $_submission Submission */
	var $_submission;

	/** @var $_stageId int */
	var $_stageId;

	/** @var $canAdminister boolean Whether the user can admin this row */
	var $_canAdminister;

	/**
	 * Constructor
	 */
	function StageParticipantGridRow(&$submission, $stageId, $canAdminister = false) {
		$this->_submission =& $submission;
		$this->_stageId =& $stageId;
		$this->_canAdminister = $canAdminister;

		parent::GridRow();
	}


	//
	// Overridden methods from GridRow
	//
	/**
	 * @see GridRow::initialize()
	 * @param $request PKPRequest
	 */
	function initialize($request) {
		// Do the default initialization
		parent::initialize($request);

		// Is this a new row or an existing row?
		$rowId = $this->getId();
		if (!empty($rowId) && is_numeric($rowId)) {
			// Only add row actions if this is an existing row.
			$router = $request->getRouter();

			import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');
			if ($this->_canAdminister) $this->addAction(
				new LinkAction(
					'delete',
					new RemoteActionConfirmationModal(
						__('editor.submission.removeStageParticipant.description'),
						__('editor.submission.removeStageParticipant'),
						$router->url($request, null, null, 'deleteParticipant', null, $this->getRequestArgs()),
						'modal_delete'
						),
					__('grid.action.remove'),
					'delete'
				)
			);

			import('lib.pkp.controllers.informationCenter.linkAction.NotifyLinkAction');
			$submission = $this->getSubmission();
			$stageId = $this->getStageId();
			$stageAssignment = $this->getData();
			$userId = $stageAssignment->getUserId();
			$this->addAction(new NotifyLinkAction($request, $submission, $stageId, $userId));
		}
	}

	//
	// Getters/Setters
	//
	/**
	 * Get the submission for this row (already authorized)
	 * @return Submission
	 */
	function &getSubmission() {
		return $this->_submission;
	}

	/**
	 * Get the stage id for this row
	 * @return int
	 */
	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * Get the grid request parameters.
	 * @see GridHandler::getRequestArgs()
	 * @return array
	 */
	function getRequestArgs() {
		return array(
			'submissionId' => $this->getSubmission()->getId(),
			'stageId' => $this->_stageId,
			'assignmentId' => $this->getId()
		);
	}
}

?>
