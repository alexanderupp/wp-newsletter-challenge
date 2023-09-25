<?php
	/**
	 * Plugin Name: WordPress Newsletter Challenge
	 * Description: A WordPress plugin that adds newsletter signup functionality to a WordPress website. Done as a coding/technical challenge
	 * Author: Alex Rupp
	 * Author URI: https://alexrupp.me
	 * Requires PHP: 8.0
	 * Version: 1.0.1
	 */

	// This file should not be called directly
	if(!defined("ABSPATH")) {
		exit;
	}

	define("WPNC_PLUGIN_DIR", plugin_dir_path(__FILE__));
	define("WPNC_PLUGIN_URL", plugin_dir_url(__FILE__));

	// Include our required files.
	// I prefer setting up an autoloader, but WordPress can be a little fussy.
	require_once WPNC_PLUGIN_DIR . "/src/WPNewsletterChallenge.php";
	require_once WPNC_PLUGIN_DIR . "/src/Form.php";
	require_once WPNC_PLUGIN_DIR . "/src/Settings.php";
	require_once WPNC_PLUGIN_DIR . "/src/Subscriber.php";

	$WPNewsletterChallenge = new WPNewsletterChallenge\WPNewsletterChallenge();
	$WPNewsletterChallenge->init();

	add_action("init", "WPNewsletterChallenge\Subscriber::register_type");