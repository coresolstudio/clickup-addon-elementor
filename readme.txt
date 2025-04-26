=== ClickUp Addon - Elementor ===
Contributors: hassanali, elementorexperts
Tags: clickup, elementor, forms, integration, tasks, productivity
Requires at least: 5.6
Tested up to: 6.8
Requires PHP: 8.3
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Connect your Elementor Forms with ClickUp to automatically create tasks and documents from form submissions.

== Description ==

The ClickUp Addon for Elementor allows you to seamlessly integrate your Elementor Forms with ClickUp, the all-in-one productivity platform. With this plugin, you can automatically create tasks or documents in ClickUp when users submit forms on your WordPress website.

= Key Features =

* **Create ClickUp Tasks**: Automatically generate tasks in ClickUp from form submissions
* **Create ClickUp Documents**: Generate documents with form data
* **Dynamic Field Mapping**: Map form fields to ClickUp properties
* **Custom Fields Support**: Send data to ClickUp custom fields
* **Due Dates**: Set due dates for tasks based on form fields or relative dates
* **Task Assignees**: Assign tasks to team members

= Use Cases =

* Customer support requests
* Project intake forms
* Quote requests
* Job applications
* Event registrations
* Order forms

= Requirements =

* WordPress 5.6+
* Elementor 3.0+
* Elementor Pro 3.0+
* PHP 7.4+

= About Hassan Ali | Elementor Experts =

We specialize in creating custom Elementor addons and solutions to help businesses streamline their workflows and improve their websites. Visit [Elementor Experts](https://elementorexperts.com) to learn more about our services.

== Installation ==

1. Upload the `clickup-addon-elementor` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Elementor > ClickUp Integration to configure your API key
4. Create or edit an Elementor form and add the ClickUp action under "Actions After Submit"

== Configuration ==

= API Key Setup =

1. Login to your ClickUp account
2. Go to Settings → Apps
3. Under the "API Token" section, click "Generate"
4. Copy the generated token
5. In WordPress, go to Elementor > ClickUp Integration
6. Paste your API key and save

= API Documentation =

For developers who want to understand the underlying API integration, please refer to the [ClickUp API Documentation](https://developer.clickup.com/docs/authentication#personal-token) for more details about authentication and available endpoints.


= Form Configuration =

1. Edit an Elementor form
2. Go to the "Actions After Submit" section
3. Add the "ClickUp" action
4. Choose whether to create a task or document
5. Select your workspace, space, and list (for tasks)
6. Configure the task/document name, description, and other fields
7. Save your changes

== Field Mapping ==

Use dynamic values in the form fields:

* `{field_id}` - Replace with the value of the form field with ID "field_id"
* `{form_name}` - The name of the form
* `{all_fields}` - A formatted list of all form fields and their values

= Examples =

* Task Name: `New request from {name}`
* Description: `Customer Email: {email}\nPhone: {phone}\n\nMessage: {message}`
* Due Date: `+3 days` or `{due_date_field}`

== Frequently Asked Questions ==

= Does this plugin require Elementor Pro? =

Yes, the ClickUp Addon requires Elementor Pro since it integrates with the Elementor Forms module, which is only available in Elementor Pro.

= Can I map form fields to ClickUp custom fields? =

Yes, you can map your form fields to custom fields in ClickUp by setting up the custom fields mapping in the form action settings.

= Can I assign tasks to specific ClickUp users? =

Yes, you can specify assignees for tasks either as static values or dynamically from form fields.

= Does this work with conditional logic in Elementor Forms? =

Yes, since this is an action that runs after form submission, it respects Elementor Forms' conditional logic settings.

== Screenshots ==

1. ClickUp Integration Settings
2. Adding the ClickUp action to an Elementor form
3. Configuring the ClickUp task settings
4. Mapping form fields to ClickUp properties
5. Tasks created in ClickUp from form submissions

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release of the ClickUp Addon for Elementor. 