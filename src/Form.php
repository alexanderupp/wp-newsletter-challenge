<?php
	/**
	 * Newsletter form handler
	 */

	namespace WPNewsletterChallenge;

	use function ob_get_clean as ob_get_clean;
	use function ob_start as ob_start;

	class Form {
		const INLINE = 0;
		const POPUP = 1;

		public $image;
		public $imageSide;
		
		public function __construct() {
			
		}

		/**
		 * Register all scripts and styles with WP
		 * 
		 * @return void
		 */
		public function add_scripts_and_styles() : void {
			wp_enqueue_script("wnc-form", WPNC_PLUGIN_URL . "assets/js/wpform.js", ["jquery"], null, true);

			wp_enqueue_style("wnc-form", WPNC_PLUGIN_URL . "assets/css/wnc.css");
		}

		/**
		 * Handles which type of form to render
		 * 
		 * @param int $_type			Form type
		 * @param array $_params		Form settings
		 * @return string				Rendered form
		 */
		public function render(int $_type, array $_params) {
			switch($_type) {
				case self::INLINE:
					return $this->render_inline($_params);
					break;
				case self::POPUP:
					return $this->render_popup($_params);
					break;
				default:
					return "";
					break;
			}
		}

		/**
		 * Renders the inline form
		 * 
		 * @return void
		 */
		private function render_inline(array $_params) {
			$labels = array(
				"email" => "Enter your email address",
				"name" => "Enter your name",
				"optin" => "I agree to receive emails from " . htmlspecialchars(get_option("blogname", "this website"))
			);

			$formClasses = array(
				"wnc-form form-inline"
			);

			$optinID = hash("md5", hrtime(true));

			ob_start();
		?>
			<div class="<?php echo implode(" ", $formClasses); ?>">
				<div class="wnc-form__content">
					<h2 class="wnc-form--h2"><?php echo htmlspecialchars($_params["title"]); ?></h2>
					<p class="wnc-form--p"><?php echo htmlspecialchars($_params["description"]); ?></p>
					<form class="wnc-form__form" method="post" action="<?php echo admin_url("admin-post.php"); ?>">
						<span class="wnc-form__status"></span>
						<input type="text" class="wnc-form-field form-field--text" aria-role="input" araia-label="<?php echo $labels["name"] ?>" name="wnc-form_name" placeholder="<?php echo $labels["name"] ?>" required/>
						<input type="email" class="wnc-form-field form-field--text wnc-form-email" aria-role="input" araia-label="<?php echo $labels["email"] ?>" name="wnc-form_email" placeholder="<?php echo $labels["email"] ?>" requried/>
						<label for="<?php echo $optinID; ?>" class="form-field--label"><input type="checkbox" aria-role="checkbox" araia-label="<?php echo $labels["optin"] ?>" class="wnc-optin" value="yes" name="wnc-form_optin" required id="<?php echo $optinID; ?>"/> <?php echo $labels["optin"]; ?></label>
						<input type="hidden" name="action" value="wnc-submission"/>
						<p>
							<button type="submit" class="wnc-form--button">Subscribe</button>
						</p>
					</form>
				</div>
			</div>
		<?php
			return ob_get_clean();
		}

		/**
		 * Renders the popup form
		 * 
		 * @return void
		 */
		private function render_popup(array $_params) {
			$labels = array(
				"email" => "Enter your email address",
				"name" => "Enter your name",
				"optin" => "I agree to receive emails from " . htmlspecialchars(get_option("blogname", "this website"))
			);

			$imageSide = ($_params["imageSide"] == "left") ? "image--left" : "image--right";
			$accentColor = $_params["accentColor"];

			$formClasses = array(
				"wnc-form form-popup",
				$imageSide,
				"accent-" . $accentColor);

			$optinID = hash("md5", hrtime(true));

			ob_start();
		?>
			<div id="wnc-popup">
				<div class="<?php echo implode(" ", $formClasses); ?>">
					<label class="wnc-popup--close">x</label>
					<?php if($_params["imageSide"] !== "none") { ?>
					<div class="wnc-form__image">
						<img src="<?php echo $_params["image"]; ?>" alt="<?php echo htmlspecialchars($_params["title"]); ?>"/>
					</div>
					<?php } ?>
					<div class="wnc-form__content">
						<h2 class="wnc-form--h2"><?php echo htmlspecialchars($_params["title"]); ?></h2>
						<p class="wnc-form--p"><?php echo htmlspecialchars($_params["description"]); ?></p>
						<form class="wnc-form__form" method="post" action="<?php echo admin_url("admin-post.php"); ?>">
							<span class="wnc-form__status"></span>
							<input type="text" class="wnc-form-field form-field--text" aria-role="input" araia-label="<?php echo $labels["name"] ?>" name="wnc-form_name" placeholder="<?php echo $labels["name"] ?>" required/>
							<input type="email" class="wnc-form-field form-field--text wnc-form-email" aria-role="input" araia-label="<?php echo $labels["email"] ?>" name="wnc-form_email" placeholder="<?php echo $labels["email"] ?>" requried/>
							<label for="<?php echo $optinID; ?>" class="form-field--label"><input type="checkbox" aria-role="checkbox" araia-label="<?php echo $labels["optin"] ?>" class="wnc-optin" value="yes" name="wnc-form_optin" required id="<?php echo $optinID; ?>"/> <?php echo $labels["optin"]; ?></label>
							<input type="hidden" name="action" value="wnc-submission"/>
							<p>
								<button type="submit" class="wnc-form--button">Subscribe</button>
							</p>
						</form>
					</div>
				</div>
			</div>
		<?php
			return ob_get_clean();
		}
	}