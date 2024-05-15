<?php
/* controlling view of dashboard*/
if (!defined('ABSPATH')) {
  die;
}

/* intiating variables */
$errors = '';

/* getting dashboard settings value */
if (!get_option('ga4wp_dash_settings')) {
  $ga4wp_dash_settings = $defaults;
  update_option('ga4wp_dash_settings', $defaults);
} else {
  $ga4wp_dash_settings = get_option('ga4wp_dash_settings');
}

/* saving dashboard settings on successful submission */
if (isset($_POST['ga4wp_dash_submit']) && wp_verify_nonce($_POST['ga4wp_nonce_header'], 'ga4wp_dash_submit')) {
  if (!empty($_POST['ga4wp_dash_settings'])) {
    $ga4wp_dash_settings_save = GA4WP_Settings::get_instance()->parse_ga4wp_dash_settings($_POST['ga4wp_dash_settings']);
    if ($ga4wp_dash_settings_save) {
      if ($ga4wp_dash_settings_save['report_frame'] == 'Yesterday') {
        $ga4wp_dash_settings_save['report_to'] = date('Y-m-d', strtotime('-1 day'));
        $ga4wp_dash_settings_save['report_from'] = date('Y-m-d', strtotime('-1 day'));
      } elseif ($ga4wp_dash_settings_save['report_frame'] == 'Last 7 days') {
        $ga4wp_dash_settings_save['report_to'] = date('Y-m-d', strtotime('-1 day'));
        $ga4wp_dash_settings_save['report_from'] = date('Y-m-d', strtotime('-8 day'));
      } elseif ($ga4wp_dash_settings_save['report_frame'] == 'Today') {
        $ga4wp_dash_settings_save['report_to'] = date('Y-m-d', strtotime('now'));
        $ga4wp_dash_settings_save['report_from'] = date('Y-m-d', strtotime('now'));
      } else {
        $ga4wp_dash_settings_save['report_to'] = date('Y-m-d', strtotime('-1 day'));
        $ga4wp_dash_settings_save['report_from'] = date('Y-m-d', strtotime('-31 day'));
      }
      update_option('ga4wp_dash_settings', $ga4wp_dash_settings_save);
      echo '<script>
          jQuery(document).ready(function(){
             M.toast({html: "'. __('Setting Saved!', 'ga-for-wp-text') .'", classes: "rounded teal", displayLength:4000});
          });
      </script>';
      $ga4wp_dash_settings = $ga4wp_dash_settings_save;
    } else {
      $errors .= 'Error while saving data!<br>';
      $ga4wp_dash_settings = $ga4wp_dash_settings_save;
    }
  }
}

/* displaying errors */
if (strlen($errors) > 0) {
  echo '<script>
            jQuery(document).ready(function(){
               M.toast({html:" '. __('Please correct following Errors:', 'ga-for-wp-text') .'", classes: "rounded red", displayLength:6000});
               M.toast({html:" '. $errors . '", classes: "rounded red", displayLength:8000});
            });
        </script>';
}
?>

<div class="ga4wp-col s12 ga4wp-options">
  <div class="ga4wp-col s12 top-mar">
    <form action="" method="POST">
      <div class="ga4wp-col m3 s12 input-field">
        <select name="ga4wp_dash_settings[report_frame]" id="report_frame">
          <option value="Today" <?php if (isset($ga4wp_dash_settings['report_frame'])) {
            echo $ga4wp_dash_settings['report_frame'] == 'Today' ? 'selected="selected"' : '';
          } ?>><?php _e('Today', 'ga-for-wp-text'); ?></option>
          <option value="Yesterday" <?php if (isset($ga4wp_dash_settings['report_frame'])) {
            echo $ga4wp_dash_settings['report_frame'] == 'Yesterday' ? 'selected="selected"' : '';
          } ?>><?php _e('Yesterday', 'ga-for-wp-text'); ?></option>
          <option value="Last 7 days" <?php if (isset($ga4wp_dash_settings['report_frame'])) {
            echo $ga4wp_dash_settings['report_frame'] == 'Last 7 days' ? 'selected="selected"' : '';
          } ?>><?php _e('Last 7 days', 'ga-for-wp-text'); ?></option>
          <option value="Last 30 days" <?php if (isset($ga4wp_dash_settings['report_frame'])) {
            echo $ga4wp_dash_settings['report_frame'] == 'Last 30 days' ? 'selected="selected"' : '';
          } ?>><?php _e('Last 30 days', 'ga-for-wp-text'); ?></option>
        </select>
        <label>
          <?php _e('Select View', 'ga-for-wp-text'); ?>Date Range
        </label>
      </div>
      <div class="ga4wp-col m5 s12">
        <div class="ga4wp-col m6 l-bord from">
          <label>
            <?php _e('From', 'ga-for-wp-text'); ?>
          </label>
          <input type="text" name="ga4wp_dash_settings[report_from]" class="datepicker" id="from"
            value="<?php if (isset($ga4wp_dash_settings['report_from'])) {
              echo $ga4wp_dash_settings['report_from'];
            } ?>">
        </div>
        <div class="ga4wp-col m6 l-bord to">
          <label>
            <?php _e('To', 'ga-for-wp-text'); ?>
          </label>
          <input type="text" name="ga4wp_dash_settings[report_to]" class="datepicker" id="to"
            value="<?php if (isset($ga4wp_dash_settings['report_to'])) {
              echo $ga4wp_dash_settings['report_to'];
            } ?>">
        </div>
      </div>
      <div class="ga4wp-col m1 s12">
        <button class="btn waves-effect waves-light top-mar" type="submit" name="ga4wp_dash_submit" value="submit">
          <?php _e('Go', 'ga-for-wp-text'); ?>
        </button>
      </div>
      <?php wp_nonce_field('ga4wp_dash_submit', 'ga4wp_nonce_header'); ?>
    </form>
  </div>
  <div class="clearfix"></div>
  <div class="divider top-mar-20" style="margin-bottom:20px"></div>
  <div class="ga4wp-row">
    <ul class="tabs">
      <li class="tab ga4wp-col m2 s4"><a id="dash-tab" href="#dash">
          <?php _e('Dashboard', 'ga-for-wp-text'); ?>
        </a></li>
      <li class="tab ga4wp-col m2 s4"><a id="audience-pro-tab" href="#upgrade-pro">
          <?php _e('Upgrade', 'ga-for-wp-text'); ?><i class="material-icons ga4wp_pro_icon info">info</i>
        </a></li>
    </ul>
  </div>
  <div id="dash" class="ga4wp-col s12"></div>
  <div id="upgrade-pro" class="ga4wp-col s12">
    <div class="ga4wp-row">
      <div class="ga4wp-col s12 m12 l8 xl9 ga4wp-flex">
        <?php 
        $features = GA4WP_Settings::get_instance()->ga4wp_features_list;
          foreach ($features as $image=>$feature ){
            $pro = $feature[2]?'<sup>pro</sup>':'';
            echo '<div class="ga4wp-col s12 m6 l6 xl6 valign-wrapper ga4wp-info-box">
              <div class="ga4wp-col s4 m3 l2 xl2">
                <img class="ga4wp-info-img" src="'.GA4WP_URL.'assests/images/GA4WP.png">
              </div>
              <div class="ga4wp-col s8 m9 l10 xl10">
                <p class="ga4wp-info-title">'.$feature[0].' '.$pro.'</p>
                <p class="ga4wp-info-description">'.$feature[1].'</p>
              </div> 
            </div>'; 
          }
        ?>
      </div>
      <div class="ga4wp-col s12 m12 l4 xl3"></div>   
      <h5 class="center-align">
        <?php _e('Please upgrade to unlock reports and stats associated with your website.', 'ga-for-wp-text'); ?>
      </h5>
      <div class="center-align top-mar-30">
        <a class="waves-effect waves-light btn" href="<?php echo gfw_fs()->get_upgrade_url(); ?>"><?php _e('Upgrade Now!', 'ga-for-wp-text'); ?></a>
      </div>
    </div>      
  </div>
    
  <div class="clearfix"></div>
</div>
<?php