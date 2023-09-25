/**
 * Most modern browsers will automatically handle form validation
 * if the applicable inputs have the "required" attribute. However,
 * we will add form validation here as well just in case/because it
 * was requested in the project brief.
 */

(function($) {
	// We're basically only using jQuery for it's shorthand DOMDocumentReady functionality
	// There is nothing wrong with using jQuery, but I quite like vanilla JS now
	$(function() {
		// listen to all form submissions
		//$(".wnc-form__form").on("submit", function(e) {
		(document.querySelectorAll(".wnc-form__form")).forEach((form) => {
			form.addEventListener("submit", (e) => {
				e.preventDefault();
				// validate the form
				const valid = wpNewsletter.validate(form);

				if(valid) {
					// submit the form data to WP
					wpNewsletter.submit(form);
				} else {
					// inform the user of an error
					wpNewsletter.displayError(form);
				}

				// we don't want the form to actually submit
				return false;
			});
		});

		(document.querySelectorAll(".wnc-popup--close")).forEach((e) => {
			e.addEventListener("click", (event) => {
				event.preventDefault();
				event.stopPropagation();

				document.getElementById("wnc-popup").classList.toggle("wnc-popup--hidden");
				document.cookie = "hide-newsletter=1;path=/;expires=" + ( new Date((new Date()).getTime() + (2592000000)));
			});
		});
	});

	var wpNewsletter = {
		error: "",
		fields: null,
		displayError: function(form) {
			(form.querySelector(".wnc-form__status")).innerHTML = this.error;
			return true;
		},
		submit: function(form) {
			form.classList.add("submitting");

			const bns = this;
			const url = form.getAttribute("action");

			fetch(url, {
				method: "POST",
				body: new URLSearchParams(bns.fields)
			}).then((response) => {
				if(!response.ok) {
					bns.error = "There was an error sending your subscrption request.";
					bns.displayError(form);
					form.classList.remove("submitting");

					return false;
				}

				return response.json();
			}).then((json) => {
				if(!json.result) {
					bns.error = json.msg || "There was an unknown error.";
					bns.displayError(form);
				} else {
					form.innerHTML = `<p>${json.msg}</p>`;
				}

				form.classList.remove("submitting");
			});
		},
		validate: function(form) {
			let invalid = false;
			const fields = new FormData(form);
			const bns = this;
			
			// check if all requried fields are present
			// 3 user inputs + 1 hidden
			if(Array.from(fields.keys()).length < 4) {
				bns.error = "Please complete all fields";

				return false;
			}
			
			// check to see if each field has any value
			fields.forEach((field) => {
				if(field.value == "") {
					bns.error = "Please complete all fields";
					invalid = true;
					return;
				}
			});

			if(invalid) {
				return false;
			}

			bns.fields = fields;

			return true;
		}
	}
})(jQuery || $);