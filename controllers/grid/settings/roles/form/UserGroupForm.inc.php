<?php

/**
 * @file controllers/grid/settings/roles/form/UserGroupForm.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserGroupForm
 * @ingroup controllers_grid_settings_roles_form
 *
 * @brief Form to add/edit user group.
 */

import('lib.pkp.classes.form.Form');

class UserGroupForm extends Form {

	/** @var Id of the user group being edited */
	var $_userGroupId;

	/** @var The context of the user group being edited */
	var $_contextId;


	/**
	 * Constructor.
	 * @param $contextId Context id.
	 * @param $userGroupId User group id.
	 */
	function UserGroupForm($contextId, $userGroupId = null) {
		parent::Form('controllers/grid/settings/roles/form/userGroupForm.tpl');
		$this->_contextId = $contextId;
		$this->_userGroupId = $userGroupId;

		// Validation checks for this form
		$this->addCheck(new FormValidatorLocale($this, 'name', 'required', 'settings.roles.nameRequired'));
		$this->addCheck(new FormValidatorLocale($this, 'abbrev', 'required', 'settings.roles.abbrevRequired'));
		$this->addCheck(new FormValidatorArray($this, 'assignedStages', 'required', 'settings.roles.stageIdRequired'));
		if ($this->getUserGroupId() == null) {
			$this->addCheck(new FormValidator($this, 'roleId', 'required', 'settings.roles.roleIdRequired'));
		}
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the user group id.
	 * @return int userGroupId
	 */
	function getUserGroupId() {
		return $this->_userGroupId;
	}

	/**
	 * Get the context id.
	 * @return int contextId
	 */
	function getContextId() {
		return $this->_contextId;
	}

	//
	// Implement template methods from Form.
	//
	/**
	 * Get all locale field names
	 */
	function getLocaleFieldNames() {
		return array('name', 'abbrev');
	}

	/**
	 * @see Form::initData()
	 */
	function initData() {
		$userGroupDao = DAORegistry::getDAO('UserGroupDAO');
		$userGroup = $userGroupDao->getById($this->getUserGroupId());
		$stages = $userGroupDao->getWorkflowStageTranslationKeys();
		$this->setData('stages', $stages);
		$this->setData('assignedStages', array()); // sensible default

		if ($userGroup) {
			$assignedStages = $userGroupDao->getAssignedStagesByUserGroupId($this->getContextId(), $userGroup->getId());

			$data = array(
				'userGroupId' => $userGroup->getId(),
				'roleId' => $userGroup->getRoleId(),
				'name' => $userGroup->getName(null), //Localized
				'abbrev' => $userGroup->getAbbrev(null), //Localized
				'assignedStages' => array_keys($assignedStages),
			);
			foreach ($data as $field => $value) {
				$this->setData($field, $value);
			}
		}
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('roleId', 'name', 'abbrev', 'assignedStages'));
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch($request) {
		$templateMgr = TemplateManager::getManager($request);

		import('classes.security.RoleDAO');
		$roleOptions = RoleDAO::getRoleNames(true);
		$templateMgr->assign('roleOptions', $roleOptions);

		// Users can't edit the role once user group is created.
		// userGroupId is 0 for new User Groups because it is cast to int in UserGroupGridHandler.
		$disableRoleSelect = ($this->getUserGroupId() > 0) ? true : false;
		$templateMgr->assign('disableRoleSelect', $disableRoleSelect);

		return parent::fetch($request);
	}

	/**
	 * @see Form::execute()
	 */
	function execute($request) {
		$userGroupId = $this->getUserGroupId();
		$userGroupDao = DAORegistry::getDAO('UserGroupDAO');

		// Check if we are editing an existing user group or creating another one.
		if ($userGroupId == null) {
			$userGroup = $userGroupDao->newDataObject();
			$role = new Role($this->getData('roleId'));
			$userGroup->setRoleId($role->getId());
			$userGroup->setContextId($this->getContextId());
			$userGroup->setPath($role->getPath());
			$userGroup->setDefault(false);
			$userGroup = $this->_setUserGroupLocaleFields($userGroup, $request);
			$userGroupId = $userGroupDao->insertObject($userGroup);
		} else {
			$userGroup = $userGroupDao->getById($userGroupId);
			$userGroup = $this->_setUserGroupLocaleFields($userGroup, $request);
			$userGroupDao->updateLocaleFields($userGroup);
		}

		// After we have created/edited the user group, we assign/update its stages.
		if ($this->getData('assignedStages')) {
			$this->_assignStagesToUserGroup($userGroupId, $this->getData('assignedStages'));
		}
	}


	//
	// Private helper methods
	//
	/**
	 * Setup the stages assignments to a user group in database.
	 * @param $userGroupId int User group id that will receive the stages.
	 * @param $userAssignedStages array of stages currently assigned to a user.
	 */
	function _assignStagesToUserGroup($userGroupId, $userAssignedStages) {
		$contextId = $this->getContextId();
		$userGroupDao = DAORegistry::getDAO('UserGroupDAO');

		// Current existing workflow stages.
		$stages = $userGroupDao->getWorkflowStageTranslationKeys();

		foreach (array_keys($stages) as $stageId) {
			$userGroupDao->removeGroupFromStage($contextId, $userGroupId, $stageId);
		}

		foreach ($userAssignedStages as $stageId) {

			// Check if is a valid stage.
			if (in_array($stageId, array_keys($stages))) {
				$userGroupDao->assignGroupToStage($contextId, $userGroupId, $stageId);
			} else {
				fatalError('Invalid stage id');
			}
		}
	}

	/**
	 * Set locale fields on a User Group object.
	 * @param UserGroup
	 * @param Request
	 * @return UserGroup
	 */
	function _setUserGroupLocaleFields($userGroup, $request) {
		$router = $request->getRouter();
		$context = $router->getContext($request);
		$supportedLocales = $context->getSupportedLocaleNames();

		if (!empty($supportedLocales)) {
			foreach ($context->getSupportedLocaleNames() as $localeKey => $localeName) {
				$name = $this->getData('name');
				$abbrev = $this->getData('abbrev');
				$userGroup->setName($name[$localeKey], $localeKey);
				$userGroup->setAbbrev($abbrev[$localeKey], $localeKey);
			}
		} else {
			$localeKey = AppLocale::getLocale();
			$userGroup->setName($this->getData('name'), $localeKey);
			$userGroup->setAbbrev($this->getData('abbrev'), $localeKey);
		}

		return $userGroup;
	}
}

?>
