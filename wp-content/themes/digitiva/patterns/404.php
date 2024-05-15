<?php
/**
 * Title: 404
 * Slug: digitiva/404
 * Categories: hidden
 * Inserter: no
 */
?>
<!-- wp:template-part {"slug":"header","tagName":"header"} /-->

<!-- wp:group {"tagName":"main","layout":{"type":"default"}} -->
<main class="wp-block-group"><!-- wp:spacer {"height":"10rem"} -->
<div style="height:10rem" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"style":{"spacing":{"padding":{"right":"var:preset|spacing|container","left":"var:preset|spacing|container"},"blockGap":"var:preset|spacing|50"}}} -->
<div class="wp-block-column" style="padding-right:var(--wp--preset--spacing--container);padding-left:var(--wp--preset--spacing--container)"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|35"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|35"}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"center"}} -->
<div class="wp-block-group"><!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"100px"}}} -->
<h1 class="wp-block-heading has-text-align-center" style="font-size:100px"><?php echo __('4', 'digitiva');?></h1>
<!-- /wp:heading -->

<!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"100px"}},"className":"gradient-text"} -->
<h1 class="wp-block-heading has-text-align-center gradient-text" style="font-size:100px">0</h1>
<!-- /wp:heading -->

<!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"100px"}}} -->
<h1 class="wp-block-heading has-text-align-center" style="font-size:100px"><?php echo __('4', 'digitiva');?></h1>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|<?php echo __('4', 'digitiva');?>0"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:heading {"textAlign":"center","level":<?php echo __('4', 'digitiva');?>} -->
<h<?php echo __('4', 'digitiva');?> class="wp-block-heading has-text-align-center"><?php echo __('Oops! Page is not available.', 'digitiva');?></h<?php echo __('4', 'digitiva');?>>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center"><?php echo __('The page you are looking for was moved, removed, renamed, or never existed.', 'digitiva');?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"gradient":"custom-main-gradiant","style":{"spacing":{"padding":{"left":"var:preset|spacing|50","right":"var:preset|spacing|50","top":"var:preset|spacing|<?php echo __('4', 'digitiva');?>0","bottom":"var:preset|spacing|<?php echo __('4', 'digitiva');?>0"}},"typography":{"fontStyle":"normal","fontWeight":"600"}}} -->
<div class="wp-block-button" style="font-style:normal;font-weight:600"><a class="wp-block-button__link has-custom-main-gradiant-gradient-background has-background wp-element-button" href="/" style="padding-top:var(--wp--preset--spacing--<?php echo __('4', 'digitiva');?>0);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--<?php echo __('4', 'digitiva');?>0);padding-left:var(--wp--preset--spacing--50)"><?php echo __('Back to home', 'digitiva');?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:spacer {"height":"10rem"} -->
<div style="height:10rem" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer --></main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->