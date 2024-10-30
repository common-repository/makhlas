<?php
/*
Plugin Name: Makhlas
Plugin URI: http://wordpress.org/plugins/makhlas/
Description: URL shortening for WordPress.
Version: 1.0.0
Author: Ali Farmad
Author URI: http://farmad.me/
Text Domain: makhlas
Domain Path: /languages
*/

//  Define constant(s)
define('MAKHLAS_META', '_makhlas');

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

if (!class_exists('Makhlas_Plugin')) {
    class Makhlas_Plugin
    {
        private $short_link_created = false;
        private $api_base_url = 'http://api.makhlas.com/v1/';

        /**
        * Initialize Makhlas options upon activation.
        */
        public function __construct() {
            //  Add Makhlas option defaults
            add_option('makhlas_default_domain', 'urly.ir');

            add_action('post_updated', array(&$this, 'makhlas_check_post_changes'), 10, 3);

            add_filter('manage_posts_columns' , array(&$this, 'makhlas_posts_table_column'));
            add_action( 'manage_posts_custom_column', array(&$this, 'makhlas_posts_custom_columns'), 10, 2);

            add_action('admin_menu', array(&$this, 'makhlas_admin_menu_link'));

            add_action('plugins_loaded', array(&$this, 'makhlas_load_plugin_textdomain'));

            add_filter('plugin_action_links_' . plugin_basename( __FILE__ ), array(&$this, 'makhlas_plugin_page_add_settings_link'));

            remove_action('wp_head', 'wp_shortlink_wp_head');
            add_action('wp_head', array(&$this, 'makhlas_shortlink_meta_tag'));

            add_action('wp_ajax_makhlas_convert_all_posts_link', array(&$this, 'makhlas_convert_all_posts_link'));
        }

        public function makhlas_admin_menu_link() {
            add_menu_page(
                __('Makhlas', 'makhlas'),
                __('Makhlas', 'makhlas'),
                'manage_options',
                plugin_dir_path(__FILE__) . 'admin/view.php',
                null,
                'dashicons-admin-links',
                82
            );

            // Change first submenu title
            add_submenu_page(
                plugin_dir_path(__FILE__) . 'admin/view.php',
                __('Settings', 'makhlas'),
                __('Settings', 'makhlas'),
                'manage_options',
                plugin_dir_path(__FILE__) . 'admin/view.php',
                null
            );

            add_submenu_page(
                plugin_dir_path(__FILE__) . 'admin/view.php',
                __('Converter', 'makhlas'),
                __('Converter', 'makhlas'),
                'manage_options',
                plugin_dir_path(__FILE__) . 'admin/converter.php',
                null
            );
        }

        // Set Plugin menu links
        public function makhlas_plugin_page_add_settings_link($links) {
            $settings_link = array('<a href="' . admin_url( 'admin.php?page=makhlas/admin/view.php' ) . '">' . __('Settings', 'makhlas') . '</a>');
            return array_merge($settings_link, $links);
        }

        public function makhlas_load_plugin_textdomain() {
            load_plugin_textdomain( 'makhlas', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
        }

        // replcae default shortlink meta tag with makhlas short url
        public function makhlas_shortlink_meta_tag() {
            echo '<link rel="shortlink" href="' . $this->makhlas_get_the_short_url() . '" />';
        }

        /**
        * Get the Makhlas api key.
        *
        * @return string Makhlas api key.
        */
        public function makhlas_get_api_key() {
            //  Return the Makhlas api key
            return get_option( 'makhlas_api_key');
        }

        /**
        * Get the Makhlas request header (add api key in header).
        *
        * @return array request header.
        */
        public function makhlas_get_api_header() {
            //  Return header for api request
            return array(
                'Authorization' => 'Bearer ' . $this->makhlas_get_api_key()
            );
        }

        /**
        * Create post short url
        *
        * @param int $post_id ID of post.
        *
        * @param array $post_before data.
        *
        * @param array $post_after data.
        *
        * @return string Post's short URL.
        */
        public function makhlas_check_post_changes($post_id, $post_after, $post_before){
            if ($post_after->post_status == 'publish' && $post_before->post_name !== $post_after->post_name) {        
                $post_short_url_key = get_post_meta($post_id, '_makhlas_key', false);
                if (empty($post_short_url_key)) {
                    $resault = $this->makhlas_shortener_url(get_permalink($post_id));
                    $this->makhlas_update_post_meta($post_id, $resault);
                } else {
                   $this->makhlas_update_short_url(get_permalink($post_id), $post_short_url_key[0]);
                }
            }
        }

        /**
        * Check is post url changed after update.
        *
        * @param int $post_id ID of post.
        *
        * @return string Post's short URL.
        */
        public function makhlas_is_post_id_link_changed($post_id) {
            $key = get_post_meta($post_id, '_makhlas_key', false);
            if (!empty($key)) {
                $api_data = $this->makhlas_get_short_url_from_key($key);
                if (isset($api_data['long_url'])) {
                    return get_permalink($post_id) == $api_data['long_url'];
                }
            }

            return false;
        }

        /**
        * Get exists short url from long url.
        *
        * @param int $post_id ID of post.
        *
        * @return string Post's short URL.
        */
        public function makhlas_get_short_url_from_post_id($post_id) {
            return get_post_meta($post_id, '_makhlas_short_url', false);
        }

        /**
        * Update post meta
        *
        * @param int $post_id ID of post.
        *
        * @param array meta data.
        */
        public function makhlas_update_post_meta($post_id, $meta_data) {
            if (isset($meta_data['short_url'])) {
                update_post_meta($post_id, '_makhlas_short_url', $meta_data['short_url']);
            }

            if (isset($meta_data['key'])) {
                update_post_meta($post_id, '_makhlas_key', $meta_data['key']);
            }
        }

        /**
        * Update post meta
        *
        * @param int $post_id ID of post.
        *
        * @param array meta data.
        */
        public function makhlas_get_default_domain() {
            return get_option('makhlas_default_domain', 'urly.ir');
        }

        /**
        * Shortening url.
        *
        * @param string long url.
        *
        * @param string domain name.
        *
        * @param string short url custom hash.
        */
        public function makhlas_shortener_url($long_url, $domain = null, $hash = null, $check_duplicate = true) {

            if ($check_duplicate && !empty($long_url)) {
                $exists_link = $this->makhlas_get_short_url_from_long_url($long_url);
                if (isset($exists_link['short_url'])) {
                    return $exists_link;
                }
            }


            if (empty($domain)) {
                $domain = $this->makhlas_get_default_domain();
            }

            $body = array(
                'long_url' => $long_url,
                'domain' => $domain,
                'hash' => $hash
            );
             
            $args = array(
                'body' => $body,
                'timeout' => '60',
                'httpversion' => '1.0',
                'headers' => $this->makhlas_get_api_header()
            );
             
            $response = wp_remote_post($this->api_base_url . 'url', $args);
            if (!is_wp_error($response) && isset($response['body'])) {
                $resault = json_decode($response['body'], true);
                if (isset($resault['data'][0]['short_url'])) {
                    return $resault['data'][0];
                }
            }

            return false;
        }

        /**
        * Update short url
        *
        * @param string long url.
        *
        * @param string domain name.
        *
        * @param string short url custom hash.
        */
        public function makhlas_update_short_url($long_url, $key = null, $hash = null) {
            $body = array(
                'long_url' => $long_url,
                'hash' => $hash
            );
             
            $args = array(
                'body' => $body,
                'method' => 'PUT',
                'timeout' => '60',
                'httpversion' => '1.0',
                'headers' => $this->makhlas_get_api_header()
            );
             
            $response = wp_remote_request($this->api_base_url . 'url/' . $key, $args);
            if (!is_wp_error($response) && isset($response['body'])) {
                $resault = json_decode($response['body'], true);
                if (isset($resault['data'][0]['short_url'])) {
                    return $resault['data'][0];
                }
            }
            
            return false;
        }

        /**
        * Return the Makhlas URL matching the long post URL.
        *
        * @param string $long Full length URL for post.
        *
        * @return string Post's short URL
        */
        public function makhlas_get_short_url_from_long_url($long_url) {
            $args = array(
                'headers' => $this->makhlas_get_api_header()
            );

            $response = wp_remote_get($this->api_base_url . 'url/show?long_url=' . $long_url, $args);

            if (!is_wp_error($response) && isset($response['body'])) {
                $resault = json_decode($response['body'], true);
                if (isset($resault['data'][0]['short_url'])) {
                    return $resault['data'][0];
                }
            }

            return false;
        }

        /**
        * Return the Makhlas URL matching the key.
        *
        * @param string $long Full length URL for post.
        *
        * @return string Post's short URL
        */
        public function makhlas_get_short_url_from_key($key) {
            $args = array(
                'headers' => $this->makhlas_get_api_header()
            );

            $response = wp_remote_get($this->api_base_url . 'url/' . $key, $args);

            if (!is_wp_error($response) && isset($response['body'])) {
                $resault = json_decode($response['body'], true);
                if (isset($resault['data'][0]['short_url'])) {
                    return $resault['data'][0];
                }
            }

            return false;
        }

        /**
        * Return the User's domains list.
        *
        * @return array domains
        */
        public function makhlas_get_user_domain_names() {
            $args = array(
                'headers' => $this->makhlas_get_api_header()
            );

            $response = wp_remote_get($this->api_base_url . 'domain?withpublic=1', $args);

            if (!is_wp_error($response) && isset($response['body'])) {
                $resault = json_decode($response['body'], true);
                if (isset($resault['data'][0]['domain'])) {
                    return $resault['data'];
                }
            }

            return false;
        }

        /**
        * Add new column (short_url) on posts table
        *
        * @param array table columns
        *
        * @return array table columns
        */
        public function makhlas_posts_table_column($columns) {
            $columns['short_url'] = __('Short Url', 'makhlas');
            return $columns;
        }

        /**
        * Return columns value
        *
        * @param string column name
        *
        * @param int $post_id ID of post.
        *
        * @return string column value
        */
        public function makhlas_posts_custom_columns($column_name, $post_id) {
            switch ($column_name) {
                case 'short_url':
                    $short_url = get_post_meta($post_id, '_makhlas_short_url', true);
                    if (!empty($short_url)) {
                        echo $short_url . ' <a target="_blank" href="http://makhlas.com/stats/' . get_post_meta($post_id, '_makhlas_key', true) . '"><span class="dashicons dashicons-chart-pie"></span></a>';
                    }
                    break;
            }
        }

        /**
        * Get the short URL for the current post within "the loop".
        *
        * @return string short URL for the current post.
        */
        public function makhlas_get_the_short_url() {
            $short_link = $this->makhlas_get_short_url_from_post_id(get_the_ID());
            return (!empty($short_link) ? $short_link[0] : '');
        }

        /**
        * Create short url for all posts url.
        */
        public function makhlas_convert_all_posts_link() {
            if (empty($_REQUEST['action']) || 'makhlas_convert_all_posts_link' != $_REQUEST['action']) {
                return;
            }

            if (!current_user_can('manage_options')) {
                return;
            }

            check_ajax_referer('makhlas_converter', 'security');

            ignore_user_abort( true );

            if (!ini_get('safe_mode')) {
                @set_time_limit(0);
            }

            $convetNumber = 10;
            $step  = isset($_POST['step']) ? absint($_POST['step'])  : 1;
            $total = isset($_POST['total']) ? absint($_POST['total']) : false;
            $convertResault = array();

            if (empty($total) || $total <= 1) {
                $posts = wp_count_posts();
                $total = $posts->publish;
                $convertResault['total'] = $total;
            }

            $args = array(
                'number' => $convetNumber,
                'status' => 'publish',
                'order'  => 'ASC',
                'meta_query' => array(
                    array(
                        'key' => '_makhlas_short_url',
                        'compare' => 'NOT EXISTS'
                    ),
                )
            );
            $posts = get_posts($args);

            if ($posts) {
                foreach( $posts as $post ) {
                    $post_id = get_the_ID();
                    $resault = $this->makhlas_shortener_url(get_permalink($post->ID));
                    $this->makhlas_update_post_meta($post->ID, $resault);
                    sleep(1);
                }

                $convertResault['converted'] = $step * $convetNumber;
                $step ++;
                $convertResault['step'] = $step;
                echo json_encode($convertResault); exit;

            } else {
                echo json_encode(array('step' => 'done')); exit;
            }
        }

    }
 
    $Makhlas = new Makhlas_Plugin();

    /**
    * Get the relative short URL for the current post within "the loop".
    *
    * @return string The relative short URL.
    */
    function makhlas_get_the_short_url() {
        $Makhlas = new Makhlas_Plugin();
        return $Makhlas->makhlas_get_the_short_url();
    }

    /**
     * Echo the short URL for the current post within "the loop".
     */
    function makhlas_the_short_url() {
        echo makhlas_get_the_short_url();
    }

    /**
     * Get the relative the short link for the current post within "the loop".
     */
    function makhlas_get_the_short_link() {
        return '<a href="' . makhlas_get_the_short_url() . '" class="makhlas short-link" rel="nofollow">' . makhlas_get_the_short_url() . '</a>';
    }

    /**
     * Echo the short link for the current post within "the loop".
     */
    function makhlas_the_short_link() {
        echo makhlas_get_the_short_link();
    }

    /**
     * Echo the short URL input for the current post within "the loop".
     */
    function makhlas_the_short_url_input() {
        echo '<input class="makhlas short-url-input" type="text" value="' . makhlas_get_the_short_url() . '"></input>';
    }
}