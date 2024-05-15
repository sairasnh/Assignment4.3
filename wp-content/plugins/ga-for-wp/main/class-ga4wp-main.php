<?php
/* Adding Main functionality of plugin here */
if (!defined('ABSPATH')) {
	die;
}
/*
 * Declaring Class
 */
class GA4WP_Main
{
	/* initiating variables */
	private $event_hooks;
	private $javascript = '';
	private $event_settings;
	private $params = array();
	private $data = array();
	private $tracking_id;
	private $cid;
	private $api_secret;
	private $loop_items;

	public function __construct()
	{
		if ($this->get_tracking_id()) {
			if ($this->disable_tracking()) {
				return;
			}
			$this->get_event_hooks();
			foreach ($this->event_hooks as $key => $value) {
				if (is_array($value)) {
					$number_args = null;
					if (is_int($value[0])) {
						$number_args = $value[0];
						unset($value[0]);
						$value = array_values($value);
					}
					if ($value[0] == 'filter') {
						unset($value[0]);
						$value = array_values($value);
						foreach ($value as $single_hook) {
							if (array_key_exists($key, $this->event_settings)) {
								if (isset($number_args)) {
									add_filter($single_hook, array($this, $key), 10, $number_args);
								} else {
									add_filter($single_hook, array($this, $key));
								}
							}
						}
					} else {
						foreach ($value as $single_hook) {
							if (array_key_exists($key, $this->event_settings)) {
								if (isset($number_args)) {
									add_action($single_hook, array($this, $key), 10, $number_args);
								} else {
									add_action($single_hook, array($this, $key));
								}
							}
						}
					}
				} else {
					if (array_key_exists($key, $this->event_settings)) {
						add_action($value, array($this, $key));
					}
				}
			}
			add_action('wp_head', array($this, 'get_tracking_code'), 9);
			add_action('admin_head', array($this, 'get_special_tracking_code'), 9);
			add_action('login_head', array($this, 'get_tracking_code'), 9);
			add_action('template_redirect', array($this, 'capture_js'), 9);
			add_filter('woocommerce_queued_js', array($this, 'print_js'), 11);
			add_action('woocommerce_before_shop_loop_item', array($this, 'product_impression'));
		}
	}

	/* getting tracking id */
	public function get_tracking_id()
	{   
		if (get_option('ga4wp_auth_settings')) {
			$auth_settings = get_option('ga4wp_auth_settings');
			if (!empty($auth_settings['api_secret'])) {
				$this->api_secret = $auth_settings['api_secret'];
			}else{
				$measurement_key = get_option('measurement_key');
				if(!empty($measurement_key)){
					$this->api_secret = $measurement_key;
				}else{
					$this->api_secret = false;
				}
			}
			if (isset($auth_settings['property_id'])) {
				$property = $auth_settings['property_id'];
				$pieces = explode('|', $property);
				$this->tracking_id = $pieces[1];
				$this->cid = $this->get_cid(true);
				return $pieces[1];
			} else {
				if (isset($auth_settings['tracking_id'])) {
					$this->tracking_id = $auth_settings['tracking_id'];
					$this->cid = $this->get_cid(true);
					return $auth_settings['tracking_id'];
				} else {
					return false;
				}
			}
		} else {
			return false;
		}
	}

	/* adding tracking code to website */
	public function get_tracking_code()
	{
		if ($this->disable_tracking()) {
			return;
		}
		$tracking_id = esc_js($this->get_tracking_id());
		$gtag_code_snippet = '<!-- Google Analytics Code Snippet By GA4WP --><script async src="https://www.googletagmanager.com/gtag/js?id=' . $tracking_id . '"></script>
		<script>
		  window.dataLayer = window.dataLayer || [];
		  function gtag(){dataLayer.push(arguments);}
		  gtag(\'js\', new Date());';
		$addon_values = array();
		if ($tracking_options = get_option('ga4wp_track_settings')) {
			if (isset($tracking_options['track_ga_consent']) && $tracking_options['track_ga_consent']) {
				$gtag_code_snippet .= 'gtag("consent", "default", {
				ad_storage: "denied",
				analytics_storage: "denied",
				functionality_storage: "denied",
				personalization_storage: "denied",
				security_storage: "granted",
				wait_for_update: 2000, 
				});
				gtag("set", "ads_data_redaction", true);
				gtag("set", "url_passthrough", true);';
			}
			if (isset($tracking_options['track_interest']) && $tracking_options['track_interest']) {
				$gtag_code_snippet .= 'gtag("set", "allow_ad_personalization_signals", false);
				gtag("set", "allow_google_signals", false);';
			}
			if (isset($tracking_options['not_track_user_id']) && $tracking_options['not_track_user_id']) {
				// do nothing
			} else {
				if (is_user_logged_in()) {
					$user_id = esc_js(get_current_user_id());
					$addon_values[] = "'user_id':'{$user_id}'";
				}
			}
			if (isset($tracking_options['not_track_pageviews']) && $tracking_options['not_track_pageviews']) {
				$addon_values[] = "'send_page_view': false";
			}
			if (isset($tracking_options['enhanced_link_attribution']) && $tracking_options['enhanced_link_attribution']) {
				$addon_values[] = "'link_attribution': true";
			}
			if (isset($tracking_options['anonymize_ip']) && $tracking_options['anonymize_ip']) {
				$addon_values[] = "'anonymize_ip': true";
			}
		}
		$advance_options = get_option('ga4wp_advance_settings');
		if ($advance_options) {
			if (isset($advance_options['google_optimize']) && $advance_options['google_optimize'] && isset($advance_options['google_optimize_code']) && ($advance_options['google_optimize_code'] !== '')) {
				$addon_values[] = "'optimize_id':'{$advance_options['google_optimize_code']}'";
			}
			if (isset($advance_options['google_adword']) && $advance_options['google_adword'] && isset($advance_options['google_adword_code']) && ($advance_options['google_adword_code'] !== '')) {
				$gtag_code_snippet .= "gtag('config', '{$advance_options['google_adword_code']}');";
			}
		}
		if (!empty($addon_values) && is_array($addon_values)) {
			$addon_code = implode(',', $addon_values);
			$gtag_code_snippet .= "gtag('config', '{$tracking_id}', {{$addon_code}});";
		} else {
			$gtag_code_snippet .= "gtag('config', '{$tracking_id}');";
		}
		$gtag_code_snippet .= "</script> <!- end of Google Analytics Code Snippet by GA4WP-->";
		$gtag_code_snippet = apply_filters('ga4wp_gtag_code_snippet', $gtag_code_snippet, $tracking_options, $advance_options);
		echo $gtag_code_snippet;
		if(isset($advance_options['google_optimize']) && $advance_options['google_optimize'] && isset($advance_options['google_optimize_code']) && ($advance_options['google_optimize_code'] !== '')) {
			echo '<script src="https://www.googleoptimize.com/optimize.js?id='.$advance_options['google_optimize_code'].'"></script>';
		}
		if ($advance_options) {
			if (isset($advance_options['facebook_pixel']) && $advance_options['facebook_pixel'] && isset($advance_options['facebook_pixel_code']) && ($advance_options['facebook_pixel_code'] !== '')) {
				?>
				<!-- Facebook Pixel Code By GA4WP -->
				<script>
					!function (f, b, e, v, n, t, s) {
						if (f.fbq) return; n = f.fbq = function () {
							n.callMethod ?
							n.callMethod.apply(n, arguments) : n.queue.push(arguments)
						};
						if (!f._fbq) f._fbq = n; n.push = n; n.loaded = !0; n.version = '2.0';
						n.queue = []; t = b.createElement(e); t.async = !0;
						t.src = v; s = b.getElementsByTagName(e)[0];
						s.parentNode.insertBefore(t, s)
					}(window, document, 'script',
						'https://connect.facebook.net/en_US/fbevents.js');
					fbq('init', '<?php echo $advance_options['facebook_pixel_code']; ?>');
					fbq('track', 'PageView');
				</script>
				<noscript><img height="1" width="1" style="display:none"
						src="https://www.facebook.com/tr?id=<?php echo $advance_options['facebook_pixel_code']; ?>&ev=PageView&noscript=1" /></noscript>
				<!-- End Facebook Pixel Code -->
				<?php
			}
		}
	}

	/* adding tracking code to website */
	public function get_special_tracking_code()
	{
		$tracking_id = esc_js($this->get_tracking_id());
		$gtag_code_snippet = '<!-- Google Analytics Code Snippet for Admin Side By GA4WP --><script async src="https://www.googletagmanager.com/gtag/js?id=' . $tracking_id . '"></script>
			<script>
			  window.dataLayer = window.dataLayer || [];
			  function gtag(){dataLayer.push(arguments);}
			  gtag(\'js\', new Date());';
		$addon_values = array();
		if ($tracking_options = get_option('ga4wp_track_settings')) {
			$addon_values[] = "'send_page_view': false";
			if (isset($tracking_options['anonymize_ip']) && $tracking_options['anonymize_ip']) {
				$addon_values[] = "'anonymize_ip': true";
			}
		}
		if (!empty($addon_values) && is_array($addon_values)) {
			$addon_code = implode(',', $addon_values);
			$gtag_code_snippet .= "gtag('config', '{$tracking_id}', {{$addon_code}});";
		} else {
			$gtag_code_snippet .= "gtag('config', '{$tracking_id}');";
		}
		$advance_options = get_option('ga4wp_advance_settings');
		$gtag_code_snippet .= "</script> <!- end of Google Analytics Code Snippetfor Admin by GA4WP-->";
		$gtag_code_snippet = apply_filters('ga4wp_admin_gtag_code_snippet', $gtag_code_snippet, $tracking_options, $advance_options);
		echo $gtag_code_snippet;
	}

	public function print_js($js)
	{
		if ($this->disable_tracking()) {
			return;
		}
		if (get_option('print_js')) {
			delete_option('print_js');
		}
		return $js;
	}

	/* getting events and their respective hooks*/
	public function get_event_hooks()
	{
		$settings = GA4WP_Settings::get_instance();
		$this->event_hooks = $settings->ga4wp_event_hooks;
		$this->event_settings = $settings->ga4wp_event_settings;
		if (get_option('ga4wp_event_settings')) {
			$this->event_settings = get_option('ga4wp_event_settings');
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

	/* adding some js to footer of website */
	public function capture_js($javascript = '')
	{
		if (!empty($javascript) && ($javascript !== '')) {
			wc_enqueue_js($javascript);
			//$this->javascript .= $javascript;
		} else {
			if (get_option('print_js')) {
				$print_js = get_option('print_js');
				if (!empty($print_js) && ($print_js !== '')) {
					wc_enqueue_js($print_js);
				}
			}
		}
		if (get_option('print_js')) {
			$print_js = get_option('print_js');
			if (!empty($javascript) && ($javascript !== '')) {
				$javascript .= $print_js;
				update_option('print_js', $javascript);
			}
		} else {
			if (!empty($javascript) && ($javascript !== '')) {
				add_option('print_js', $javascript);
			}
		}
	}

	/* creating transient based on current user id or cid */
	public function ga4wp_set_transient($ana_code)
	{
		if (is_user_logged_in()) {
			$user_id = get_current_user_id();
			$transient_id = 'ga4wp_analytics_code_' . $user_id;
		} else {
			$user_cid = $this->get_cid();
			$transient_id = 'ga4wp_analytics_code_' . $user_cid;
		}
		$ga4wp_analytics_code = get_transient($transient_id);
		if ($ga4wp_analytics_code) {
			$ga4wp_analytics_code .= $ana_code;
		} else {
			$ga4wp_analytics_code = $ana_code;
		}
		set_transient($transient_id, $ga4wp_analytics_code, 300);
	}

	/* check for tracking should function or not */
	private function disable_tracking()
	{
		if ($this->get_tracking_id()) {
			$disable_tracking = false;
		} else {
			$disable_tracking = true;
		}
		$user_id = get_current_user_id();
		if ($user_id && user_can($user_id, 'manage_woocommerce')) {
			$tracking_options = get_option('ga4wp_track_settings');
			if (isset($tracking_options['track_admin']) && $tracking_options['track_admin'] && is_user_logged_in() && $this->get_tracking_id()) {
				$disable_tracking = false;
			} else {
				$settings = GA4WP_Settings::get_instance();
				$track_default_settings = $settings->ga4wp_tracking_settings;
				if (empty($tracking_options) && isset($track_default_settings['track_admin']) && $track_default_settings['track_admin'] && is_user_logged_in() && $this->get_tracking_id()) {
					$disable_tracking = false;
				} else {
					$disable_tracking = true;
				}
			}
		}
		return $disable_tracking;
	}

	/* Avoid multi trigerring of same event */
	private function avoid_multi_trigger()
	{
		if (!isset($_SERVER['HTTP_REFERER'])) {
			if (isset($_SERVER['REQUEST_URI'])) {
				update_option('ga4wp_old_url', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
			}
			return true;
		}
		if (get_option('ga4wp_old_url')) {
			$ga4wp_old_url = get_option('ga4wp_old_url');
			if (($ga4wp_old_url !== parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
				update_option('ga4wp_old_url', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
				return true;
			} else {
				return false;
			}
		} else {
			if ((parse_url($_SERVER['HTTP_REFERER'], PHP_URL_PATH) !== parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
				update_option('ga4wp_old_url', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
				return true;
			}
		}
	}

	/* get product variation attributes */
	public function get_product_variation_attributes($product)
	{
		if (!$product instanceof \WC_Product) {
			$product = wc_get_product($product);
		}
		if (!$product) {
			return '';
		}
		$variant = '';
		if ('variation' === $product->get_type()) {
			$variant = implode(',', array_values($product->get_variation_attributes()));
		} elseif ('variable' === $product->get_type()) {
			global $woocommerce;
			$attributes = $product->get_default_attributes();
			$variant = implode(', ', array_values($attributes));
		}
		return $variant;
	}

	/* getting user agent from server */
	public function ga4wp_get_user_agent()
	{
		return isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';
	}

	/* product impression */
	public function product_impression()
	{
		$tracking_options = get_option('ga4wp_track_settings');
		if (!((isset($tracking_options['product_single_track']) && is_product()) || (isset($tracking_options['product_archive_track']) && (is_shop() || is_product_taxonomy() || is_product_category() || is_product_tag() || is_cart())))) {
			return;
		}
		global $product, $woocommerce_loop, $woocommerce;
		if (!$product instanceof \WC_Product) {
			return;
		}
		if(!empty($this->api_secret)){
			global $woocommerce_loop;
			$current_total = (($woocommerce_loop['current_page'] - 1)*$woocommerce_loop['per_page']) + ($woocommerce_loop['loop']);
			$item_list_name = $this->ga4wp_esc($this->get_list_name());
			$item_list_id = $this->ga4wp_esc(strtolower(str_replace(' ', '_', $item_list_name)));
			$item_data[] = $this->ga4wp_esc($this->get_product_details($product->get_id()));
			$this->loop_items[] = array(
				'item_id' => $this->ga4wp_esc($item_data[0]['item_id']),
				'item_name' => $this->ga4wp_esc($item_data[0]['item_name']),
				'currency' => $this->ga4wp_esc(get_woocommerce_currency()),
				'item_category' => $this->ga4wp_esc($item_data[0]['item_category']),
				'item_list_id' => $this->ga4wp_esc($item_list_id),
				'item_list_name' => $this->ga4wp_esc($item_list_name),
				'price' => $this->ga4wp_esc($item_data[0]['price']),
				'quantity' => $this->ga4wp_esc($item_data[0]['quantity']),
				'index' => $this->ga4wp_esc($current_total),
			);
			if(isset($woocommerce_loop['per_page']) && !empty($woocommerce_loop['per_page'])){
				if($woocommerce_loop['per_page'] < $woocommerce_loop['total']){
					if($woocommerce_loop['loop'] == $woocommerce_loop['per_page'])  {
						$this->data = $this->init_default_params();
						$this->data['events'][0]= array(
							'name' => 'view_item_list',
							'params' => array(
								'items'=> $this->loop_items,
								'item_list_name' => $this->ga4wp_esc($item_list_name),
								'item_list_id' => $this->ga4wp_esc($item_list_id),
							),
						);
						$this->making_remote_request();
						$this->params = null;
						$this->loop_items = null;
						$this->data = null;
					}elseif($current_total == $woocommerce_loop['total']){
						$this->data = $this->init_default_params();
						$this->data['events'][0]= array(
							'name' => 'view_item_list',
							'params' => array(
								'items'=> $this->loop_items,
								'item_list_name' => $this->ga4wp_esc($item_list_name),
								'item_list_id' => $this->ga4wp_esc($item_list_id),
							),
						);
						$this->making_remote_request();
						$this->params = null;
						$this->loop_items = null;
						$this->data = null;
					}
				}else{
					if($woocommerce_loop['loop'] == $woocommerce_loop['total'])  {
						$this->data = $this->init_default_params();
						$this->data['events'][0]= array(
							'name' => 'view_item_list',
							'params' => array(
								'items'=> $this->loop_items,
								'item_list_name' => $this->ga4wp_esc($item_list_name),
								'item_list_id' => $this->ga4wp_esc($item_list_id),
							),
						);
						$this->making_remote_request();
						$this->params = null;
						$this->loop_items = null;
						$this->data = null;
					}
				}
			}else{
				if($woocommerce_loop['loop'] == $woocommerce_loop['columns'])  {	
					$this->data = $this->init_default_params();
					$this->data['events'][0]= array(
						'name' => 'view_item_list',
						'params' => array(
							'items'=> $this->loop_items,
							'item_list_name' => $this->ga4wp_esc($item_list_name),
							'item_list_id' => $this->ga4wp_esc($item_list_id),
						),
					);
					$this->making_remote_request();
					$this->params = null;
					$this->loop_items = null;
					$this->data = null;
				}
			}
		}else{
			global $woocommerce_loop;
			$item_data[] = $this->get_product_details($product->get_id());
			$item_list_name = $this->get_list_name();
			$item_list_id = strtolower(str_replace(' ', '_', $item_list_name));
			$this->loop_items .= '{
				item_id: "' . $this->ga4wp_esc($item_data[0]['item_id']) . '",
				item_name: "' . $this->ga4wp_esc($item_data[0]['item_name']) . '",
				currency: "' . $this->ga4wp_esc(get_woocommerce_currency()) . '",
				item_category: "' . $this->ga4wp_esc($item_data[0]['item_category']) . '",
				item_list_id: "' . $this->ga4wp_esc($item_list_id) . '",
				item_list_name: "' . $this->ga4wp_esc($item_list_name) . '",
				price: ' . $this->ga4wp_esc($item_data[0]['price']) . ',
				quantity: ' . $this->ga4wp_esc($item_data[0]['quantity']) . ',
				},';
			if(isset($woocommerce_loop['per_page']) && !empty($woocommerce_loop['per_page'])){
				if($woocommerce_loop['loop'] == $woocommerce_loop['per_page'])  {
					$ga4wp_analytics_code = 'gtag("event", "view_item_list", {
						item_list_id: "' . $this->ga4wp_esc($item_list_id) . '",
						item_list_name: "' . $this->ga4wp_esc($item_list_name) . '",
						items: ['.$this->loop_items.']
					});';
					$this->ga4wp_set_transient($ga4wp_analytics_code);
				}
			}else{
				if($woocommerce_loop['loop'] == $woocommerce_loop['columns'])  {
					$ga4wp_analytics_code = 'gtag("event", "view_item_list", {
						item_list_id: "' . $this->ga4wp_esc($item_list_id) . '",
						item_list_name: "' . $this->ga4wp_esc($item_list_name) . '",
						items: ['.$this->loop_items.']
					});';
					$this->ga4wp_set_transient($ga4wp_analytics_code);
				}
			}
		}
		/*$ga4wp_analytics_code = 'gtag("event", "view_item_list", {
				item_list_id: "' . $item_list_id . '",
				item_list_name: "' . $item_list_name . '",
				items: [
				{
					item_id: "' . $item_data[0]['item_id'] . '",
					item_name: "' . $item_data[0]['item_name'] . '",
					currency: "' . get_woocommerce_currency() . '",
					item_category: "' . $item_data[0]['item_category'] . '",
					item_list_id: "' . $item_list_id . '",
					item_list_name: "' . $item_list_name . '",
					price: ' . $item_data[0]['price'] . ',
					quantity: ' . $item_data[0]['quantity'] . ',
				}
				]
			});';*/
	}

	/* get list name */
	public function get_list_name()
	{
		$list_name = '';
		if (is_search()) {
			$list_name = 'Search';
		} elseif (is_shop()) {
			$list_name = 'Shop';
		} elseif (is_product_category()) {
			$list_name = 'Product Category';
		} elseif (is_product_tag()) {
			$list_name = 'Product Tag';
		} elseif (is_archive()) {
			$list_name = 'Archive';
		} elseif (is_single()) {
			$list_name = 'Product Page';
		} elseif (is_cart()) {
			$list_name = 'cart page';
		}
		return $list_name;
	}

	/* intiating default params(check for more details) */
	private function init_default_params($track_user = true)
	{
		$this->data['client_id'] = $this->cid;
		if ($track_user && (is_user_logged_in())) {
			if (isset($_COOKIE['opt_in'])) {
				if ($_COOKIE['opt_in'] == 'yes') {
					$this->data['user_id'] = esc_js(get_current_user_id());
				}
			} else {
				$this->data['user_id'] = esc_js(get_current_user_id());
			}
		}
		return $this->data;
	}

	/* setting up request arguments for api request */
	protected function get_request_args()
	{
		if (function_exists($this->ga4wp_get_user_agent())) {
			$user_agent = $this->ga4wp_get_user_agent();
		} else {
			$user_agent = sprintf('%s/%s (WordPress/%s)', 'GA4WP', GA4WP_VERSION, $GLOBALS['wp_version']);
		}
		$args = array(
			'method' => 'POST',
			'timeout' => MINUTE_IN_SECONDS,
			'redirection' => 0,
			'sslverify' => true,
			'user-agent' => $user_agent,
			'body' => json_encode($this->data),
		);
		return $args;
	}

	/* get product details */
	private function get_product_details($product_id, $quantity = 1, $i = 1)
	{
		global $woocommerce_loop;
		$product = wc_get_product($product_id);
		if ($product instanceof \WC_Product) {
			$product_identifier = ($sku = $product->get_sku()) ? $sku : $product_id;
			$categories = wc_get_product_terms($product_id, 'product_cat', array('orderby' => 'parent', 'order' => 'DESC'));
			if (is_array($categories) || !empty($categories)) {
				$product_category = '';
				foreach ($categories as $category) {
					if (isset($category->name) && is_object($category)) {
						$product_category .= $category->name . '/';
					}
				}
				if (isset($product_category)) {
					$product_category = trim($product_category, '/'); // product category
				}
			}
			$item['item_id'] = $this->ga4wp_esc(strval($product_identifier));
			$item['item_name'] = $this->ga4wp_esc($product->get_title());
			$item['quantity'] =$this->ga4wp_esc($quantity);
			$item['item_category'] = $this->ga4wp_esc($product_category);
			$item['item_variant'] = $this->ga4wp_esc($this->get_product_variation_attributes($product));
			$item['price'] = $this->ga4wp_esc($product->get_price());
			$item['index'] = $this->ga4wp_esc(isset( $woocommerce_loop['loop'] ) ? $woocommerce_loop['loop'] : '');
			foreach ($item as $key => $value) {
				if (empty($value)) {
					unset($item[$key]);
				}
			}
			return $item;
		}
		return '';
	}

	/* making remote request */
	private function making_remote_request()
	{	
		$remote_url = null;
		if ((strpos((string) $this->tracking_id, 'G') !== false) && !empty($this->api_secret)) {
			
			$remote_url = 'https://www.google-analytics.com/mp/collect?measurement_id=' . $this->tracking_id . '&api_secret=' . $this->api_secret;
		}
		if (!empty($remote_url)) {
			$args = $this->get_request_args();
			$response = null;
			$i = 0;
			while (1) {
				$response = wp_safe_remote_request(untrailingslashit($remote_url), $args);
				if (!empty($response) && is_array($response)) {
					if (isset($response['response']['code']) && ((int) $response['response']['code'] < 300)) {
						break;
					}
				}
				if ($i > 1) {
					break;
				}
				$i++;
			}
		}
	}

	/* recording signed in event -completed*/
	public function user_login($user_login, $user)
	{
		
		if(!empty($this->api_secret)){
			if (class_exists('WooCommerce')) {
				if(is_checkout()){
					$this->data['events'][0]= array(
						'name' => 'login',
						'params'=>array(
							'method' => 'checkout',
						),
					);
				}else{
					$this->data['events'][0]= array(
						'name' => 'login',
						'params'=>array(
							'method' => 'myaccount',
						),
					);
				}
			}else{
					$this->data['events'][0]= array(
						'name' => 'login',
						'params'=>array(
							'method' => 'wplogin',
						),
					);
			}
			$this->making_remote_request();
			$this->params = null;
			$this->data = null;
		}else{
			if (class_exists('WooCommerce')) {
				if (is_checkout()) {
					$ga4wp_analytics_code = 'gtag("event", "login", {
								method: "checkout"
							});';
				} else {
					$ga4wp_analytics_code = 'gtag("event", "login", {
								method: "myaccount"
							});';
				}
			} else {
				$ga4wp_analytics_code = 'gtag("event", "login", {
							method: "myaccount"
						});';
			}
			$this->ga4wp_set_transient($ga4wp_analytics_code);
		}
	}

	/* recording signed out event -completed*/
	public function user_logout()
	{
		if(!empty($this->api_secret)){
			$this->data = $this->init_default_params();
			$this->data['events'][0]= array(
				'name' => 'logout',
			);
			$this->making_remote_request();
			$this->params = null;
			$this->data = null;
		}else{
			$ga4wp_analytics_code = 'gtag("event", "logout", {});';
			$this->ga4wp_set_transient($ga4wp_analytics_code);
		}
	}

	/* recording viewed signup form event -completed */
	public function viewed_signup_form()
	{
		/* if (!$this->avoid_multi_trigger()) {
			return;
		} */
		if(!empty($this->api_secret)){
			$this->data = $this->init_default_params();
			$this->data['events'][0]= array(
				'name' => 'viewed_signup_form',
			);
			$this->making_remote_request();
			$this->params = null;
			$this->data = null;
		}else{
			$ga4wp_analytics_code = 'gtag("event", "viewed_signup_form", {});';
			$this->ga4wp_set_transient($ga4wp_analytics_code);
		}
	}

	/* recording user signup form event -completed*/
	public function user_signup()
	{
		if(!empty($this->api_secret)){
			$this->data = $this->init_default_params();
			if (class_exists('WooCommerce')) {
				if(is_checkout()){
					$this->data['events'][0]= array(
						'name' => 'sign_up',
						'params'=>array(
							'method' => 'checkout',
						),
					);
				}else{
					$this->data['events'][0]= array(
						'name' => 'sign_up',
						'params'=>array(
							'method' => 'myaccount',
						),
					);
				}
			}else{
				$this->data['events'][0]= array(
					'name' => 'sign_up',
					'params'=>array(
						'method' => 'wp-signup',
					),
				);
			}
			$this->making_remote_request();
			$this->params = null;
			$this->data = null;
		}else{
			if (class_exists('WooCommerce')) {
				if (is_checkout()) {
					$ga4wp_analytics_code = 'gtag("event", "sign_up", { method: "checkout"});';
					$this->ga4wp_set_transient($ga4wp_analytics_code);
				} else {
					$ga4wp_analytics_code = 'gtag("event", "sign_up", { method: "myaccount"});';
					$this->ga4wp_set_transient($ga4wp_analytics_code);
				}
			} else {
				$ga4wp_analytics_code = 'gtag("event", "sign_up", { method: "myaccount"});';
				$this->ga4wp_set_transient($ga4wp_analytics_code);
			} 
		}
	}

	/* recording user viewed my account page */
	public function viewed_account()
	{
		/* if (!$this->avoid_multi_trigger()) {
			return;
		} */
		if(!empty($this->api_secret)){
			$this->data = $this->init_default_params();
			$this->data['events'][0]= array(
				'name' => 'viewed_account',
			);
			$this->making_remote_request();
			$this->params = null;
			$this->data = null;
		}else{
			$ga4wp_analytics_code = 'gtag("event", "viewed_account", {});';
			$this->ga4wp_set_transient($ga4wp_analytics_code);
		}
	}

	/* recording user viewed order */
	public function viewed_order($order_id)
	{
		/* if (!$this->avoid_multi_trigger()) {
			return;
		} */
		if(!empty($this->api_secret)){
			$this->data = $this->init_default_params();
			$this->data['events'][0]= array(
				'name' => 'viewed_order',
			);
			$this->making_remote_request();
			$this->params = null;
			$this->data = null;
		}else{
			$ga4wp_analytics_code = 'gtag("event", "viewed_order", {});';
			$this->ga4wp_set_transient($ga4wp_analytics_code);
		}
	}

	/* recording user changed password event*/
	public function changed_password()
	{
		if(!empty($this->api_secret)){
			$this->data = $this->init_default_params();
			$this->data['events'][0]= array(
				'name' => 'changed_password',
			);
			$this->making_remote_request();
			$this->params = null;
			$this->data = null;
		}else{
			$ga4wp_analytics_code = 'gtag("event", "changed_password", {});';
			$this->ga4wp_set_transient($ga4wp_analytics_code); 
		}
	}

	/* recording event for writing review for product */
	public function wrote_review($comment_ID)
	{
		$comment = get_comment($comment_ID);
		$post_ID = $comment->comment_post_ID;
		$type = get_post_type($post_ID);
		if ('product' == $type) {
			if(!empty($this->api_secret)){
				$this->data = $this->init_default_params();
				$this->data['events'][0]= array(
					'name' => 'wrote_review',
				);
				$this->making_remote_request();
				$this->params = null;
				$this->data = null;
			}else{
				$ga4wp_analytics_code = 'gtag("event", "wrote_review", {});';
				$this->ga4wp_set_transient($ga4wp_analytics_code);
			}
		}
	}

	/* recording event for writing comment for post */
	public function commented($comment_ID)
	{
		$comment = get_comment($comment_ID);
		$post_ID = $comment->comment_post_ID;
		$type = get_post_type($post_ID);
		if ('post' == $type) {
			if(!empty($this->api_secret)){
				$this->data = $this->init_default_params();
				$this->data['events'][0]= array(
					'name' => 'commented',
				);
				$this->making_remote_request();
				$this->params = null;
				$this->data = null;
			}else{
				$ga4wp_analytics_code = 'gtag("event", "commented", {});';
				$this->ga4wp_set_transient($ga4wp_analytics_code); 
			}
		}
	}

	/* recording event for viewing shop page */
	public function viewed_shop()
	{
		if (class_exists('WooCommerce')) {
			if (is_shop()) {
				/* if (!$this->avoid_multi_trigger()) {
					return;
				} */
				if(!empty($this->api_secret)){
					$this->data = $this->init_default_params();
					$this->data['events'][0]= array(
						'name' => 'viewed_shop',
					);
					$this->making_remote_request();
					$this->params = null;
					$this->data = null;
				}else{
					$ga4wp_analytics_code = 'gtag("event", "viewed_shop", {});';
					$this->ga4wp_set_transient($ga4wp_analytics_code);
				}
			}
		}
	}

	/* recording event for viewing cart page -completed */
	public function viewed_cart()
	{
		if (is_cart()) {
			/* if (!$this->avoid_multi_trigger()) {
				return;
			} */
			$i = 0;
			$items_data = null;
			foreach (WC()->cart->get_cart() as $item) {
				$product_id = !empty($item['variation_id']) ? $item['variation_id'] : $item['product_id'];
				$items_data[$i] = $this->get_product_details($product_id, $item['quantity'], $i);
				$i++;
			}
			$items_data = json_encode($items_data);
			$cart_value = floatval(preg_replace('#[^\d.]#', '', WC()->cart->get_cart_contents_total()));
			if(!empty($this->api_secret)){
				$this->data['events'][0]= array(
					'name' => 'view_cart',
					'params' => array(
						'currency' => $this->ga4wp_esc(get_woocommerce_currency()),
						'items'=> $items_data,
						'value' => $this->ga4wp_esc($cart_value),
					),
				);
				$this->making_remote_request();
				$this->params = null;
				$this->data = null;
			}else{
				$ga4wp_analytics_code = 'gtag("event", "view_cart", {
					currency: "' . $this->ga4wp_esc(get_woocommerce_currency()) . '",
					value:' . $this->ga4wp_esc($cart_value) . ',
					items:' . $items_data . '
				});';
				$this->ga4wp_set_transient($ga4wp_analytics_code);
			}
		}
	}

	/* recording event for viewing product -completed*/
	public function viewed_product()
	{
		/* if (!$this->avoid_multi_trigger()) {
			return;
		} */
		$product_id = get_the_ID();
		$product = wc_get_product($product_id);
		$items_data[] = $this->get_product_details($product_id);
		$items_data = json_encode($items_data);
		$this->data = $this->init_default_params();
		$item_data[] = $this->get_product_details($product_id);
		if(!empty($this->api_secret)){
			$this->data['events'][0]= array(
				'name' => 'view_item',
				'params' => array(
					'currency' => $this->ga4wp_esc(get_woocommerce_currency()),
					'items'=> $item_data,
					'value' => $this->ga4wp_esc($product->get_price()),
				),
			);
			$this->making_remote_request();
			$this->params = null;
			$this->data = null;
		}else{
			$ga4wp_analytics_code = 'gtag("event", "view_item", {
				currency: "' . $this->ga4wp_esc(get_woocommerce_currency()) . '",
				value:' . $this->ga4wp_esc($product->get_price()) . ',
				items:' . $items_data . '
			});';
			$this->ga4wp_set_transient($ga4wp_analytics_code);
		}
		$advance_options = get_option('ga4wp_advance_settings');
		if (isset($advance_options['facebook_pixel']) && $advance_options['facebook_pixel'] && isset($advance_options['facebook_pixel_code']) && ($advance_options['facebook_pixel_code'] !== '')) {
			if (function_exists('get_woocommerce_currency')) {
				$product_currency = get_woocommerce_currency();
			} else {
				$product_currency = '';
			}
			$this->capture_js("fbq('track', 'ViewContent',{
				value: " . $this->ga4wp_esc(floor($product->get_price())) . ",
				currency: '" . $this->ga4wp_esc($product_currency) . "',
				content_ids: " . $this->ga4wp_esc($product_id) . ",
				content_type: 'product'
			});");
		}
	}

	/* recording event for product added to cart -completed*/
	public function added_product($cart_item_key)
	{
		$item = WC()->cart->cart_contents[$cart_item_key];
		$product_id = !empty($item['variation_id']) ? $item['variation_id'] : $item['product_id'];
		if (!$product_id) {
			return;
		}
		$product = wc_get_product($product_id);
		if(!empty($this->api_secret)){
			$this->data = $this->init_default_params();
			$item_data[] = $this->get_product_details($product_id);
			$this->data['events'][0]= array(
				'name' => 'add_to_cart',
				'params' => array(
					'currency' => $this->ga4wp_esc(get_woocommerce_currency()),
					'items'=> $item_data,
					'value' => $this->ga4wp_esc($product->get_price()),
				),
			);
			$this->making_remote_request();
			$this->params = null;
			$this->data = null;
		}else{
			$items_data[] = $this->get_product_details($product_id);
			$items_data = json_encode($items_data);
			$ga4wp_analytics_code = 'gtag("event", "add_to_cart", {
				currency: "' . $this->ga4wp_esc(get_woocommerce_currency()) . '",
				value:' . $this->ga4wp_esc($product->get_price()) . ',
				items:' . $items_data . '
			});';
			$this->ga4wp_set_transient($ga4wp_analytics_code);
		}
		$advance_options = get_option('ga4wp_advance_settings');
		if (isset($advance_options['facebook_pixel']) && $advance_options['facebook_pixel'] && isset($advance_options['facebook_pixel_code']) && ($advance_options['facebook_pixel_code'] !== '')) {
			if (function_exists('get_woocommerce_currency')) {
				$product_currency = get_woocommerce_currency();
			} else {
				$product_currency = '';
			}
			$this->capture_js("fbq('track', 'AddToCart',{
				value: " . $this->ga4wp_esc(floor($product->get_price())) . ",
				currency:'" . $this->ga4wp_esc($product_currency) . "',
				content_ids: " . $this->ga4wp_esc($product_id) . ",
				content_type: 'product'
			});");
		}
	}

	/* recording event for product removed from cart -completed*/
	public function removed_product($cart_item_key)
	{
		if (isset(WC()->cart->cart_contents[$cart_item_key])) {
			$item = WC()->cart->cart_contents[$cart_item_key];
			$product_id = !empty($item['variation_id']) ? $item['variation_id'] : $item['product_id'];
			if (!$product_id) {
				return;
			}
			$product = wc_get_product($product_id);
			if(!empty($this->api_secret)){
				$this->data = $this->init_default_params();
				$item_data[] = $this->get_product_details($product_id);
				$this->data['events'][0]= array(
					'name' => 'remove_from_cart',
					'params' => array(
						'currency' => get_woocommerce_currency(),
						'items'=> $item_data,
						'value' => $product->get_price(),
					),
				);
				$this->making_remote_request();
				$this->params = null;
				$this->data = null;
			}else{
				$items_data[] = $this->get_product_details($product_id);
				$items_data = json_encode($items_data);
				$ga4wp_analytics_code = 'gtag("event", "remove_from_cart", {
					currency: "' . get_woocommerce_currency() . '",
					value:' . $product->get_price() . ',
					items:' . $items_data . '
				});';
				$this->ga4wp_set_transient($ga4wp_analytics_code);
			}
		}
	}

	/* User changing cart quantity for added product -completed*/
	public function changed_quantity($cart_item_key, $quantity)
	{
		if (isset(WC()->cart->cart_contents[$cart_item_key])) {
			$item = WC()->cart->cart_contents[$cart_item_key];
			$product_id = !empty($item['variation_id']) ? $item['variation_id'] : $item['product_id'];
			if (!$product_id) {
				return;
			}
			$product = wc_get_product($product_id);
			if(!empty($this->api_secret)){
				$this->data = $this->init_default_params();
				$this->data['events'][0]= array(
					'name' => 'changed_cart_quantity',
				);
				$this->making_remote_request();
				$this->params = null;
				$this->data = null;
			}else{
				$ga4wp_analytics_code = 'gtag("event", "changed_cart_quantity", {});';
				$this->ga4wp_set_transient($ga4wp_analytics_code);
			}
		}
	}

	/* User estimated shipping charges event -completed*/
	public function estimated_shipping()
	{
		if(!empty($this->api_secret)){
			$this->data = $this->init_default_params();
			$this->data['events'][0]= array(
				'name' => 'estimated_shipping',
			);
			$this->making_remote_request();
			$this->params = null;
			$this->data = null;
		}else{
			$ga4wp_analytics_code = 'gtag("event", "estimated_shipping", {});';
			$this->ga4wp_set_transient($ga4wp_analytics_code); 
		}
	}

	/* recording event for login errors */
	public function user_login_errors($error_msg)
	{
		if(!empty($this->api_secret)){
			$this->data = $this->init_default_params();
			$this->data['events'][0]= array(
				'name' => 'user_login_errors',
			);
			$this->making_remote_request();
			$this->params = null;
			$this->data = null;
		}else{
			$ga4wp_analytics_code = 'gtag("event", "user_login_errors", {});';
			$this->ga4wp_set_transient($ga4wp_analytics_code);
		}
		return $error_msg;
	}

	/* recording event for lost password reset */
	public function lost_password($lost_password_msg)
	{
		if(!empty($this->api_secret)){
			$this->data = $this->init_default_params();
			$this->data['events'][0]= array(
				'name' => 'lost_password',
			);
			$this->making_remote_request();
			$this->params = null;
			$this->data = null;
		}else{
			$ga4wp_analytics_code = 'gtag("event", "lost_password", {});';
			$this->ga4wp_set_transient($ga4wp_analytics_code);
		}
		return $lost_password_msg;
	}

	/* recording event for wrong_coupon_applied */
	public function wrong_coupon_applied($error_msg, $err_code, $coupon)
	{
		if(!empty($this->api_secret)){
			$this->data = $this->init_default_params();
			$this->data['events'][0]= array(
				'name' => 'wrong_coupon_applied',
			);
			$this->making_remote_request();
			$this->params = null;
			$this->data = null;
		}else{
			$ga4wp_analytics_code = 'gtag("event", "wrong_coupon_applied", {});';
			$this->ga4wp_set_transient($ga4wp_analytics_code); 
		}
		return $error_msg;
	}

	/* recording event for successfully applied coupon -completed*/
	public function applied_coupon($coupon_code)
	{
		if(!empty($this->api_secret)){
			$this->data = $this->init_default_params();
			$this->data['events'][0]= array(
				'name' => 'applied_coupon',
			);
			$this->making_remote_request();
			$this->params = null;
			$this->data = null;
		}else{
			$ga4wp_analytics_code = 'gtag("event", "applied_coupon", {});';
			$this->ga4wp_set_transient($ga4wp_analytics_code);
		}
	}

	/* recording event for removing applied coupon code -completed*/
	public function removed_coupon($coupon_code)
	{
		if(!empty($this->api_secret)){
			$this->data = $this->init_default_params();
			$this->data['events'][0]= array(
				'name' => 'removed_coupon',
			);
			$this->making_remote_request();
			$this->params = null;
			$this->data = null;
		}else{
			$ga4wp_analytics_code = 'gtag("event", "removed_coupon", {});';
			$this->ga4wp_set_transient($ga4wp_analytics_code); 
		}
	}

	/* recording event for initiating checkout -completed */
	public function begin_checkout()
	{
		/* if (!$this->avoid_multi_trigger()) {
			return;
		}*/
		if(!empty($this->api_secret)){
			foreach ( WC()->cart->get_cart() as $item ) {
				$i = 0;
				$i++;
				$product_id = ! empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'];
				$items_data[] = $this->get_product_details($product_id , $item['quantity'], $i );
			}
			$this->data = $this->init_default_params();
			$checkout_value = floatval( preg_replace( '#[^\d.,]#', '',WC()->cart->get_cart_total()));
			$applied_coupons = WC()->cart->get_applied_coupons();
			$coupon_code = null;
			foreach( $applied_coupons as $coupon){
				$coupon_code .= $coupon.'/';
			}
			$coupon_code = trim($coupon_code,'/');
			$this->data['events'][0]= array(
				'name' => 'begin_checkout',
				'params' => array(
					'coupon' => $this->ga4wp_esc($coupon_code),
					'currency' => $this->ga4wp_esc(get_woocommerce_currency()),
					'items'=> $items_data,
					'value' => $this->ga4wp_esc($checkout_value),
				),
			);
			$this->making_remote_request();
			$this->params = null;
			$this->data = null;
		}else{
			foreach (WC()->cart->get_cart() as $item) {
				$i = 0;
				$i++;
				$product_id = !empty($item['variation_id']) ? $item['variation_id'] : $item['product_id'];
				$items_data[] = $this->get_product_details($product_id, $item['quantity'], $i);
			}
			$checkout_value = floatval(preg_replace('#[^\d.,]#', '', WC()->cart->get_cart_contents_total()));
			$applied_coupons = WC()->cart->get_applied_coupons();
			$coupon_code = null;
			foreach ($applied_coupons as $coupon) {
				$coupon_code .= $coupon . '/';
			}
			if (!empty($coupon_code)) {
				$coupon_code = trim($coupon_code, '/');
			}
			$items_data = json_encode($items_data);
			$ga4wp_analytics_code = 'gtag("event", "begin_checkout", {
				currency: "' . $this->ga4wp_esc(get_woocommerce_currency()) . '",
			value: ' . $this->ga4wp_esc($checkout_value) . ',
			coupon: "' . $this->ga4wp_esc($coupon_code) . '",
			items:' . $items_data . ',
			});';
			$this->ga4wp_set_transient($ga4wp_analytics_code);
		}
	}

	/* recording checkout page events starting with user provided require info */
	public function filled_checkout_form()
	{
		$live_js = '';
		$option_name = is_user_logged_in() ? 'Registered User' : 'Guest';
		$live_js = "gtag( 'event','filled_checkout_form');";
		$added_js = "
			var user_info_fired = false;
			var all_filled = true;
			jQuery( 'form.checkout' ).on( 'change', 'input', function() {
				if(!user_info_fired){
					jQuery('input[id|=\'billing\']').each(function(){
						if (!all_filled){
							return;
						}
						if (!jQuery(this).val()){
							if(jQuery(this).attr('type')=='email'){
								if ( !isEmail( this.value )){
									all_filled = false;
									return;
								}
							}
							if(jQuery(this).attr('type')=='phone'){
								if ( !isPhone( this.value )){
									all_filled = false;
									return;
								}
							}
							if(!(jQuery(this).attr('id').includes('company') || jQuery(this).attr('id').includes('address_2'))){
								all_filled = false;
								return;
							}
						}
					});
					if(all_filled){
						user_info_fired = true;
						{$live_js}
					}
				}
			});
			function isEmail(email) {
							var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
							return regex.test(email);
			}
			function isPhone(phone) {
							var regex = /[0-9\-\(\)\s]+/;
							return regex.test(phone);
			}
			jQuery( 'form.checkout' ).on( 'checkout_place_order', function() { if ( !user_info_fired ) {user_info_fired = true;{$live_js}}});";
		if (!empty($added_js)) {
			$this->capture_js($added_js);
		}
	}

	/* selected shipping method */
	public function added_shipping_method()
	{
		
		$live_js = "function get_shipping_event (shipping_method) {
					return gtag( 'event','add_shipping_info',{'shipping_tier' :shipping_method});
				}";
		$js = '';
		$js = $live_js;
		$js .= "var selected_shipping_method = jQuery( 'input[name^=\'shipping_method\']:checked' ).val();";
		$js .= "var shipping_method_tracked = false; var shipping_method = '';";
		$js .= "jQuery( 'form.checkout' ).on( 'click', 'input[name^=\'shipping_method\']', function( e ) { if ( selected_shipping_method !== this.value ) { shipping_method = this.value; shipping_method_tracked = true; if(shipping_method){get_shipping_event(shipping_method);} selected_shipping_method = this.value; } });";
		$js .= "jQuery( 'form.checkout' ).on( 'checkout_place_order', function() { if ( !shipping_method_tracked ) {shipping_method = selected_shipping_method ; shipping_method_tracked = true; if(shipping_method){get_shipping_event(shipping_method);} } });";
		$this->capture_js($js);
	}

	/* selected payment method */
	public function added_payment_method()
	{
		$live_js = "function get_paymnet_event (payment_method) {
						return gtag( 'event','add_payment_info',{'payment_type' :payment_method});
					}";
		$js = '';
		$js = $live_js;
		$js .= "var selected_payment_method = jQuery( 'input[name=\'payment_method\']:checked' ).val();";
		$js .= "var payment_method_tracked = false; var payment_method = '';";
		$js .= "jQuery( 'form.checkout' ).on( 'click', 'input[name=\'payment_method\']', function( e ) { if ( selected_payment_method !== this.value ) { payment_method = this.value; payment_method_tracked = true; if(payment_method){get_paymnet_event(payment_method);} selected_payment_method = this.value; } });";
		$js .= "jQuery( 'form.checkout' ).on( 'checkout_place_order', function() { if ( !payment_method_tracked ) {payment_method = selected_payment_method ; payment_method_tracked = true; if(payment_method){get_paymnet_event(payment_method);} } });";
		$this->capture_js($js);
	}

	/* started payment */
	public function processing_payment($order_id)
	{
		$order = wc_get_order($order_id);
		if ($order instanceof WC_Order) {
			if(!empty($this->api_secret)){
				$this->data = $this->init_default_params();
				$this->data['events'][0]= array(
					'name' => 'processing_payment',
				);
				$this->making_remote_request();
				$this->params = null;
				$this->data = null;
			}else{
				$ga4wp_analytics_code = 'gtag("event", "processing_payment", {});';
				$this->ga4wp_set_transient($ga4wp_analytics_code);
			}
		}
	}

	/* order cancelled */
	public function order_cancelled($order_id)
	{
		if(!empty($this->api_secret)){
			$this->data = $this->init_default_params();
			$this->data['events'][0]= array(
				'name' => 'order_cancelled',
			);
			$this->making_remote_request();
			$this->params = null;
			$this->data = null;
		}else{
			$ga4wp_analytics_code = 'gtag("event", "order_cancelled", {});';
			$this->ga4wp_set_transient($ga4wp_analytics_code);
		}
	}

	/* order failed */
	public function order_failed($order_id, $order)
	{
		if ($order instanceof WC_Order) {
			if(!empty($this->api_secret)){
				$this->data = $this->init_default_params();
				$this->data['events'][0]= array(
					'name' => 'order_failed',
				);
				$this->making_remote_request();
				$this->params = null;
				$this->data = null;
			}else{
				$ga4wp_analytics_code = 'gtag("event", "order_failed", {});';
				$this->ga4wp_set_transient($ga4wp_analytics_code);
			}
		}
	}

	/* recording event for completed purchase */
	public function completed_purchase($order_id)
	{
		$order = wc_get_order($order_id);
		if (!$order || ('yes' === get_post_meta($order_id, 'ga4wp_already_tracked', true))) {
			return;
		}
		if ($tracking_options = get_option('ga4wp_track_settings')) {
			$order_status = $order->get_status();
			if (($order_status == 'on-hold') && isset($tracking_options['disable_on_hold_conversion'])) {
				return;
			}
		}
		$coupons_list = '';
		if ($order->get_coupon_codes()) {
			$i = 1;
			foreach ($order->get_coupon_codes() as $coupon) {
				if ($i > 1) {
					$coupons_list .= ',';
				}
				$coupons_list .= $coupon;
				$i++;
			}
			$this->params['tcc'] = $coupons_list;
		}
		if(!empty($this->api_secret)){
			$i = 0;
			$contents = array();
			foreach ( $order->get_items() as $item ) {
				$i++;
				$product_id = ! empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'];
				$items_data[] = $this->get_product_details($product_id , $item['qty'], $i );
				$contents[] = array(
				'id'=>$product_id,
				'quantity'=>$item['qty'],
				);
			}
			$this->data = $this->init_default_params();
			$this->data['events'][0]= array(
				'name' => 'purchase',
				'params' => array(
					'coupon' => $this->ga4wp_esc($coupons_list),
					'currency' => $this->ga4wp_esc(get_woocommerce_currency()),
					'items'=> $items_data,
					'transaction_id'=> $this->ga4wp_esc($order->get_order_number()),
					'value' => $this->ga4wp_esc($order->get_total()),
					'shipping' => $this->ga4wp_esc($order->get_total_shipping()),
					'tax' => $this->ga4wp_esc($order->get_total_tax()),
				),
			);
			$this->making_remote_request();
			$this->params = null;
			$this->data = null;
		}else{
			$i = 0;
			$contents = array();
			foreach ($order->get_items() as $item) {
				$i++;
				$product_id = !empty($item['variation_id']) ? $item['variation_id'] : $item['product_id'];
				$items_data[] = $this->get_product_details($product_id, $item['qty'], $i);
				$contents[] = array(
					'id' => $this->ga4wp_esc($product_id),
					'quantity' => $this->ga4wp_esc($item['qty']),
				);
			}
			$items_data = json_encode($items_data);
			$ga4wp_analytics_code = 'gtag("event", "purchase", {
				transaction_id: "' . $this->ga4wp_esc($order->get_order_number()) . '",
				value: ' . $this->ga4wp_esc($order->get_total()) . ',
				tax: ' . $this->ga4wp_esc($order->get_total_tax()) . ',
				shipping: ' . $this->ga4wp_esc($order->get_total_shipping()) . ',
				currency: "' . $this->ga4wp_esc(get_woocommerce_currency()) . '",
				coupon: "' . $this->ga4wp_esc($coupons_list) . '",
				items:' . $items_data . ',
				});';
			$this->ga4wp_set_transient($ga4wp_analytics_code);
		}
		update_post_meta($order->get_id(), 'ga4wp_already_tracked', 'yes');
		$this->adding_conversion_info($order_id, $contents);
	}

	/* adding conversion info */
	public function adding_conversion_info($order_id, $contents)
	{
		$order = wc_get_order($order_id);
		if ($order instanceof WC_Order) {
			/* working for google adword conversions */
			$advance_options = get_option('ga4wp_advance_settings');
			if ($advance_options) {
				if (isset($advance_options['google_adword']) && $advance_options['google_adword'] && isset($advance_options['google_adword_code']) && ($advance_options['google_adword_code'] !== '') && isset($advance_options['google_adword_label']) && ($advance_options['google_adword_label'] !== '')) {
					$this->capture_js("gtag('event', 'conversion', {
			      'send_to': '" . $this->ga4wp_esc($advance_options['google_adword_code'] . "/" . $advance_options['google_adword_label']) . "',
			      'value': " . $this->ga4wp_esc(floor($order->get_total())) . ",
			      'currency': '" . $this->ga4wp_esc($order->get_currency()) . "',
			      'transaction_id': '" . $this->ga4wp_esc($order->get_transaction_id()) . "'
			  	});");
				}
				if (isset($advance_options['facebook_pixel']) && $advance_options['facebook_pixel'] && isset($advance_options['facebook_pixel_code']) && ($advance_options['facebook_pixel_code'] !== '')) {
					$this->capture_js("fbq('track', 'Purchase',{
				    value: " . $this->ga4wp_esc(floor($order->get_total())) . ",
				    currency: '" . $this->ga4wp_esc($order->get_currency()) . "',
				    contents: " . json_encode($contents) . ",
				    content_type: 'product'
				  });");
				}
			}
		}
	}
	/* recording event for order refund */
	public function order_refunded($order_id, $refund_id)
	{
		if ('yes' === get_post_meta($refund_id, 'ga4wp_refund_already_tracked')) {
			return;
		}
		$order = wc_get_order($order_id);
		$refund = wc_get_order($refund_id);
		if (($order instanceof WC_Order) && ($refund instanceof WC_Order_Refund)) {
			if (method_exists($refund, 'get_reason') && $refund->get_reason()) {
				$reason = $order->get_order_number() . ' : ' . $refund->get_reason();
			} else {
				$reason = $order->get_order_number() . ' : Refund reason is not set';
			}
			$coupons_list = '';
			if ($order->get_coupon_codes()) {
				$i = 1;
				foreach ($order->get_coupon_codes() as $coupon) {
					if ($i > 1) {
						$coupons_list .= ',';
					}
					$coupons_list .= $coupon;
					$i++;
				}
				$this->params['tcc'] = $coupons_list;
			}
			if(!empty($this->api_secret)){
				$i = 0;
				$refund_items_data = null;
				$contents = array();
				$items = $refund->get_items();
				if ( ! empty( $items ) ) {
					foreach ( $items as $item ) {
						$i++;
						$product_id = ! empty( $item['variation_id'] ) ? $item['variation_id'] : $item['product_id'];
						$refund_items_data[] = $this->get_product_details($product_id , $item['qty'], $i );
					}
				}
				$this->data = $this->init_default_params();
				$this->data['events'][0]= array(
					'name' => 'refund',
					'params' => array(
						'coupon' => $this->ga4wp_esc($coupons_list),
						'currency' => $this->ga4wp_esc(get_woocommerce_currency()),
						//'items'=> $refund_items_data,
						'transaction_id'=> $this->ga4wp_esc($order->get_order_number()),
						'value' => $this->ga4wp_esc($refund->get_amount()),
					),
				);
				$this->making_remote_request();
				$this->params = null;
				$this->data = null;
			}else{
				$i = 0;
				$refund_items_data = null;
				$contents = array();
				$items = $refund->get_items();
				if (!empty($items)) {
					foreach ($items as $item) {
						$i++;
						$product_id = !empty($item['variation_id']) ? $item['variation_id'] : $item['product_id'];
						$refund_items_data[] = $this->get_product_details($product_id, $item['qty'], $i);
					}
				}
				$refund_items_data = json_encode($refund_items_data);
				$ga4wp_analytics_code = 'gtag("event", "refund", {
					transaction_id: "' . $this->ga4wp_esc($order->get_order_number()) . '",
				value: ' . $this->ga4wp_esc($refund->get_amount()) . ',
				currency: "' . $this->ga4wp_esc(get_woocommerce_currency()) . '",
				coupon: "' . $this->ga4wp_esc($coupons_list) . '",
				});';
				$this->ga4wp_set_transient($ga4wp_analytics_code);
			}
			update_post_meta($refund_id, 'ga4wp_refund_already_tracked', 'yes');
		}
	}

	/* Logging Errors */
	public function log_error($error)
	{
		if (!is_array($error)) {
			return;
		}
		if(!empty($this->api_secret)){
			$this->data = $this->init_default_params();
			$this->data['events'][0]= array(
				'name' => 'error_occured',
			);
			$this->making_remote_request();
			$this->params = null;
			$this->data = null;
		}else{
			$ga4wp_analytics_code = 'gtag("event", "error_occured", {});';
			$this->ga4wp_set_transient($ga4wp_analytics_code); 
		}
	}

	public function ga4wp_esc($string){
		if(!empty($string)){
			$string = str_replace( array( '"' , ';', '<', '>' ), ' ', $string);
			$string = trim(preg_replace('/\s+/', ' ', $string));
		}
		return $string;
	}
}