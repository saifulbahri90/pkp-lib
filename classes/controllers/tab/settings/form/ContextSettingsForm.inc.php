<?php

/**
 * @file classes/controllers/tab/settings/form/ContextSettingsForm.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ContextSettingsForm
 * @ingroup classes_controllers_tab_settings_form
 *
 * @brief Base class for forms that manage context settings data.
 */


// Import the base Form.
import('lib.pkp.classes.form.Form');

class ContextSettingsForm extends Form {

	/** @var array */
	var $_settings;

	/** @var boolean */
	var $_wizardMode;


	/**
	 * Constructor.
	 * @param $template The form template file.
	 * @param $settings An associative array with the setting names as keys and associated types as values.
	 */
	function ContextSettingsForm($settings, $template, $wizardMode) {
		$this->addCheck(new FormValidatorPost($this));
		$this->setSettings($settings);
		$this->setWizardMode($wizardMode);
		parent::Form($template);
	}


	//
	// Getters and Setters
	//
	/**
	 * Get if the current form is in wizard mode (hide advanced settings).
	 * @return boolean
	 */
	function getWizardMode() {
		return $this->_wizardMode;
	}

	/**
	 * Set if the current form is in wizard mode (hide advanced settings).
	 * @param $wizardMode boolean
	 */
	function setWizardMode($wizardMode) {
		$this->_wizardMode = $wizardMode;
	}

	/**
	 * Get settings array.
	 * @return array
	 */
	function getSettings() {
		return $this->_settings;
	}

	/**
	 * Set settings array.
	 * @param $settings array
	 */
	function setSettings($settings) {
		$this->_settings = $settings;
	}


	//
	// Implement template methods from Form.
	//
	/**
	 * @see Form::initData()
	 * @param $request Request
	 */
	function initData($request) {
		$context = $request->getContext();
		$this->_data = $context->getSettings();
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData($request) {
		$this->readUserVars(array_keys($this->getSettings()));
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch($request, $params = null) {
		$templateMgr = TemplateManager::getManager($request);

		// Insert the wizardMode parameter in params array to pass to template.
		$params = array_merge((array)$params, array('wizardMode' => $this->getWizardMode()));

		// Pass the parameters to template.
		foreach($params as $tplVar => $value) {
			$templateMgr->assign($tplVar, $value);
		}

		return parent::fetch($request);
	}

	/**
	 * @see Form::execute()
	 */
	function execute($request) {
		$context = $request->getContext();
		$settingsDao = $context->getSettingsDao();
		$settings = $this->getSettings();

		foreach ($this->_data as $name => $value) {
			if (isset($settings[$name])) {
				$isLocalized = in_array($name, $this->getLocaleFieldNames());
				$settingsDao->updateSetting(
					$context->getId(),
					$name,
					$value,
					$settings[$name],
					$isLocalized
				);
			}
		}
	}
}

?>
