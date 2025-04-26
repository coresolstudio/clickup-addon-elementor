<?php
/**
 * ClickUp API Class for Elementor
 *
 * Handles all API interactions with the ClickUp API
 *
 * @package clickup-addon-elementor
 * @since 1.0.0
 */

// Prevent direct access
defined( 'ABSPATH' ) || die( 'Access denied.' );

/**
 * ClickUp_Elementor_API class.
 *
 * @since 1.0.0
 */
class ClickUp_Elementor_API {

    /**
     * Base URL for the ClickUp API.
     *
     * @since 1.0.0
     * @var string
     */
    private $api_base_url = 'https://api.clickup.com/api/';

    /**
     * API token instance variable.
     *
     * @since 1.0.0
     * @var string
     */
    private $api_token = null;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        // Nothing to do here
    }

    /**
     * Set the API token.
     *
     * @since 1.0.0
     * @param string $token API token.
     * @return void
     */
    public function set_api_token( $token ) {
        $this->api_token = $token;
    }

    /**
     * Get the stored API token.
     *
     * @since 1.0.0
     * @return string|null
     */
    public function get_api_token() {
        if ( $this->api_token !== null ) {
            return $this->api_token;
        }

        $token = get_option( 'clickup_elementor_api_key' );
        $this->api_token = $token;
        
        return $token;
    }

    /**
     * Make an authenticated request to the ClickUp API.
     *
     * @since 1.0.0
     * @param string $endpoint The API endpoint to call.
     * @param array  $args     Additional wp_remote_request arguments.
     * @param string $api_version API version to use.
     * @return array|WP_Error The response or WP_Error on failure.
     */
    public function request( $endpoint, $args = array(), $api_version = 'v2' ) {
        $token = $this->get_api_token();
        
        if ( empty( $token ) ) {
            $this->log_debug( __METHOD__ . '(): No API token configured' );
            return new WP_Error( 'no_token', 'No API token configured' );
        }

        // Validate endpoint (basic security check)
        $endpoint = preg_replace( '/[^a-zA-Z0-9\/_-]/', '', $endpoint );
        
        $default_args = array(
            'headers' => array(
                'Authorization' => $token,
                'Content-Type'  => 'application/json'
            ),
            'method'  => 'GET',
            'timeout' => 30
        );

        $args = wp_parse_args( $args, $default_args );
        
        $url = $this->api_base_url . $api_version . '/' . ltrim( $endpoint, '/' );
        
        $response = wp_remote_request( $url, $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $response_body = wp_remote_retrieve_body( $response );
        $data = json_decode( $response_body, true );

        if ( $response_code === 401 ) {
            // Token might be invalid - clear it
            delete_option( 'clickup_elementor_api_key' );
            return new WP_Error( 'invalid_token', 'API token is invalid' );
        }

        if ( $response_code < 200 || $response_code >= 300 ) {
            $error = isset( $data['err'] ) ? $data['err'] : 'Unknown error';
            return new WP_Error( 'api_error', $error );
        }

        return $data;
    }

    /**
     * Get workspaces from cache or API.
     *
     * @since 1.0.0
     * @return array|WP_Error Array of workspace choices or WP_Error.
     */
    public function get_workspaces() {
        $token = $this->get_api_token();
        
        if ( empty( $token ) ) {
            return new WP_Error( 'no_token', 'No API token configured' );
        }

        // Try to get cached workspaces first
        $cached = get_transient( 'clickup_elementor_workspaces_' . md5( $token ) );
        if ( $cached !== false ) {
            return $cached;
        }

        // Get fresh data from API
        $response = $this->request( 'team' );
        
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( ! isset( $response['teams'] ) || ! is_array( $response['teams'] ) ) {
            return new WP_Error( 'invalid_response', 'Invalid API response' );
        }

        $workspaces = array();
        
        foreach ( $response['teams'] as $team ) {
            $workspaces[] = array(
                'id'   => $team['id'],
                'name' => $team['name']
            );
        }

        // Cache the results for 1 hour
        set_transient( 'clickup_elementor_workspaces_' . md5( $token ), $workspaces, HOUR_IN_SECONDS );
        
        return $workspaces;
    }

    /**
     * Get spaces from a workspace.
     *
     * @since 1.0.0
     * @param string $workspace_id Workspace ID.
     * @return array|WP_Error Array of space choices or WP_Error.
     */
    public function get_spaces( $workspace_id ) {
        if ( empty( $workspace_id ) ) {
            return new WP_Error( 'missing_workspace_id', 'Workspace ID is required' );
        }

        // Try to get cached spaces first
        $cached = get_transient( 'clickup_elementor_spaces_' . $workspace_id );
        if ( $cached !== false ) {
            return $cached;
        }

        // Get fresh data from API
        $response = $this->request( "team/{$workspace_id}/space" );
        
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( ! isset( $response['spaces'] ) || ! is_array( $response['spaces'] ) ) {
            return new WP_Error( 'invalid_response', 'Invalid API response' );
        }

        $spaces = array();
        
        foreach ( $response['spaces'] as $space ) {
            $spaces[] = array(
                'id'   => $space['id'],
                'name' => $space['name']
            );
        }

        // Cache the results for 1 hour
        set_transient( 'clickup_elementor_spaces_' . $workspace_id, $spaces, HOUR_IN_SECONDS );
        
        return $spaces;
    }

    /**
     * Get lists from a space.
     *
     * @since 1.0.0
     * @param string $space_id Space ID.
     * @return array|WP_Error Array of list choices or WP_Error.
     */
    public function get_lists_from_space( $space_id ) {
        if ( empty( $space_id ) ) {
            return new WP_Error( 'missing_space_id', 'Space ID is required' );
        }

        // Try to get cached lists first
        $cached = get_transient( 'clickup_elementor_lists_' . $space_id );
        // if ( $cached !== false ) {
        //     return $cached;
        // }

        // Get fresh data from API
        $response = $this->request( "space/{$space_id}/list" );
        
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( ! isset( $response['lists'] ) || ! is_array( $response['lists'] ) ) {
            return new WP_Error( 'invalid_response', 'Invalid API response' );
        }

        $lists = array();
        
        // Add lists from the space
        if ( isset( $response['lists'] ) && is_array( $response['lists'] ) ) {
            foreach ( $response['lists'] as $list ) {
                $lists[] = array(
                    'value'    => esc_attr( $list['id'] ),
                    'label'    => esc_html( $list['name'] ),
                    'statuses' => isset( $list['statuses'] ) ? $list['statuses'] : array()
                );
            }
        }
        
        // Get folders and their lists
        $folders_response = $this->request( "space/{$space_id}/folder" );
        if ( ! is_wp_error( $folders_response ) && isset( $folders_response['folders'] ) ) {
            foreach ( $folders_response['folders'] as $folder ) {
                if ( isset( $folder['lists'] ) && is_array( $folder['lists'] ) ) {
                    foreach ( $folder['lists'] as $list ) {
                        $lists[] = array(
                            'value'    => esc_attr( $list['id'] ),
                            'label'    => esc_html( $folder['name'] . ' → ' . $list['name'] ),
                            'statuses' => isset( $list['statuses'] ) ? $list['statuses'] : array()
                        );
                    }
                }
            }
        }

        // Cache the results for 1 hour
        set_transient( 'clickup_elementor_lists_' . $space_id, $lists, HOUR_IN_SECONDS );
        
        return $lists;
    }

    /**
     * Get statuses from a list.
     *
     * @since 1.0.0
     * @param string $list_id List ID.
     * @return array|WP_Error Array of status choices or WP_Error.
     */
    public function get_list_statuses( $list_id ) {
        if ( empty( $list_id ) ) {
            return new WP_Error( 'missing_list_id', 'List ID is required' );
        }

        // Try to get cached statuses first
        $cached = get_transient( 'clickup_elementor_statuses_' . $list_id );
        if ( $cached !== false ) {
            return $cached;
        }

        // Get fresh data from API
        $response = $this->request( "list/{$list_id}" );
        
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        if ( ! isset( $response['statuses'] ) || ! is_array( $response['statuses'] ) ) {
            return new WP_Error( 'invalid_response', 'Invalid API response' );
        }

        $statuses = array();
        
        foreach ( $response['statuses'] as $status ) {
            $statuses[] = array(
                'id'    => $status['status'],
                'name'  => $status['status'],
                'color' => $status['color']
            );
        }

        // Cache the results for 1 hour
        set_transient( 'clickup_elementor_statuses_' . $list_id, $statuses, HOUR_IN_SECONDS );
        
        return $statuses;
    }

    /**
     * Validate an API token.
     *
     * @since 1.0.0
     * @param string $token API token to validate.
     * @return bool|WP_Error True if valid, WP_Error otherwise.
     */
    public function validate_token( $token ) {
        if ( empty( $token ) ) {
            return new WP_Error( 'empty_token', 'API token is empty' );
        }

        // Set the token temporarily
        $this->set_api_token( $token );
        
        // Try to get workspaces to validate the token
        $response = $this->request( 'user' );
        
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        // If we got here, token is valid
        return true;
    }

    /**
     * Create a task in ClickUp.
     *
     * @since 1.0.0
     * @param array  $task_data Task data.
     * @param string $list_id   List ID.
     * @return array|WP_Error The created task or WP_Error.
     */
    public function create_task( $task_data, $list_id ) {
        if ( empty( $list_id ) ) {
            return new WP_Error( 'missing_list_id', 'List ID is required' );
        }

        if ( empty( $task_data['name'] ) ) {
            return new WP_Error( 'missing_name', 'Task name is required' );
        }

        $args = array(
            'method' => 'POST',
            'body'   => wp_json_encode( $task_data )
        );

        return $this->request( "list/{$list_id}/task", $args );
    }

    /**
     * Create a document in ClickUp.
     *
     * @since 1.0.0
     * @param array  $doc_data     Document data.
     * @param string $workspace_id Workspace ID.
     * @param string $space_id     Space ID.
     * @return array|WP_Error The created document or WP_Error.
     */
    public function create_document( $doc_data, $workspace_id, $space_id ) {
        if ( empty( $workspace_id ) ) {
            return new WP_Error( 'missing_workspace_id', 'Workspace ID is required' );
        }

        if ( empty( $space_id ) ) {
            return new WP_Error( 'missing_space_id', 'Space ID is required' );
        }

        if ( empty( $doc_data['name'] ) ) {
            return new WP_Error( 'missing_name', 'Document name is required' );
        }

        // Sanitize inputs
        $workspace_id = sanitize_text_field( $workspace_id );
        $space_id = sanitize_text_field( $space_id );
        
        // Sanitize document data
        $sanitized_data = array(
            'name'        => isset( $doc_data['name'] ) ? sanitize_text_field( $doc_data['name'] ) : '',
            'create_page' => false,
            'parent'      => array(
                'type' => 4,  // Space type
                'id'   => (string) $space_id
            )
        );

        $args = array(
            'method' => 'POST',
            'body'   => wp_json_encode( $sanitized_data ),
            'headers' => array(
                'Authorization' => $this->get_api_token(),
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json'
            )
        );

        return $this->request( "workspaces/{$workspace_id}/docs", $args, 'v3' );
    }

    /**
     * Log debug message to error log.
     *
     * @since 1.0.0
     * @param string $message Message to log.
     * @return void
     */
    private function log_debug( $message ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'ClickUp Elementor: ' . $message );
        }
    }
} 