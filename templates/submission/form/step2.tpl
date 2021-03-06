{**
 * templates/submission/form/step2.tpl
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Step 2 of author submission.
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#submitStep2Form').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>
<form class="pkp_form" id="submitStep2Form" method="post" action="{url op="saveStep" path=$submitStep}" enctype="multipart/form-data">
	<input type="hidden" name="submissionId" value="{$submissionId|escape}" />
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="submitStep2FormNotification"}

	<!-- Submission upload grid -->

	{url|assign:submissionFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.submission.SubmissionWizardFilesGridHandler" op="fetchGrid" submissionId=$submissionId escape=false}
	{load_url_in_div id="submissionFilesGridDiv" url=$submissionFilesGridUrl}

	{if $currentContext->getSetting('supportPhone')}
		{assign var="howToKeyName" value="submission.submit.howToSubmit"}
	{else}
		{assign var="howToKeyName" value="submission.submit.howToSubmitNoPhone"}
	{/if}

	<p>{translate key=$howToKeyName supportName=$currentContext->getSetting('supportName') supportEmail=$currentContext->getSetting('supportEmail') supportPhone=$currentContext->getSetting('supportPhone')}</p>

	<div class="separator"></div>

	{fbvFormButtons id="step2Buttons" submitText="common.saveAndContinue"}
</form>
