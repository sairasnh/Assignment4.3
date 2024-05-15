<?php
/**
 * Title: header
 * Slug: digitiva/header
 * Categories: hidden
 * Inserter: no
 */
?>
<!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:columns {"style":{"spacing":{"padding":{"left":"var:preset|spacing|container","right":"var:preset|spacing|container"},"margin":{"top":"var:preset|spacing|55","bottom":"0"}}},"className":"header-columns"} -->
<div class="wp-block-columns header-columns" style="margin-top:var(--wp--preset--spacing--55);margin-bottom:0;padding-right:var(--wp--preset--spacing--container);padding-left:var(--wp--preset--spacing--container)"><!-- wp:column {"verticalAlignment":"center","width":"30%","style":{"spacing":{"padding":{"top":"0","bottom":"0"},"blockGap":"0"}},"backgroundColor":"custom-dark-background","className":"header-logo","layout":{"type":"default"}} -->
<div class="wp-block-column is-vertically-aligned-center header-logo has-custom-dark-background-background-color has-background" style="padding-top:0;padding-bottom:0;flex-basis:30%"><!-- wp:site-title {"level":5,"textAlign":"left","style":{"typography":{"textTransform":"uppercase","fontStyle":"normal","fontWeight":"600"}},"className":"text-logo"} /--></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","className":"header-col-2"} -->
<div class="wp-block-column is-vertically-aligned-center header-col-2"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|45"}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
<div class="wp-block-group"><!-- wp:navigation {"icon":"menu","className":"header-menu-navigation","style":{"typography":{"fontStyle":"normal","fontWeight":"600"},"spacing":{"blockGap":"var:preset|spacing|45"}}} /-->

<!-- wp:buttons {"className":"header-menu-button","layout":{"type":"flex","flexWrap":"nowrap"},"style":{"layout":{"selfStretch":"fit","flexSize":null}}} -->
<div class="wp-block-buttons header-menu-button"><!-- wp:button {"gradient":"custom-main-gradiant","style":{"spacing":{"padding":{"left":"var:preset|spacing|50","right":"var:preset|spacing|50","top":"var:preset|spacing|35","bottom":"var:preset|spacing|35"}},"typography":{"fontStyle":"normal","fontWeight":"600"}},"className":"header-button"} -->
<div class="wp-block-button header-button" style="font-style:normal;font-weight:600"><a class="wp-block-button__link has-custom-main-gradiant-gradient-background has-background wp-element-button" href="#" style="padding-top:var(--wp--preset--spacing--35);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--35);padding-left:var(--wp--preset--spacing--50)"><?php echo __('Button', 'digitiva');?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->