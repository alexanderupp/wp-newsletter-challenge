<?php
	/**
	 * Handles the main interaction with the WordPress site
	 */

	namespace WPNewsletterChallenge;

	/**
	 * Let PHP know which functions we are using that are in the global namespace
	 * This saves us a little bit of overhead at run-time
	 * We could add a backslash to each function call, but I think that looks ugly
	 * Plus, with this route we get a quick overview of what the class is up to
	 */
	use function add_action as add_action;
	use function add_shortcode as add_shortcode;
	use function shortcode_atts as shortcode_atts;

	class WPNewsletterChallenge {
		private $form;
		private $scriptsAndStyles = false;
		private $settings;
	
		public function __construct() {
			$this->settings = new Settings();
		}

		/**
		 * Entry point into the plugin. This functions adds our required
		 * hooks to WP.
		 * 
		 * @return void
		 */
		public function init() : void {
			add_action("init", [$this, "register_shortcode"]);
			add_action("admin_post_wnc-submission", ["WPNewsletterChallenge\Subscriber", "submit_request"]);
			add_action("admin_post_nopriv_wnc-submission", ["WPNewsletterChallenge\Subscriber", "submit_request"]);

			$this->form = new Form();

			if($this->settings->fullSite && empty($_COOKIE["hide-newsletter"])) {
				if(!$this->scriptsAndStyles) {
					$this->form->add_scripts_and_styles();
					$this->scriptsAndStyles = true;
				}
				// add css and scripts
				add_action("wp_footer", [$this, "render_form_to_footer"]);
			}

			// I don't like but I've already put too much time into this
			$GLOBALS["newsletter-settings"] = $this->settings->settings;
		}

		/**asdasd

		/**
		 * Handler for the newsletter popup shortcode
		 * 
		 * @return string		Rendered Newsletter HTML
		 */
		public function newsletter_shortcode($_atts, $_content, $_shortcode) : string {
			$defaults = $this->settings->settings;
			$atts = array_merge($defaults, shortcode_atts([
				"accentColor" => "lime",
				"image" => WPNC_PLUGIN_URL . "/assets/img/default.png",
				"imageSide" => "left"
			], $_atts, $_shortcode));

			if(!$this->scriptsAndStyles) {
				$this->form->add_scripts_and_styles();
				$this->scriptsAndStyles = true;
			}

			return $this->form->render(Form::INLINE, $atts);
		}

		/**
		 * Register the newsletter form shortcode with WordPress
		 * We return void here because add_shortcode does not return 
		 * anything so we have to trust WP
		 * 
		 * @return void
		 */
		public function register_shortcode() : void {
			add_shortcode("wpnc_newsletter", [$this, "newsletter_shortcode"]);
		}

		/**
		 * Handler for the WP_FOOTER to render newsletter form 
		 * to the footer
		 * 
		 * @return void
		 */
		public function render_form_to_footer() {
			$params = $this->settings->settings;
		
			echo $this->form->render(Form::POPUP, $params);
		}
	}