<?php
/**
 * Admin settings page for ClickUp Elementor Addon
 *
 * @package clickup-addon-elementor
 * @since 1.0.0
 */

// Prevent direct access
defined( 'ABSPATH' ) || die( 'Access denied.' );

// Handle form submission
if ( isset( $_POST['clickup_elementor_api_key'] ) && isset( $_POST['clickup_elementor_settings_nonce'] ) ) {
    if ( check_admin_referer( 'clickup_elementor_settings', 'clickup_elementor_settings_nonce' ) ) {
        $api_key = sanitize_text_field( $_POST['clickup_elementor_api_key'] );
        update_option( 'clickup_elementor_api_key', $api_key );
        
        // Show success message
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e( 'API key saved successfully!', 'clickup-addon-elementor' ); ?></p>
        </div>
        <?php
    }
}

// Get current API key
$api_key = get_option( 'clickup_elementor_api_key', '' );
?>

<div class="wrap clickup-elementor-settings">
    <h1><?php esc_html_e( 'ClickUp Integration Settings', 'clickup-addon-elementor' ); ?></h1>
    
    <div class="clickup-elementor-settings-content">
        <div class="clickup-elementor-settings-main">
            <form method="post" action="">
                <?php wp_nonce_field( 'clickup_elementor_settings', 'clickup_elementor_settings_nonce' ); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="clickup_elementor_api_key"><?php esc_html_e( 'ClickUp API Key', 'clickup-addon-elementor' ); ?></label>
                        </th>
                        <td>
                            <input type="password" 
                                   name="clickup_elementor_api_key" 
                                   id="clickup_elementor_api_key" 
                                   value="<?php echo esc_attr( $api_key ); ?>" 
                                   class="regular-text"
                                   autocomplete="off"
                            />
                            <p class="description">
                                <?php esc_html_e( 'Enter your ClickUp API key. You can find this in your ClickUp account settings.', 'clickup-addon-elementor' ); ?>
                            </p>
                            <p>
                                <button type="button" 
                                        id="clickup_elementor_verify_api_key" 
                                        class="button button-secondary"
                                        <?php echo empty( $api_key ) ? 'disabled' : ''; ?>
                                >
                                    <?php esc_html_e( 'Verify API Key', 'clickup-addon-elementor' ); ?>
                                </button>
                                <span id="clickup_elementor_verify_result"></span>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" 
                           name="submit" 
                           id="submit" 
                           class="button button-primary" 
                           value="<?php esc_attr_e( 'Save Changes', 'clickup-addon-elementor' ); ?>"
                    />
                </p>
            </form>
        </div>
        
        <div class="clickup-elementor-settings-sidebar">
            <div class="clickup-elementor-box">
                <h3><?php esc_html_e( 'Getting Your ClickUp API Key', 'clickup-addon-elementor' ); ?></h3>
                <ol>
                    <li><?php esc_html_e( 'Login to your ClickUp account', 'clickup-addon-elementor' ); ?></li>
                    <li><?php esc_html_e( 'Go to Settings → Apps', 'clickup-addon-elementor' ); ?></li>
                    <li><?php esc_html_e( 'Under the "API Token" section, click "Generate"', 'clickup-addon-elementor' ); ?></li>
                    <li><?php esc_html_e( 'Copy the generated token', 'clickup-addon-elementor' ); ?></li>
                    <li><?php esc_html_e( 'Paste it into the field on this page', 'clickup-addon-elementor' ); ?></li>
                </ol>
                <p>
                    <a href="https://docs.clickup.com/en/articles/1367130-getting-started-with-the-clickup-api" target="_blank" class="button button-secondary">
                        <?php esc_html_e( 'ClickUp API Documentation', 'clickup-addon-elementor' ); ?>
                    </a>
                </p>
            </div>
            
            <div class="clickup-elementor-box">
                <h3><?php esc_html_e( 'Using the Integration', 'clickup-addon-elementor' ); ?></h3>
                <p><?php esc_html_e( 'Once your API key is configured, you can use the ClickUp action in your Elementor Forms:', 'clickup-addon-elementor' ); ?></p>
                <ol>
                    <li><?php esc_html_e( 'Edit an Elementor form', 'clickup-addon-elementor' ); ?></li>
                    <li><?php esc_html_e( 'Go to the "Actions After Submit" section', 'clickup-addon-elementor' ); ?></li>
                    <li><?php esc_html_e( 'Add the "ClickUp" action', 'clickup-addon-elementor' ); ?></li>
                    <li><?php esc_html_e( 'Configure the ClickUp settings', 'clickup-addon-elementor' ); ?></li>
                </ol>
                <p><?php esc_html_e( 'You can create tasks or documents in ClickUp directly from your form submissions!', 'clickup-addon-elementor' ); ?></p>
            </div>
            
            <div class="clickup-elementor-box">
                <h3><?php esc_html_e( 'Need Help?', 'clickup-addon-elementor' ); ?></h3>
                <p><?php esc_html_e( 'For support, questions, or feature requests, please contact us:', 'clickup-addon-elementor' ); ?></p>
                <p><a href="https://elementorexperts.com/contact/" target="_blank"><?php esc_html_e( 'Contact Support', 'clickup-addon-elementor' ); ?></a></p>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Enable/disable verify button based on API key field
    $('#clickup_elementor_api_key').on('input', function() {
        $('#clickup_elementor_verify_api_key').prop('disabled', $(this).val().trim() === '');
    });
    
    // Verify API key
    $('#clickup_elementor_verify_api_key').on('click', function() {
        const apiKey = $('#clickup_elementor_api_key').val().trim();
        const $result = $('#clickup_elementor_verify_result');
        
        if (!apiKey) {
            $result.html('<span style="color: red;"><?php esc_html_e( 'Please enter an API key', 'clickup-addon-elementor' ); ?></span>');
            return;
        }
        
        $result.html('<span style="color: blue;"><?php esc_html_e( 'Verifying...', 'clickup-addon-elementor' ); ?></span>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'clickup_ele_verify_api_key',
                api_key: apiKey,
                nonce: '<?php echo esc_js( wp_create_nonce( 'clickup_elementor_nonce' ) ); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $result.html('<span style="color: green;"><?php esc_html_e( 'API key verified successfully!', 'clickup-addon-elementor' ); ?></span>');
                } else {
                    $result.html('<span style="color: red;">' + (response.data.message || '<?php esc_html_e( 'Verification failed', 'clickup-addon-elementor' ); ?>') + '</span>');
                }
            },
            error: function() {
                $result.html('<span style="color: red;"><?php esc_html_e( 'Request failed. Please try again.', 'clickup-addon-elementor' ); ?></span>');
            }
        });
    });
});
</script>

<style>
.clickup-elementor-settings-content {
    display: flex;
    flex-wrap: wrap;
    margin-top: 20px;
}

.clickup-elementor-settings-main {
    flex: 2;
    min-width: 500px;
    margin-right: 20px;
}

.clickup-elementor-settings-sidebar {
    flex: 1;
    min-width: 300px;
}

.clickup-elementor-box {
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
    padding: 15px;
    margin-bottom: 20px;
}

.clickup-elementor-box h3 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

#clickup_elementor_verify_result {
    margin-left: 10px;
    font-weight: 500;
}

@media screen and (max-width: 782px) {
    .clickup-elementor-settings-content {
        flex-direction: column;
    }
    
    .clickup-elementor-settings-main,
    .clickup-elementor-settings-sidebar {
        min-width: 100%;
        margin-right: 0;
    }
}
</style> 