<?php
	/**
	 * Handler for the Newsletters custom settings
	 */

	namespace WPNewsletterChallenge;

	use function add_action as add_action;
	use function add_options_page as add_options_page;
	use function get_option as get_option;
	use function update_option as update_option;
	
	class Settings {
		// default settings
		public $settings = array(
			"accentColor" => "lime",
			"description" => "We'll send you only the most high-quality emails. No fat. All substance.",
			"fullSite" => true,
			"image" => WPNC_PLUGIN_URL . "/assets/img/default.png",
			"imageSide" => "left",
			"messages" => [
				"duplicate" => "This email is already subscribed to our newsletter.",
				"error" => "There was an error submitting your request. Please try again.",
				"thankyou" => "Thank you! Your subscription request has been received."
			],
			"title" => "Subscribe our newsletter!"
		);

		public $ID = "wp-newsletter-challenge";

		/**
		 * Class constuctor
		 * Loads our custom settings
		 */
		public function __construct() {
			$savedSettings = get_option("wp_newsletter_settings");

			if(is_array($savedSettings)) {
				$this->settings = array_merge($this->settings, $savedSettings);
			}

			add_action("admin_init", [$this, "settings_init"]);
			add_action("admin_menu", [$this, "add_settings_menu"]);
		}

		/**
		 * Handler to get ther private settings values
		 * 
		 * @return mixed		Value of specified setting | NULL if not found
		 */
		public function __get(string $_setting) : mixed {
			if(!isset($this->$_setting)) {
				return $this->settings[$_setting] ?? NULL;
			}

			return NULL;
		}

		/**
		 * TODO
		 * 
		 * @return
		 */
		public function add_settings_menu() {
			if(current_user_can("manage_options")) {
				add_options_page(
					"Newsletter Form",
					"Newsletter Form",
					"manage_options",
					$this->ID,
					array($this, "render_settings_page")
				);
			}
		}

		/**
		 * Returns an array with all of the settings
		 * 
		 * @return array
		 */
		public function get_all_settings() : array {
			return [];
		}

		/**
		 * Creates the accent color select option
		 * 
		 * @param array $args		Field options
		 * @return void
		 */
		public function render_field_select(array $_args) : void {
			ob_start();

			$boolToText = array(
				"Disabled",
				"Enabled"
			);
		?>
			<select name="wp_newsletter_settings[<?php echo $_args["name"]; ?>]">
			<?php
				foreach($_args["options"] as $option) {
					if(gettype($option) == "boolean") {
						$optionName = $boolToText[(int)$option];
					} else {
						$optionName = ucfirst($option);
					}
			?>
				<option value="<?php echo $option; ?>" <?php echo ($_args["default_value"] == $option) ? "selected" : ""; ?>><?php echo $optionName; ?></option>
			<?php
				}
			?>
			</select>
		<?php
			echo ob_get_clean();
		}

		/**
		 * Creates a text input for the settings page
		 * 
		 * @param array $args		Field options
		 * @return void
		 */
		public function render_field_text(array $_args) : void {
			ob_start();
		?>
			<input type="text" name="wp_newsletter_settings[<?php echo $_args["name"]; ?>]" class="<?php echo $_args["class"]; ?>" placeholder="<?php echo $_args["placeholder"]; ?>" value="<?php echo $_args["default_value"]; ?>"/>
		<?php
			echo ob_get_clean();
		}

		/**
		 * Renders the full settings page
		 * 
		 * @return void
		 */
		public function render_settings_page() : void {
			if(!current_user_can("manage_options")) {
				return;
			}
			
			ob_start();
		?>
		<div class="wrap">
			<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
			<form action="options.php" method="post">
			<?php
				settings_fields($this->ID);
				do_settings_sections($this->ID);
				submit_button("Save Settings");
			?>
			</form>
		</div>
		<?php
			echo ob_get_clean();			
		}

		/**
		 * Renders the section heading
		 * 
		 * @return void
		 */
		public function render_settings_heading() : void {
			ob_start();
		?>
			<p>The following options relate to how the popup and inline shortcode subscription forms will look and function.</p>
		<?php
			echo ob_get_clean();
		}

		/**
		 * Register our settings with WordPress
		 * 
		 * @return void
		 */
		public function settings_init() : void {
			//register settings
			add_settings_section($this->ID . "-settings", "Newsletter Settings", [$this, "render_settings_heading"], $this->ID);

			// accent color field
			add_settings_field(
				"accentColor",
				"Accent Color",
				[$this, "render_field_select"],
				$this->ID,
				$this->ID . "-settings",
				array(
					"default_value" => $this->settings["accentColor"],
					"name" => "accentColor",
					"options" => ["aqua", "lime", "orange", "white"]
				)
			);

			// image position
			add_settings_field(
				"imageSide",
				"Popup Form Image Position",
				[$this, "render_field_select"],
				$this->ID,
				$this->ID . "-settings",
				array(
					"default_value" => $this->settings["imageSide"],
					"name" => "imageSide",
					"options" => ["left", "right", "none"]
				)
			);

			// enable popup menu
			add_settings_field(
				"fullSite",
				"Enable Popup Form",
				[$this, "render_field_select"],
				$this->ID,
				$this->ID . "-settings",
				array(
					"default_value" => $this->settings["fullSite"],
					"name" => "fullSite",
					"options" => [true, false]
				)
			);

			// title
			add_settings_field(
				"title",
				"Title",
				[$this, "render_field_text"],
				$this->ID,
				$this->ID . "-settings",
				array(
					"class" => "form-field form-required",
					"default_value" => $this->settings["title"],
					"name" => "title",
					"placeholder" => "Enter a short title"
				)
			);

			// description field
			add_settings_field(
				"description",
				"Description",
				[$this, "render_field_text"],
				$this->ID,
				$this->ID . "-settings",
				array(
					"class" => "form-field form-required",
					"default_value" => $this->settings["description"],
					"name" => "description",
					"placeholder" => "Enter a short blurb selling the newsletter"
				)
			);

			register_setting($this->ID, "wp_newsletter_settings");
		}

		/**
		 * Adds the options page to WordPress
		 * 
		 * @return void
		 */
		public function settings_page() : void {
			add_options_page(
				"WP Newsletter",
				"WP Newsletter",
				"manage_options",
				"wp-newsletter-challenge",
				[$this, "render_settings_page"]
			);
		}
	}