<?php
/**
 * ClickUp Form Action
 *
 * Handles the integration between Elementor Forms and ClickUp
 *
 * @package clickup-addon-elementor
 * @since 1.0.0
 */

// Prevent direct access
defined( 'ABSPATH' ) || die( 'Access denied.' );

/**
 * ClickUp_Elementor_Action class.
 *
 * @since 1.0.0
 */
class ClickUp_Elementor_Action extends \ElementorPro\Modules\Forms\Classes\Action_Base {

    /**
     * Get action name.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_name() {
        return 'clickup';
    }

    /**
     * Get action label.
     *
     * @since 1.0.0
     * @return string
     */
    public function get_label() {
        return esc_html__( 'ClickUp', 'clickup-addon-elementor' );
    }

    /**
     * Register settings section.
     *
     * @since 1.0.0
     * @param \Elementor\Widget_Base $widget Widget instance.
     * @return void
     */
    public function register_settings_section( $widget ) {

        
        
        $widget->start_controls_section(
            'section_clickup',
            [
                'label' => esc_html__( 'ClickUp', 'clickup-addon-elementor' ),
                'condition' => [
                    'submit_actions' => $this->get_name(),
                ],
            ]
        );

        $widget->add_control(
            'clickup_integration_info',
            [
                'type' => \Elementor\Controls_Manager::ALERT,
                'alert_type' => 'info',
                'content' => sprintf(
                    '<p>%s</p>',
                    sprintf(
                        esc_html__( 'Make sure you have configured your ClickUp API key in the %1$ssettings page%2$s.', 'clickup-addon-elementor' ),
                        '<a href="' . admin_url( 'admin.php?page=clickup-elementor-settings' ) . '" target="_blank">',
                        '</a>'
                    )
                ),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );

        $widget->add_control(
            'clickup_action_type',
            [
                'label' => esc_html__( 'Action Type', 'clickup-addon-elementor' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'task',
                'options' => [
                    'task' => esc_html__( 'Create Task', 'clickup-addon-elementor' ),
                    'document' => esc_html__( 'Create Document', 'clickup-addon-elementor' ),
                ],
            ]
        );

        $widget->add_control(
            'clickup_workspace',
            [
                'label' => esc_html__( 'Workspace', 'clickup-addon-elementor' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_workspace_options(),
                'description' => esc_html__( 'Select your ClickUp Workspace', 'clickup-addon-elementor' ),
                'classes' => 'clickup-elementor-workspace-select',
                'placeholder' => esc_html__( 'Select a Workspace', 'clickup-addon-elementor' ),
            ]
        );

        $widget->add_control(
            'clickup_space',
            [
                'label' => esc_html__( 'Space', 'clickup-addon-elementor' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [],
                'description' => esc_html__( 'Select your ClickUp Space', 'clickup-addon-elementor' ),
                'classes' => 'clickup-elementor-space-select',
                'placeholder' => esc_html__( 'Select a Space', 'clickup-addon-elementor' ),
                'condition' => [
                    'clickup_workspace!' => '',
                ],
                'dynamic' => [
                    'active' => true,
                ],
                'refresh_on_change' => [
                    'clickup_workspace',
                ],
            ]
        );

        $widget->add_control(
            'clickup_list',
            [
                'label' => esc_html__( 'List', 'clickup-addon-elementor' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [],
                'description' => esc_html__( 'Select your ClickUp List', 'clickup-addon-elementor' ),
                'classes' => 'clickup-elementor-list-select',
                'placeholder' => esc_html__( 'Select a List', 'clickup-addon-elementor' ),
                'condition' => [
                    'clickup_space!' => '',
                    'clickup_action_type' => 'task',
                ],
            ]
        );

        $widget->add_control(
            'clickup_task_name',
            [
                'label' => esc_html__( 'Task Name', 'clickup-addon-elementor' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => esc_html__( 'Enter the task name or use {field_id} for dynamic values', 'clickup-addon-elementor' ),
                'placeholder' => esc_html__( 'New task from {form_name}', 'clickup-addon-elementor' ),
                'condition' => [
                    'clickup_action_type' => 'task',
                    'clickup_list!' => '',
                ],
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $widget->add_control(
            'clickup_document_name',
            [
                'label' => esc_html__( 'Document Name', 'clickup-addon-elementor' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => esc_html__( 'Enter the document name or use {field_id} for dynamic values', 'clickup-addon-elementor' ),
                'placeholder' => esc_html__( 'New document from {form_name}', 'clickup-addon-elementor' ),
                'condition' => [
                    'clickup_action_type' => 'document',
                    'clickup_space!' => '',
                ],
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $widget->add_control(
            'clickup_description',
            [
                'label' => esc_html__( 'Description', 'clickup-addon-elementor' ),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'description' => esc_html__( 'Enter the description or use {field_id} for dynamic values. Use {all_fields} to include all form data.', 'clickup-addon-elementor' ),
                'placeholder' => esc_html__( '{all_fields}', 'clickup-addon-elementor' ),
                'condition' => [
                    'clickup_action_type!' => '',
                ],
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $widget->add_control(
            'clickup_status',
            [
                'label' => esc_html__( 'Status', 'clickup-addon-elementor' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [],
                'description' => esc_html__( 'Select the task status', 'clickup-addon-elementor' ),
                'classes' => 'clickup-elementor-status-select',
                'placeholder' => esc_html__( 'Default Status', 'clickup-addon-elementor' ),
                'condition' => [
                    'clickup_action_type' => 'task',
                    'clickup_list!' => '',
                ],
            ]
        );

        $widget->add_control(
            'clickup_due_date',
            [
                'label' => esc_html__( 'Due Date', 'clickup-addon-elementor' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => esc_html__( 'Use {field_id} for a date field, or use +2 days, +1 week, etc.', 'clickup-addon-elementor' ),
                'placeholder' => esc_html__( '+3 days', 'clickup-addon-elementor' ),
                'condition' => [
                    'clickup_action_type' => 'task',
                    'clickup_list!' => '',
                ],
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $widget->add_control(
            'clickup_priority',
            [
                'label' => esc_html__( 'Priority', 'clickup-addon-elementor' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '' => esc_html__( 'None', 'clickup-addon-elementor' ),
                    '1' => esc_html__( 'Urgent', 'clickup-addon-elementor' ),
                    '2' => esc_html__( 'High', 'clickup-addon-elementor' ),
                    '3' => esc_html__( 'Normal', 'clickup-addon-elementor' ),
                    '4' => esc_html__( 'Low', 'clickup-addon-elementor' ),
                ],
                'default' => '',
                'condition' => [
                    'clickup_action_type' => 'task',
                    'clickup_list!' => '',
                ],
            ]
        );

        $widget->add_control(
            'clickup_assignees',
            [
                'label' => esc_html__( 'Assignees', 'clickup-addon-elementor' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => esc_html__( 'Enter usernames or email addresses, separated by commas', 'clickup-addon-elementor' ),
                'placeholder' => esc_html__( 'username1, username2, email@example.com', 'clickup-addon-elementor' ),
                'condition' => [
                    'clickup_action_type' => 'task',
                    'clickup_list!' => '',
                ],
                'dynamic' => [
                    'active' => true,
                ],
            ]
        );

        $widget->add_control(
            'clickup_custom_fields',
            [
                'label' => esc_html__( 'Custom Fields Mapping', 'clickup-addon-elementor' ),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'description' => esc_html__( 'Map form fields to custom fields in format: custom_field_id:form_field_id (one per line)', 'clickup-addon-elementor' ),
                'placeholder' => esc_html__( 'custom1234:form_field_1
custom5678:form_field_2', 'clickup-addon-elementor' ),
                'condition' => [
                    'clickup_action_type' => 'task',
                    'clickup_list!' => '',
                ],
            ]
        );

        $widget->end_controls_section();
    }

    /**
     * Get workspace options.
     *
     * @since 1.0.0
     * @return array
     */
    private function get_workspace_options() {
        $api = ClickUp_Elementor_Addon::get_instance()->api;
        $workspaces = $api->get_workspaces();
        
        if ( is_wp_error( $workspaces ) || empty( $workspaces ) ) {
            return [ '' => esc_html__( 'No workspaces found', 'clickup-addon-elementor' ) ];
        }
        
        $options = [ '' => esc_html__( 'Select a Workspace', 'clickup-addon-elementor' ) ];
        
        foreach ( $workspaces as $workspace ) {
            $options[ $workspace['id'] ] = $workspace['name'];
        }
        
        return $options;
    }

    /** 
     * Get space options by workspace.
     *
     * @since 1.0.0
     * @param string $workspace_id Workspace ID.
     * @return array
     */
    private function get_space_options_by_workspace( $workspace_id ) {
        $api = ClickUp_Elementor_Addon::get_instance()->api;
        $spaces = $api->get_spaces( $workspace_id );
        
        if ( is_wp_error( $spaces ) || empty( $spaces ) ) {
            return [ '' => esc_html__( 'No spaces found', 'clickup-addon-elementor' ) ];
        }
        
        $options = [ '' => esc_html__( 'Select a Space', 'clickup-addon-elementor' ) ];
        
        return $spaces;
    }
        


    /**
     * Handle form submission.
     *
     * @since 1.0.0
     * @param \ElementorPro\Modules\Forms\Classes\Form_Record $record Form record.
     * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler Ajax handler.
     * @return void
     */
    public function run( $record, $ajax_handler ) {
        $settings = $record->get( 'form_settings' );
        
        // Extract form settings
        $action_type = $settings['clickup_action_type'] ?? 'task';
        $workspace_id = $settings['clickup_workspace'] ?? '';
        $space_id = $settings['clickup_space'] ?? '';
        
        // Check if required fields are set
        if ( empty( $workspace_id ) || empty( $space_id ) ) {
            $ajax_handler->add_error_message( esc_html__( 'ClickUp Integration Error: Workspace and Space are required.', 'clickup-addon-elementor' ) );
            return;
        }

        $space_options = $this->get_space_options_by_workspace( $workspace_id );
        if ( !empty( $space_options ) ) {
            update_option( 'clickup_elementor_space_options', $space_options );
        }
        
        $api = ClickUp_Elementor_Addon::get_instance()->api;
        
        // Process fields and create task/document
        $submitted_data = $record->get( 'fields' );


        
        try {
            if ( $action_type === 'task' ) {
                $this->create_task( $settings, $submitted_data, $record, $ajax_handler, $api );
            } else {
                $this->create_document( $settings, $submitted_data, $record, $ajax_handler, $api );
            }
        } catch ( \Exception $e ) {
            $ajax_handler->add_error_message( sprintf(
                esc_html__( 'ClickUp Integration Error: %s', 'clickup-addon-elementor' ),
                $e->getMessage()
            ) );
        }
    }

    /**
     * Create a task in ClickUp.
     *
     * @since 1.0.0
     * @param array $settings Form settings.
     * @param array $submitted_data Submitted form data.
     * @param \ElementorPro\Modules\Forms\Classes\Form_Record $record Form record.
     * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler Ajax handler.
     * @param ClickUp_Elementor_API $api ClickUp API instance.
     * @return void
     */
    private function create_task( $settings, $submitted_data, $record, $ajax_handler, $api ) {
        $list_id = $settings['clickup_list'] ?? '';
        
        if ( empty( $list_id ) ) {
            throw new \Exception( esc_html__( 'List ID is required for creating tasks.', 'clickup-addon-elementor' ) );
        }
        
        $name = $this->replace_form_field_shortcodes( $settings['clickup_task_name'] ?? 'New task from Elementor form', $record );
        $description = $this->replace_form_field_shortcodes( $settings['clickup_description'] ?? '{all_fields}', $record );
        
        // Replace {all_fields} with a formatted list of all form fields
        if ( strpos( $description, '{all_fields}' ) !== false ) {
            $all_fields = $this->format_all_fields( $submitted_data );
            $description = str_replace( '{all_fields}', $all_fields, $description );
        }
        
        // Prepare task data
        $task_data = [
            'name' => $name,
            'description' => $description
        ];
        
        // Set status if provided
        if ( ! empty( $settings['clickup_status'] ) ) {
            $task_data['status'] = $settings['clickup_status'];
        }
        
        // Set priority if provided
        if ( ! empty( $settings['clickup_priority'] ) ) {
            $task_data['priority'] = (int) $settings['clickup_priority'];
        }
        
        // Process due date
        if ( ! empty( $settings['clickup_due_date'] ) ) {
            $due_date = $this->parse_due_date( $settings['clickup_due_date'], $record );
            if ( $due_date ) {
                $task_data['due_date'] = $due_date;
            }
        }
        
        // Process assignees
        if ( ! empty( $settings['clickup_assignees'] ) ) {
            $assignees = $this->parse_assignees( $settings['clickup_assignees'], $record );
            if ( ! empty( $assignees ) ) {
                $assignees_ids = [];
                // Get the workspace ID to find users
                $workspace_id = $settings['clickup_workspace'];
                if ( !empty( $workspace_id ) ) {
                    $response = $api->request( "team/{$workspace_id}" );
                    
                    if ( !is_wp_error( $response ) ) {
                        if ( isset( $response['team'] ) && isset( $response['team']['members'] ) ) {
                            foreach ( $response['team']['members'] as $member ) {
                                if ( isset( $member['user'] ) && isset( $member['user']['username'] ) && in_array( $member['user']['username'], $assignees ) ) {
                                    $assignees_ids[] = $member['user']['id'];
                                    break;
                                }
                            }
                        }
                    }
                }

                $task_data['assignees'] = $assignees_ids;
            }
        }
        
        // Process custom fields
        if ( ! empty( $settings['clickup_custom_fields'] ) ) {
            $custom_fields = $this->parse_custom_fields( $settings['clickup_custom_fields'], $record );
            if ( ! empty( $custom_fields ) ) {
                $task_data['custom_fields'] = $custom_fields;
            }
        }
        
        // Create the task
        $response = $api->create_task( $task_data, $list_id );
        
        if ( is_wp_error( $response ) ) {
            throw new \Exception( $response->get_error_message() );
        }
        
        // Success - task created
        $ajax_handler->add_success_message( sprintf(
            esc_html__( 'ClickUp task created successfully! Task ID: %s', 'clickup-addon-elementor' ),
            $response['id'] ?? 'N/A'
        ) );
    }

    /**
     * Create a document in ClickUp.
     *
     * @since 1.0.0
     * @param array $settings Form settings.
     * @param array $submitted_data Submitted form data.
     * @param \ElementorPro\Modules\Forms\Classes\Form_Record $record Form record.
     * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler Ajax handler.
     * @param ClickUp_Elementor_API $api ClickUp API instance.
     * @return void
     */
    private function create_document( $settings, $submitted_data, $record, $ajax_handler, $api ) {
        $workspace_id = $settings['clickup_workspace'] ?? '';
        $space_id = $settings['clickup_space'] ?? '';
        
        $name = $this->replace_form_field_shortcodes( $settings['clickup_document_name'] ?? 'New document from Elementor form', $record );
        $content = $this->replace_form_field_shortcodes( $settings['clickup_description'] ?? '{all_fields}', $record );
        
        // Replace {all_fields} with a formatted list of all form fields
        if ( strpos( $content, '{all_fields}' ) !== false ) {
            $all_fields = $this->format_all_fields( $submitted_data );
            $content = str_replace( '{all_fields}', $all_fields, $content );
        }
        
        // Prepare document data
        $doc_data = [
            'name' => $name,
            'content' => $content
        ];
        
        // Create the document
        $response = $api->create_document( $doc_data, $workspace_id, $space_id );
        
        if ( is_wp_error( $response ) ) {
            throw new \Exception( $response->get_error_message() );
        }
        
        // Success - document created
        $ajax_handler->add_success_message( sprintf(
            esc_html__( 'ClickUp document created successfully! Document ID: %s', 'clickup-addon-elementor' ),
            $response['id'] ?? 'N/A'
        ) );
    }

    /**
     * Replace form field shortcodes in a string.
     *
     * @since 1.0.0
     * @param string $string String containing shortcodes.
     * @param \ElementorPro\Modules\Forms\Classes\Form_Record $record Form record.
     * @return string
     */
    private function replace_form_field_shortcodes( $string, $record ) {
        $fields = $record->get( 'fields' );
        $form_settings = $record->get( 'form_settings' );
        
        $string = str_replace( '{form_name}', $form_settings['form_name'] ?? 'Elementor Form', $string );
        
        preg_match_all( '/{([a-zA-Z0-9_-]+)}/', $string, $matches );
        
        if ( empty( $matches[1] ) ) {
            return $string;
        }
        
        foreach ( $matches[1] as $field_id ) {
            if ( $field_id === 'all_fields' ) {
                continue; // This is handled separately
            }
            
            $field_value = isset( $fields[ $field_id ] ) ? $fields[ $field_id ]['value'] : '';
            $string = str_replace( '{' . $field_id . '}', $field_value, $string );
        }
        
        return $string;
    }

    /**
     * Format all fields for display.
     *
     * @since 1.0.0
     * @param array $fields Form fields.
     * @return string
     */
    private function format_all_fields( $fields ) {
        $output = '';
        
        foreach ( $fields as $id => $field ) {
            $label = $field['title'] ?? $id;
            $value = $field['value'] ?? '';
            
            $output .= "**{$label}**: {$value}\n";
        }
        
        return $output;
    }

    /**
     * Parse due date from string.
     *
     * @since 1.0.0
     * @param string $due_date_string Due date string.
     * @param \ElementorPro\Modules\Forms\Classes\Form_Record $record Form record.
     * @return int|null Timestamp in milliseconds or null.
     */
    private function parse_due_date( $due_date_string, $record ) {
        // Check if it's a field reference
        if ( preg_match( '/{([a-zA-Z0-9_-]+)}/', $due_date_string, $matches ) ) {
            $field_id = $matches[1];
            $fields = $record->get( 'fields' );
            
            if ( isset( $fields[ $field_id ] ) ) {
                $date_value = $fields[ $field_id ]['value'];
                $timestamp = strtotime( $date_value );
                
                if ( $timestamp ) {
                    // ClickUp needs milliseconds
                    return $timestamp * 1000;
                }
            }
            
            return null;
        }
        
        // Check if it's a relative date (e.g., +3 days)
        if ( strpos( $due_date_string, '+' ) === 0 ) {
            $timestamp = strtotime( $due_date_string );
            
            if ( $timestamp ) {
                // ClickUp needs milliseconds
                return $timestamp * 1000;
            }
        }
        
        // Direct date format
        $timestamp = strtotime( $due_date_string );
        
        if ( $timestamp ) {
            // ClickUp needs milliseconds
            return $timestamp * 1000;
        }
        
        return null;
    }

    /**
     * Parse assignees string.
     *
     * @since 1.0.0
     * @param string $assignees_string Assignees string.
     * @param \ElementorPro\Modules\Forms\Classes\Form_Record $record Form record.
     * @return array
     */
    private function parse_assignees( $assignees_string, $record ) {
        // Replace field references
        $assignees_string = $this->replace_form_field_shortcodes( $assignees_string, $record );
        
        // Split by commas and trim
        $assignees = array_map( 'trim', explode( ',', $assignees_string ) );
        
        // Filter out empty values
        return array_filter( $assignees );
    }

    /**
     * Parse custom fields mapping.
     *
     * @since 1.0.0
     * @param string $custom_fields_string Custom fields mapping string.
     * @param \ElementorPro\Modules\Forms\Classes\Form_Record $record Form record.
     * @return array
     */
    private function parse_custom_fields( $custom_fields_string, $record ) {
        $lines = preg_split( '/\r\n|\r|\n/', $custom_fields_string );
        $custom_fields = [];
        $fields = $record->get( 'fields' );
        
        foreach ( $lines as $line ) {
            $line = trim( $line );
            
            if ( empty( $line ) ) {
                continue;
            }
            
            $parts = explode( ':', $line, 2 );
            
            if ( count( $parts ) !== 2 ) {
                continue;
            }
            
            $custom_field_id = trim( $parts[0] );
            $form_field_id = trim( $parts[1] );
            
            if ( isset( $fields[ $form_field_id ] ) ) {
                $custom_fields[] = [
                    'id' => $custom_field_id,
                    'value' => $fields[ $form_field_id ]['value']
                ];
            }
        }
        
        return $custom_fields;
    }

    /**
     * Export form settings.
     *
     * @since 1.0.0
     * @param array $element Form settings.
     * @return array
     */
    public function on_export( $element ) {
        return $element;
    }
} 