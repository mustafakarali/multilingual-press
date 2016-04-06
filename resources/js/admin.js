'use strict';

import { Toggler } from "./admin/core/common";
import Controller from "./admin/core/Controller";
import AddNewSite from "./admin/network/AddNewSite";
import TermTranslator from "./admin/term-translation/TermTranslator";
import UserBackEndLanguage from "./admin/user-settings/UserBackEndLanguage";

/**
 * The MultilingualPress admin namespace.
 * @namespace
 * @alias MultilingualPressAdmin
 */
const MLP = window.MultilingualPressAdmin = {};

const toggler = new Toggler( {
	el: 'body',
	events: {
		'click .mlp-click-toggler': 'toggleElement'
	}
} );
/**
 * The MultilingualPress toggler instance.
 * @type {Toggler}
 */
MLP.toggler = toggler;

// Initialize the state togglers.
toggler.initializeStateTogglers();

const controller = new Controller();
/**
 * The MultilingualPress admin controller instance.
 * @type {Controller}
 */
MLP.controller = controller;

// Register the AddNewSite module for the Add New Site network admin page.
controller.registerModule( 'network/site-new.php', AddNewSite, {
	el: '#wpbody-content form',
	events: {
		'change #site-language': 'adaptLanguage',
		'change #mlp-base-site-id': 'togglePluginsRow'
	}
} );

// Register the TermTranslator module for the Edit Tags admin page.
controller.registerModule( 'edit-tags.php', TermTranslator, {
	el: '#mlp-term-translations',
	events: {
		'change select': 'propagateSelectedTerm'
	}
} );

// Register the UserBackEndLanguage module for the General Settings admin page.
controller.registerModule( 'options-general.php', UserBackEndLanguage, {
	el: '#WPLANG'
}, module => module.updateSiteLanguage() );

// Initialize the admin controller, and thus all modules registered for the current admin page.
jQuery( controller.initialize );
