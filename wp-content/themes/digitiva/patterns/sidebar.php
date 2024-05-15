<?php
/**
 * Title: sidebar
 * Slug: digitiva/sidebar
 * Categories: hidden
 * Inserter: no
 */
?>
<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|55"}},"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:search {"label":"","showLabel":false,"placeholder":"Searching...","width":100,"widthUnit":"%","buttonText":"Search","buttonPosition":"button-inside","buttonUseIcon":true,"className":"search-input"} /--></div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|40","padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"className":"container-with-gradient-border-radius","layout":{"type":"default"}} -->
<div class="wp-block-group container-with-gradient-border-radius" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php echo __('Popular posts', 'digitiva');?></h3>
<!-- /wp:heading -->

<!-- wp:latest-posts {"displayFeaturedImage":true} /--></div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|40","padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"className":"container-with-gradient-border-radius","layout":{"type":"default"}} -->
<div class="wp-block-group container-with-gradient-border-radius" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php echo __('Categories', 'digitiva');?></h3>
<!-- /wp:heading -->

<!-- wp:categories {"showHierarchy":true,"showPostCounts":true,"showOnlyTopLevel":true} /--></div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|40","padding":{"right":"var:preset|spacing|50","left":"var:preset|spacing|50","top":"var:preset|spacing|50","bottom":"var:preset|spacing|50"}}},"className":"container-with-gradient-border-radius","layout":{"type":"default"}} -->
<div class="wp-block-group container-with-gradient-border-radius" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)"><!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading"><?php echo __('Popular tags ', 'digitiva');?></h3>
<!-- /wp:heading -->

<!-- wp:tag-cloud {"numberOfTags":5,"taxonomy":"category","smallestFontSize":"12pt","largestFontSize":"12pt"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->