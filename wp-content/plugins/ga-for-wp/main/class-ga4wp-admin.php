<?php
/*class for creating plugin menu and tabs */
if (!defined('ABSPATH')) {
	die;
}
/*
 * Declaring Class
 */
class GA4WP_Admin
{
	public function __construct()
	{
		/* ading admin view class */
		$this->includes();
		/* adding stylesheets and scripts of plugin */
		add_action('admin_enqueue_scripts', array($this, 'ga4wp_enqueue_scripts'));
		/* adding plugin link in wp-menu */
		add_action('admin_menu', array($this, 'add_menu_pages'));
		/* add Review Request */
		add_action('admin_notices', array($this, 'add_review_request'));
		/* hide review request using ajax */
		add_action('wp_ajax_ga4wp_hide_review_notice', array($this, 'hide_review_request'));
		add_action('wp_ajax_nopriv_ga4wp_hide_review_notice', array($this, 'hide_review_request'));
		/* adding links to plugin on pluings page*/
		add_filter('plugin_action_links_' . GA4WP_BASENAME, array($this, 'settings_link'));
		add_action('wp_footer', array($this, 'ga4wp_add_this_script_footer'));
		add_action('admin_footer', array($this, 'ga4wp_add_this_script_footer'));
	}

	/* add review request in plugin */
	public function add_review_request()
	{
		$review_request_time = get_option('ga4wp_review_request_time');
		if ($review_request_time) {
			$current_time = time();
			if ($current_time > $review_request_time) {
				echo '<div class="notice notice-success is-dismissible">
				<p>
					<img style="float:left;margin-right:27px;width: 50px;padding: 0.25em;" src="' . GA4WP_URL . 'assests/images/GA4WP.png">
					<strong>
						' . __('Hi there! You\'ve been using GA4WP: Google Analytics for Wordpress Plugin. We hope it\'s been helpful. Would you mind rating it 5-stars to help spread the word?', 'ga-for-wp-text') . '
					</strong>	
				</p>
				<p>
					<a class="button button-primary" target="_blank" href="https://wordpress.org/support/plugin/ga-for-wp/reviews/?rate=5#rate-response>" data-reason="am_now">
						<strong>' . __('Ok, you deserve it', 'ga-for-wp-text') . '</strong>
					</a>
					<a class="button-secondary ga4wp-dismiss-maybelater" data-reason="maybe_later">
						' . __('Nope, maybe later', 'g4-for-wp-text') . '
					</a>
					<a class="button-secondary ga4wp-dismiss-alreadydid" data-reason="already_did">
						' . __('I already did', 'ga4-wp-for-text') . '
					</a>
				</p>
			</div>';
			}
		} else {
			update_option('ga4wp_review_request_time', strtotime(date('d-m-Y H:i:s') . "+ 48 hours"));
		}
	}

	/* hide review request */
	public function hide_review_request()
	{
		$nonce = $_REQUEST['security'];
		if (wp_verify_nonce($nonce, 'maybelater-nonce')) {
			update_option('ga4wp_review_request_time', strtotime(date('d-m-Y H:i:s') . "+ 48 hours"));
		}
		if (wp_verify_nonce($nonce, 'alreadydid-nonce')) {
			update_option('ga4wp_review_request_time', strtotime(date('d-m-Y H:i:s') . "+ 1800 hours"));
		}
	}
	/* adding analytics code to front for triggering events */
	public function ga4wp_add_this_script_footer()
	{
		$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$url = strtok($url, '?');
		if (isset($_SERVER['HTTP_REFERER'])) {
			$old_url = $_SERVER['HTTP_REFERER'];
		} else {
			$old_url = '';
		}
		if (is_user_logged_in()) {
			$user_id = get_current_user_id();
			$transient_id = 'ga4wp_analytics_code_' . $user_id;
			$user_cid = $this->get_cid();
			$transient_id_2 = 'ga4wp_analytics_code_' . $user_cid;
			$ana_code_2 = get_transient($transient_id_2);
		} else {
			$user_cid = $this->get_cid();
			$transient_id = 'ga4wp_analytics_code_' . $user_cid;
		}
		$ana_code = get_transient($transient_id);
		if (!empty($ana_code_2)) {
			$ana_code .= $ana_code_2;
		}
		if (!empty($ana_code)) {
			if (class_exists('WooCommerce')) {
				if (is_cart() || is_checkout()) {
					if ($url !== $old_url) {
						echo "<script>" . $ana_code . "</script>";
						delete_transient($transient_id);
						if (isset($transient_id_2)) {
							delete_transient($transient_id_2);
						}
					}
				} else {
					echo "<script>" . $ana_code . "</script>";
					delete_transient($transient_id);
					if (isset($transient_id_2)) {
						delete_transient($transient_id_2);
					}
				}
			} else {
				echo "<script>" . $ana_code . "</script>";
				delete_transient($transient_id);
				if (isset($transient_id_2)) {
					delete_transient($transient_id_2);
				}
			}
		}
	}

	/* getting cid for event api calls */
	private function get_cid($generate_cid = false)
	{
		$cid = '';
		/* get client identity via GA cookie and only accepting value if it validated */
		if (isset($_COOKIE['_ga'])) {
			$ga_cookie_data = filter_var($_COOKIE['_ga'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
			$data = explode('.', $ga_cookie_data);
			if (is_array($data) && count($data) > 3) {
				if (strlen($data[2]) > 3 && strlen($data[3]) > 3) {
					$cid = $data[2] . '.' . $data[3];
				}
			}
		}
		/* generate custom cid if cookie is not set */
		if (empty($cid)) {
			$custom_cid = $generate_cid || (empty($cid) && is_user_logged_in());
			if ($custom_cid) {
				$bytes = random_bytes(16);
				$bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40); // set version to 0100
				$bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80); // set bits 6-7 to 10
				return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
			}
		} else {
			return $cid;
		}
	}

	/* Adding plugin menu to Admin menu of WP */
	public function add_menu_pages()
	{
		$title = __('GA4WP', 'ga-for-wp-text');
		$this->pages['ga4wp'] = new GA4WP_Admin_View($title, 'ga4wp_pro_plugin_options');
	}

	/*  enqueue scripts */
	public function ga4wp_enqueue_scripts()
	{   
		$screen = get_current_screen();
		$this->register_scripts();
		if (isset($_GET['page']) && ($_GET['page'] == 'ga4wp_pro_plugin_options')) {
			wp_enqueue_script('ga4wp_material_js');
			wp_enqueue_style('ga4wp_material_css');
		}
		if ((isset($_GET['page']) && ($_GET['page'] == 'ga4wp_pro_plugin_options'))||($screen -> id == "dashboard")) {
			wp_enqueue_style('ga4wp_icons');
			wp_enqueue_style('ga4wp_css');
			wp_enqueue_script('ga4wp_chart_js');
		}
		wp_enqueue_script('ga4wp_ajax_js');
	}

	/* registering scripts */
	private function register_scripts()
	{
		wp_register_style('ga4wp_material_css', GA4WP_URL . 'assests/css/materialize.min.css', false, null);
		wp_register_style('ga4wp_css', GA4WP_URL . 'assests/css/ga4wp.css', false, null);
		wp_register_style('ga4wp_icons', 'https://fonts.googleapis.com/icon?family=Material+Icons');
		wp_register_script('ga4wp_material_js', GA4WP_URL . 'assests/js/materialize.min.js', array('jquery'), null, true);
		wp_register_script('ga4wp_chart_js', GA4WP_URL . 'assests/js/chart.js', null, true);
		wp_register_script('ga4wp_ajax_js', GA4WP_URL . 'assests/js/ga4wp-ajax.js', array('jquery'), null, true);
		wp_localize_script('ga4wp_ajax_js', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php'), 'maybelater_nonce' => wp_create_nonce('maybelater-nonce'), 'alreadydid_nonce' => wp_create_nonce('alreadydid-nonce'), ));
	}

	/* Adding Settings link on plugins page */
	public function settings_link($links, $url_only = false, $networkwide = false)
	{
		$settings_page = is_multisite() && is_network_admin() ? network_admin_url('admin.php?page=ga4wp_pro_plugin_options') : menu_page_url('ga4wp_pro_plugin_options', false);
		/* If networkwide setting url is needed. */
		$settings_page = $url_only && $networkwide && is_multisite() ? network_admin_url('admin.php?page=ga4wp_pro_plugin_options') : $settings_page;
		$settings = '<a href="' . $settings_page . '">' . __('Settings', 'ga-for-wp-text') . '</a>';
		/* Return only settings page link. */
		if ($url_only) {
			return $settings_page;
		}
		if (!empty($links)) {
			array_unshift($links, $settings);
		} else {
			$links = array($settings);
		}
		return $links;
	}

	/* Adding tab views to plugin */
	private function includes()
	{
		/* main view class */
		include_once GA4WP_DIR . 'inc/abstract-ga4wp-view.php';
		include_once GA4WP_DIR . 'inc/class-ga4wp-admin-view.php';
	}
}