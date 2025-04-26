# ClickUp Addon for Elementor

![ClickUp + Elementor](https://user-images.githubusercontent.com/96265013/178159595-52c9aafc-f5dc-43f0-9b0c-40e4f5cc8bd0.png)

[![WordPress Plugin Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://wordpress.org/plugins/clickup-addon-elementor/)
[![License: GPL v2](https://img.shields.io/badge/License-GPL_v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)
[![Tested WP Version](https://img.shields.io/badge/WordPress-6.8-success.svg)](https://wordpress.org/plugins/clickup-addon-elementor/)
[![Required Elementor Version](https://img.shields.io/badge/Elementor-3.0+-success.svg)](https://wordpress.org/plugins/elementor/)

Connect your Elementor Forms with ClickUp to automatically create tasks and documents from form submissions.

## 📋 Description

The ClickUp Addon for Elementor allows you to seamlessly integrate your Elementor Forms with ClickUp, the all-in-one productivity platform. With this plugin, you can automate your workflow by creating tasks or documents in ClickUp when users submit forms on your WordPress website.

### 🌟 Key Features

- **Create ClickUp Tasks**: Automatically generate tasks in ClickUp from form submissions
- **Create ClickUp Documents**: Generate documents with form data
- **Dynamic Field Mapping**: Map form fields to ClickUp properties
- **Custom Fields Support**: Send data to ClickUp custom fields
- **Due Dates**: Set due dates for tasks based on form fields or relative dates
- **Task Assignees**: Assign tasks to team members
- **Status Selection**: Set the initial status for tasks
- **Priority Settings**: Define task priorities
- **Tags**: Add tags to your ClickUp tasks

### 🚀 Use Cases

- **Customer Support**: Create support tickets automatically from contact forms
- **Project Intake**: Generate project briefs from client questionnaires
- **Quote Requests**: Transform quote requests into actionable tasks
- **Job Applications**: Organize job applications as tasks
- **Event Registrations**: Manage event signups in ClickUp
- **Order Forms**: Create tasks for processing orders

## 📦 Installation

1. **Upload** the `clickup-addon-elementor` folder to the `/wp-content/plugins/` directory
2. **Activate** the plugin through the 'Plugins' menu in WordPress
3. **Configure** your ClickUp API key by going to Elementor > ClickUp Integration
4. **Add** the ClickUp action to your Elementor forms

## 🔧 Requirements

- WordPress 5.6+ (Compatible with WordPress 6.8)
- Elementor 3.0+
- Elementor Pro 3.0+
- PHP 8.3+
- Active ClickUp account

## ⚙️ Configuration

### API Key Setup

1. **Login** to your ClickUp account
2. **Navigate** to Settings → Apps
3. **Find** the "API Token" section, and click "Generate"
4. **Copy** the generated token
5. **Open** WordPress admin, go to Elementor > ClickUp Integration
6. **Paste** your API key and click "Verify & Save"

![API Key Setup](https://elementorexperts.com/wp-content/uploads/2024/04/clickup-api-key-setup.jpg)

### Form Configuration

1. **Edit** an Elementor form
2. **Go to** the "Actions After Submit" section
3. **Add** the "ClickUp" action
4. **Choose** whether to create a task or document
5. **Select** your workspace, space, and list (for tasks)
6. **Configure** the task/document name, description, and other fields
7. **Save** your changes

![Form Configuration](https://elementorexperts.com/wp-content/uploads/2024/04/clickup-form-action.jpg)

## 🧩 Field Mapping

Use dynamic values in the form fields by inserting variables:

- `{field_id}` - Replace with the value of the form field with ID "field_id"
- `{form_name}` - The name of the form
- `{all_fields}` - A formatted list of all form fields and their values
- `{date}` - Current date
- `{time}` - Current time
- `{page_title}` - Title of the page containing the form
- `{page_url}` - URL of the page containing the form
- `{user_agent}` - User's browser information
- `{remote_ip}` - User's IP address

### Examples

- **Task Name**: `New request from {name}`
- **Description**: 
```
Customer Email: {email}
Phone: {phone}

Message: {message}
```
- **Due Date**: `+3 days` or `{due_date_field}`
- **Tags**: `website, form-submission, {service}`

## 📊 Advanced Usage

### Custom Fields

To map form fields to ClickUp custom fields:

1. Add a custom field in your ClickUp list
2. In the form action settings, find the "Custom Fields" section
3. Select the custom field from the dropdown
4. Map it to your form field using the `{field_id}` syntax

### Relative Due Dates

You can set relative due dates using the following formats:

- `+n days` - n days from today
- `+n weeks` - n weeks from today
- `+n months` - n months from today

### Task Priority

Set task priority using:

- `1` - Urgent
- `2` - High
- `3` - Normal
- `4` - Low

## 🔍 Troubleshooting

### Common Issues

1. **API Key Not Working**: Ensure your API key is correct and has the necessary permissions
2. **Fields Not Mapping**: Verify that your field IDs match exactly what's in your form
3. **Task Not Being Created**: Check your ClickUp permissions for the list you're trying to add tasks to

### Debug Mode

To enable debug logging:

1. Add the following to your wp-config.php:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```
2. Check the debug.log file in your wp-content directory for errors

## ❓ Frequently Asked Questions

### Does this plugin require Elementor Pro?

Yes, the ClickUp Addon requires Elementor Pro since it integrates with the Elementor Forms module, which is only available in Elementor Pro.

### Can I map form fields to ClickUp custom fields?

Yes, you can map your form fields to custom fields in ClickUp by setting up the custom fields mapping in the form action settings.

### Can I assign tasks to specific ClickUp users?

Yes, you can specify assignees for tasks either as static values or dynamically from form fields.

### Does this work with conditional logic in Elementor Forms?

Yes, since this is an action that runs after form submission, it respects Elementor Forms' conditional logic settings.

## 📱 Support

For support, feature requests, or bug reports, please contact us:

- Email: [support@elementorexperts.com](mailto:support@elementorexperts.com)
- Website: [Contact Support](https://elementorexperts.com/contact/)

## 📜 Changelog

### 1.0.0
* Initial release
* Task creation support
* Document creation support
* Dynamic field mapping
* Custom fields integration
* Due date settings
* Task assignees

## 👨‍💻 About Hassan Ali | Elementor Experts

We specialize in creating custom Elementor addons and solutions to help businesses streamline their workflows and improve their websites. Visit [Elementor Experts](https://elementorexperts.com) to learn more about our services.

## 📝 License

This plugin is licensed under the GPL v2 or later.

---

Made with ❤️ by [Hassan Ali | Elementor Experts](https://elementorexperts.com) 