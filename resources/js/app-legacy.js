/* app-legacy.js */

import * as timezone from "./modules/timezone";
import * as home from "./modules/home";
import * as signupInvite from "./modules/signup_invite";
import * as dialog from "./modules/window";
import * as events from "./modules/events";
import * as accomReports from "./modules/accom_reports";
import * as gpoaActivities from "./modules/gpoa_activities";
import * as students from "./modules/students";

function runActions(actionDeps) {
	var actions, depends, action, depend, satisfied;

	for (var i = 0; i < actionDeps.length; i++) {
		satisfied = true;
		action = actionDeps[i];
		for (var j = 0; j < action.depends.length; j++) {
			if (window[action.depends[j]] === undefined) {
				satisfied = false;
				break;
			}
		}
		if (!satisfied) continue;
		for (var k = 0; k < action.actions.length; k++) {
			action.actions[k]();
		}
	} 
}

function addEventToMatchingIds(element) {
	var elements, currentEl, idParts, id, pattern, containerEl, containerId;

	pattern = element.element;
	containerId = pattern.substring(0, pattern.indexOf("-*")) + "-items";
	containerEl = document.getElementById(containerId);
	if (!containerEl) return;
	elements = containerEl.getElementsByTagName("*"); 
	idParts = pattern.split("*"); 
	for (var i = 0; i < elements.length; i++) {
		currentEl = elements[i];
		id = currentEl.id;
		if (!(id && id.indexOf(idParts[0]) === 0 && 
			id.lastIndexOf(idParts[1]) === 
			(id.length - idParts[1].length))) continue;
		currentEl.addEventListener(element.event, element.action, false);
	}
}

function addEvents(elementActions) {
	var element, currentEl;

	for (var i = 0; i < elementActions.length; i++) {
		element = elementActions[i];
		if (element.element.indexOf("*") !== -1) {
			addEventToMatchingIds(element);
			continue;
		}
		currentEl = document.getElementById(element.element);
		if (!currentEl) {
			continue;
		}
		currentEl.addEventListener(element.event, element.action, false);
	}
}

/*

function setActions() {
	var actionDependencies;

	actionDependencies = [
		{
			actions: [ home.streamHome, home.streamHomeInfos ],
			depends: [ "EventSource", "JSON" ]
		}
	];
	runActions(actionDependencies);
}

*/

function setTimezoneActions() {
	if (window["Intl"] !== undefined 
		&& Intl.DateTimeFormat().resolvedOptions().timeZone != undefined) {
		timezone.setTimezoneFromIntl();
	} else {
		timezone.setTimezoneFromDate();
	}
}

function setEvents() {
	var elementActions, element, currentEl;

	elementActions = [
		{
			element: "event-description_edit-button",
			event: "click",
			action: events.editEventDescription
		},
		{
			element: "event-narrative_edit-button",
			event: "click",
			action: events.editEventNarrative
		},
		{
			element: "event-venue_edit-button",
			event: "click",
			action: events.editEventVenue
		},
		{
			element: "event-date_create-button",
			event: "click",
			action: events.createEventDate
		},
		{
			element: "event-date-*_delete-button",
			event: "click",
			action: dialog.openDeleteItemWindow 
		},
		{
			element: "event-link-*_delete-button",
			event: "click",
			action: dialog.openDeleteItemWindow 
		},
		{
			element: "event-date-watten-*_delete-button",
			event: "click",
			action: dialog.openDeleteItemWindow 
		},
		{
			element: "event-attachment-set_create-button",
			event: "click",
			action: events.createEventAttachmentSet
		},
		{
			element: "event-link_create-button",
			event: "click",
			action: events.createEventLink
		},
		{
			element: "event-banner_edit-button",
			event: "click",
			action: events.editEventBanner
		},
		{
			element: "event-attachment-set-*_edit-button",
			event: "click",
			action: events.editEventAttachmentSet
		},
		{
			element: "main-back-link",
			event: "click",
			action: setBackLink
		},
		{
			element: "event-attachment_delete-button",
			event: "click",
			action: events.deleteEventAttachment
		},
		{
			element: "accom-report_return-button",
			event: "click",
			action: accomReports.returnAccomReport
		},
		{
			element: "accom-report_submit-button",
			event: "click",
			action: accomReports.submitAccomReport
		},
		{
			element: "accom-report_approve-button",
			event: "click",
			action: accomReports.approveAccomReport
		},
		{
			element: "gpoa-activity_approve-button",
			event: "click",
			action: gpoaActivities.approveActivity
		},
		{
			element: "gpoa-activity_return-button",
			event: "click",
			action: gpoaActivities.returnActivity
		},
		{
			element: "gpoa-activity_reject-button",
			event: "click",
			action: gpoaActivities.rejectActivity
		},
		{
			element: "gpoa-activity_submit-button",
			event: "click",
			action: gpoaActivities.submitActivity
		},
		{
			element: "gpoa-activity_delete-button",
			event: "click",
			action: gpoaActivities.deleteActivity
		},
		{
			element: "accom-report-background_edit-button",
			event: "click",
			action: accomReports.editBackground
		},
		{
			element: "gpoa_close-button",
			event: "click",
			action: gpoaActivities.closeGpoa
		},
		{
			element: "student-section_create-button",
			event: "click",
			action: students.createSection,
		},
		{
			element: "student-section-*_delete-button",
			event: "click",
			action: dialog.openDeleteItemWindow 
		},
		{
			element: "student-year-level_create-button",
			event: "click",
			action: dialog.prepareOpenWindow 
		},
		{
			element: "student-year-level-*_delete-button",
			event: "click",
			action: dialog.openDeleteItemWindow 
		},
		{
			element: "student-course_create-button",
			event: "click",
			action: dialog.prepareOpenWindow 
		},
		{
			element: "student-course-*_delete-button",
			event: "click",
			action: dialog.openDeleteItemWindow 
		},
		{
			element: "gpoa-mode-*_delete-button",
			event: "click",
			action: dialog.openDeleteItemWindow 
		},
		{
			element: "gpoa-fund-*_delete-button",
			event: "click",
			action: dialog.openDeleteItemWindow 
		},
		{
			element: "gpoa-partnership-*_delete-button",
			event: "click",
			action: dialog.openDeleteItemWindow 
		},
		{
			element: "gpoa-type-*_delete-button",
			event: "click",
			action: dialog.openDeleteItemWindow 
		},
		{
			element: "print-button",
			event: "click",
			action: printPage
		},
		{
			element: "file-input",
			event: "change",
			action: browseFiles
		},
		{
			element: "signup-invite_create-button",
			event: "click",
			action: dialog.prepareOpenWindow 
		},
		{
			element: "signup-invite-*_delete-button",
			event: "click",
			action: dialog.openDeleteItemWindow 
		},
		{
			element: "profile-email_edit-button",
			event: "click",
			action: dialog.prepareOpenWindow 
		},
		{
			element: "profile-password_edit-button",
			event: "click",
			action: dialog.prepareOpenWindow 
		},
		{
			element: "event-heads_edit-button",
			event: "click",
			action: dialog.prepareOpenWindow 
		},
		{
			element: "event-coheads_edit-button",
			event: "click",
			action: dialog.prepareOpenWindow 
		},
	];
	addEvents(elementActions);
}

function printPage() {
	window.print();
}

function setBackLink(e) {
	var href, ref;

	href = e.currentTarget.href;
	ref = document.referrer;
	if (ref && ref.indexOf(href) === 0 && history.length > 1) {
		e.preventDefault();
		history.back();
	}
}

function browseFiles(e) {
	var el, fileSizeLimit, multiple, message, files, invalid;

	el = e.currentTarget;
	el.setCustomValidity("");
	fileSizeLimit = 2097152;
	multiple = el.hasAttribute("multiple");
	message = "File too large! Max size is 2 MB.";
	files = el.files;
	invalid = false;
	if (multiple) {
		for (var i = 0; i < files.length; i++) {
			if (files[i].size > fileSizeLimit) {
				invalid = true;
				break;
			}
		}
		if (invalid)
			el.setCustomValidity(message);
		else
			el.setCustomValidity("");
	} else {
		if (files[0].size > fileSizeLimit)
			el.setCustomValidity(message);
		else 
			el.setCustomValidity("");
	}
}

home.streamHome();
home.streamHomeInfos();
signupInvite.streamSignupInviteStatus();
setTimezoneActions();
setEvents();
dialog.openWindow(true);

