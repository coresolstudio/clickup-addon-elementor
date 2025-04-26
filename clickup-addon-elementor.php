<?php
/**
 * Plugin Name: ClickUp Addon - Elementor
 * Plugin URI: https://elementorexperts.com
 * Description: Integrates Elementor Forms with ClickUp, allowing you to create tasks, documents, and manage projects directly from your form submissions.
 * Version: 1.0.0
 * Author: Hassan Ali | Elementor Experts
 * Author URI: https://elementorexperts.com
 * License: GPL-2.0+
 * Text Domain: clickup-addon-elementor
 * Domain Path: /languages
 * Elementor tested up to: 3.17.0
 * Elementor Pro tested up to: 3.17.0
 *
 * ------------------------------------------------------------------------
 * Copyright 2024 Hassan Ali | Elementor Experts
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

// Prevent direct access to this file
defined( 'ABSPATH' ) || die( 'Access denied.' );

// Define plugin constants
define( 'CLICKUP_ELE_VERSION', '1.0.0' );
define( 'CLICKUP_ELE_FILE', __FILE__ );
define( 'CLICKUP_ELE_PATH', plugin_dir_path( __FILE__ ) );
define( 'CLICKUP_ELE_URL', plugin_dir_url( __FILE__ ) );
define( 'CLICKUP_ELE_ASSETS', CLICKUP_ELE_URL . 'assets/' );

/**
 * Main plugin class
 */
class ClickUp_Elementor_Addon {

    /**
     * Instance of this class.
     *
     * @since 1.0.0
     * @var object
     */
    private static $instance;

    /**
     * ClickUp API instance.
     *
     * @since 1.0.0
     * @var ClickUp_Elementor_API
     */
    public $api;

    /**
     * Get the singleton instance of this class.
     *
     * @since 1.0.0
     * @return ClickUp_Elementor_Addon
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    private function __construct() {
        
        // Check if Elementor is installed and activated
        add_action( 'plugins_loaded', array( $this, 'init' ) );
    }

    /**
     * Initialize the plugin.
     *
     * @since 1.0.0
     * @return void
     */
    public function init() {
        // Check if Elementor is installed and activated
        if ( ! did_action( 'elementor/loaded' ) ) {
            add_action( 'admin_notices', array( $this, 'admin_notice_missing_elementor' ) );
            return;
        }
        // Check if Elementor Pro is installed and activated
        if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) ) {
            add_action( 'admin_notices', array( $this, 'admin_notice_missing_elementor_pro' ) );
            return;
        }

        // Load required files
        $this->includes();

        // Initialize the API
        $this->api = new ClickUp_Elementor_API();

        // Register form action
        add_action( 'elementor_pro/forms/actions/register', array( $this, 'register_form_action' ) );

        // Register admin menu for API settings
        add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 99 );

        // Register scripts and styles
        add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_assets' ) );
        add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'register_admin_assets' ) );
        add_action( 'elementor/frontend/after_register_scripts', array( $this, 'register_frontend_assets' ) );

        // Register AJAX handlers
        add_action( 'wp_ajax_clickup_ele_verify_api_key', array( $this, 'ajax_verify_api_key' ) );
        add_action( 'wp_ajax_clickup_ele_get_workspaces', array( $this, 'ajax_get_workspaces' ) );
        add_action( 'wp_ajax_clickup_ele_get_spaces', array( $this, 'ajax_get_spaces' ) );
        add_action( 'wp_ajax_clickup_ele_get_lists', array( $this, 'ajax_get_lists' ) );
        add_action( 'wp_ajax_clickup_ele_get_statuses', array( $this, 'ajax_get_statuses' ) );
    }

    /**
     * Include required files.
     *
     * @since 1.0.0
     * @return void
     */
    private function includes() {
        require_once CLICKUP_ELE_PATH . 'includes/class-clickup-elementor-api.php';
    }

    /**
     * Register the form action.
     *
     * @since 1.0.0
     * @param \ElementorPro\Modules\Forms\Registrars\Form_Actions_Registrar $actions_registrar The form actions registrar.
     * @return void
     */
    public function register_form_action( $actions_registrar ) {
        require_once CLICKUP_ELE_PATH . 'includes/class-clickup-elementor-action.php';
        $actions_registrar->register( new \ClickUp_Elementor_Action() );
    }

    /**
     * Register admin menu for API settings.
     *
     * @since 1.0.0
     * @return void
     */
    public function register_admin_menu() {
        add_submenu_page(
            'elementor',
            __( 'ClickUp Integration', 'clickup-addon-elementor' ),
            __( 'ClickUp Integration', 'clickup-addon-elementor' ),
            'manage_options',
            'clickup-elementor-settings',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Render settings page.
     *
     * @since 1.0.0
     * @return void
     */
    public function render_settings_page() {
        require_once CLICKUP_ELE_PATH . 'includes/admin-settings.php';
    }

    /**
     * Register admin assets.
     *
     * @since 1.0.0
     * @return void
     */
    public function register_admin_assets() {
        // Get current screen
        $screen = get_current_screen();
        
        // Check if we're in Elementor editor or on our settings page
        $is_elementor = (
            $screen && (
                $screen->id === 'elementor_page_clickup-elementor-settings' ||
                $screen->id === 'elementor' ||
                strpos($screen->id, 'elementor') !== false ||
                isset($_GET['action']) && $_GET['action'] === 'elementor'
            )
        );
        
        // Also load when in Elementor preview mode
        if (isset($_GET['elementor-preview'])) {
            $is_elementor = true;
        }
        
        if ($is_elementor) {
            wp_enqueue_style(
                'clickup-elementor-admin',
                CLICKUP_ELE_ASSETS . 'css/admin.css',
                array(),
                CLICKUP_ELE_VERSION
            );

            wp_enqueue_script(
                'clickup-elementor-admin',
                CLICKUP_ELE_ASSETS . 'js/admin.js',
                array('jquery'),
                CLICKUP_ELE_VERSION,
                true
            );

            wp_localize_script(
                'clickup-elementor-admin',
                'clickupElementor',
                array(
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'nonce'   => wp_create_nonce('clickup_elementor_nonce'),
                    'i18n'    => array(
                        'verifying'   => __('Verifying...', 'clickup-addon-elementor'),
                        'verified'    => __('Verified!', 'clickup-addon-elementor'),
                        'failed'      => __('Verification failed', 'clickup-addon-elementor'),
                        'loading'     => __('Loading...', 'clickup-addon-elementor'),
                        'selectSpace' => __('Select a Space', 'clickup-addon-elementor'),
                        'selectList'  => __('Select a List', 'clickup-addon-elementor'),
                        'selectStatus' => __('Select a Status', 'clickup-addon-elementor'),
                    )
                )
            );
        }
    }

    /**
     * Register frontend assets.
     *
     * @since 1.0.0
     * @return void
     */
    public function register_frontend_assets() {
        if ( \Elementor\Plugin::instance()->preview->is_preview_mode() ) {
            wp_enqueue_script(
                'clickup-elementor-frontend',
                CLICKUP_ELE_ASSETS . 'js/frontend.js',
                array( 'jquery', 'elementor-frontend' ),
                CLICKUP_ELE_VERSION,
                true
            );
        }
    }

    /**
     * AJAX: Verify API key.
     *
     * @since 1.0.0
     * @return void
     */
    public function ajax_verify_api_key() {
        // Verify nonce
        if ( ! check_ajax_referer( 'clickup_elementor_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'clickup-addon-elementor' ) ) );
        }

        // Verify user permissions
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action', 'clickup-addon-elementor' ) ) );
        }

        $api_key = isset( $_POST['api_key'] ) ? sanitize_text_field( $_POST['api_key'] ) : '';
        
        if ( empty( $api_key ) ) {
            wp_send_json_error( array( 'message' => __( 'API key is required', 'clickup-addon-elementor' ) ) );
        }

        // Verify the API key with ClickUp
        $result = $this->api->validate_token( $api_key );
        
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }

        // Save the API key
        update_option( 'clickup_elementor_api_key', $api_key );
        
        wp_send_json_success( array( 'message' => __( 'API key verified and saved', 'clickup-addon-elementor' ) ) );
    }

    /**
     * AJAX: Get workspaces.
     *
     * @since 1.0.0
     * @return void
     */
    public function ajax_get_workspaces() {
        // Verify nonce
        if ( ! check_ajax_referer( 'clickup_elementor_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'clickup-addon-elementor' ) ) );
        }

        $workspaces = $this->api->get_workspaces();
        
        if ( is_wp_error( $workspaces ) ) {
            wp_send_json_error( array( 'message' => $workspaces->get_error_message() ) );
        }
        
        wp_send_json_success( $workspaces );
    }

    /**
     * AJAX: Get spaces.
     *
     * @since 1.0.0
     * @return void
     */
    public function ajax_get_spaces() {
        // Verify nonce
        if ( ! check_ajax_referer( 'clickup_elementor_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'clickup-addon-elementor' ) ) );
        }

        $workspace_id = isset( $_POST['workspace_id'] ) ? sanitize_text_field( $_POST['workspace_id'] ) : '';
        
        if ( empty( $workspace_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Workspace ID is required', 'clickup-addon-elementor' ) ) );
        }

        $spaces = $this->api->get_spaces( $workspace_id );
        
        if ( is_wp_error( $spaces ) ) {
            wp_send_json_error( array( 'message' => $spaces->get_error_message() ) );
        }
        
        wp_send_json_success( $spaces );
    }

    /**
     * AJAX: Get lists.
     *
     * @since 1.0.0
     * @return void
     */
    public function ajax_get_lists() {
        // Verify nonce
        if ( ! check_ajax_referer( 'clickup_elementor_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'clickup-addon-elementor' ) ) );
        }

        $space_id = isset( $_POST['space_id'] ) ? sanitize_text_field( $_POST['space_id'] ) : '';
        
        if ( empty( $space_id ) ) {
            wp_send_json_error( array( 'message' => __( 'Space ID is required', 'clickup-addon-elementor' ) ) );
        }

        $lists = $this->api->get_lists_from_space( $space_id );
        
        if ( is_wp_error( $lists ) ) {
            wp_send_json_error( array( 'message' => $lists->get_error_message() ) );
        }
        
        wp_send_json_success( $lists );
    }

    /**
     * AJAX: Get statuses.
     *
     * @since 1.0.0
     * @return void
     */
    public function ajax_get_statuses() {
        // Verify nonce
        if ( ! check_ajax_referer( 'clickup_elementor_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => __( 'Security check failed', 'clickup-addon-elementor' ) ) );
        }

        $list_id = isset( $_POST['list_id'] ) ? sanitize_text_field( $_POST['list_id'] ) : '';
        
        if ( empty( $list_id ) ) {
            wp_send_json_error( array( 'message' => __( 'List ID is required', 'clickup-addon-elementor' ) ) );
        }

        $statuses = $this->api->get_list_statuses( $list_id );
        
        if ( is_wp_error( $statuses ) ) {
            wp_send_json_error( array( 'message' => $statuses->get_error_message() ) );
        }
        
        wp_send_json_success( $statuses );
    }

    /**
     * Admin notice for missing Elementor.
     *
     * @since 1.0.0
     * @return void
     */
    public function admin_notice_missing_elementor() {
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }

        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor */
            esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'clickup-addon-elementor' ),
            '<strong>' . esc_html__( 'ClickUp Addon - Elementor', 'clickup-addon-elementor' ) . '</strong>',
            '<strong>' . esc_html__( 'Elementor', 'clickup-addon-elementor' ) . '</strong>'
        );

        printf( '<div class="notice notice-error"><p>%1$s</p></div>', $message );
    }

    /**
     * Admin notice for missing Elementor Pro.
     *
     * @since 1.0.0
     * @return void
     */
    public function admin_notice_missing_elementor_pro() {
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }

        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor Pro */
            esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'clickup-addon-elementor' ),
            '<strong>' . esc_html__( 'ClickUp Addon - Elementor', 'clickup-addon-elementor' ) . '</strong>',
            '<strong>' . esc_html__( 'Elementor Pro', 'clickup-addon-elementor' ) . '</strong>'
        );

        printf( '<div class="notice notice-error"><p>%1$s</p></div>', $message );
    }
}

// Initialize the plugin
ClickUp_Elementor_Addon::get_instance(); 