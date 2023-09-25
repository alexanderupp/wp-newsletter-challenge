<?php
	/**
	 * PHP class that handles all functions related
	 * to the "Subscriber" custom post
	 */

	namespace WPNewsletterChallenge;

	/**
	 * Let PHP know which functions we are using that are in the global namespace
	 * This saves us a little bit of overhead at run-time
	 * We could add a backslash to each function call, but I think that looks ugly
	 * Plus, now we get a quick overview of what the class is up to
	 */
	use function count as count;
	use function get_post as get_post;
	use function header as header;
	use function implode as implode;
	use function is_wp_error as is_wp_error;
	use function json_encode as json_encode;
	use function register_post_type as register_post_type;
	use function sanitize_email as sanitize_email;
	use function sanitize_text_field as sanitize_text_field;
	use function strip_tags as strip_tags;
	use function wp_insert_post as wp_insert_post;

	class Subscriber {
		/**
		 * Adds a subscriber post to the database
		 * 
		 * @param array $data		Array (email|name)
		 * @return array|bool		Array containing the new sub info, false otherwise
		 */
		public static function add(array $data) : array|bool {
			$messages = $GLOBALS["newsletter-settings"]["messages"];
			// check for an existing subscriber
			$existing = get_posts([
				"post_name__in" => [$data["email"]],
				"post_status" => "any",
				"post_type" => "subscribers"
			]);

			if(count($existing) > 0) {
				return array(
					"insert" => false,
					"msg" => $messages["duplicate"]
				);
			}

			$insert = wp_insert_post([
				"post_content" => self::new_subscriber_content($data),
				"post_status" => "publish",
				"post_title" => $data["email"],
				"post_type" => "subscribers"
			]);

			if($insert) {
				add_post_meta($insert, "_subscriber_name", $data["name"]);

				$msg = $messages["thankyou"];
			} else {
				$msg = $messages["error"];
			}

			return array(
				"insert" => true,
				"ID" => $insert,
				"msg" => $msg
			);
		}

		/**
		 * Formats the new subscriber's content feld
		 * 
		 * @param array $data		Array (email|name)
		 * @return string			Formatted text
		 */
		public static function new_subscriber_content(array $data) : string {
			$content = array(
				"Subscriber Info:",
				"Email Address: " . $data["email"],
				"Name: " . $data["name"]
			);

			return implode("\n", $content);
		}

		/**
		 * Register the custom post type with WordPress
		 * 
		 * @return bool			Whether the post type was registered or not
		 */ 
		public static function register_type() : bool {
			$labels = array(
				"menu_name" => "Subscribers",
				"name" => "Subscribers",
				"singular_name" => "Subscriber"
			);
			$args = array(
				"description" => "Post type that holds all users who have subscribed to the newsletter",
				"has_archive" => false,
				"labels" => $labels,
				"menu_position" => 41,
				"public" => true,
				"support" => [
					"content",
					"title"
				]
			);

			$reg = register_post_type("subscribers", $args);

			return !is_wp_error($reg);
		}

		/**
		 * Initiates a subscription request
		 * 
		 * @return void
		 */
		public static function submit_request() : void {
			header("Content-Type: application/json");

			if(!self::validate_submission()) {
				echo json_encode([
					"result" => false,
					"msg" => "There was an error submitting your request. Please try again."
				]);
				exit;
			}

			$data = array(
				"email" => sanitize_email($_POST["wnc-form_email"]),
				"name" => sanitize_text_field($_POST["wnc-form_name"])
			);

			$added = self::add($data);

			if($added) {
				echo json_encode([
					"result" => $added["insert"],
					"msg" => $added["msg"]
				]);
			} else {
				echo json_encode([
					"result" => false,
					"msg" => "There was an error processing your submission. Please try again."
				]);
			}

			exit;
		}

		/**
		 * Checks whether a submitted subscription request is valid
		 * 
		 * @return bool
		 */
		public static function validate_submission() : bool {
			$fields = [
				"wnc-form_name",
				"wnc-form_email",
				"wnc-form_optin"	
			];

			// make sure all fields are present
			foreach($fields as $field) {
				if(empty(strip_tags($_POST[$field]))) {
					return false;
				}
			}

			return true;
		}
	}