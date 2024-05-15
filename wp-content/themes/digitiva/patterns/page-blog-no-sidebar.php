<?php
/**
 * Title: page-blog-no-sidebar
 * Slug: digitiva/page-blog-no-sidebar
 * Categories: hidden
 * Inserter: no
 */
?>
<!-- wp:template-part {"slug":"header","area":"header"} /-->

<!-- wp:group {"tagName":"main","layout":{"type":"default"}} -->
<main class="wp-block-group"><!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:spacer {"height":"var:preset|spacing|75"} -->
<div style="height:var(--wp--preset--spacing--75)" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:heading {"textAlign":"center","level":1} -->
<h1 class="wp-block-heading has-text-align-center"><?php echo __('Blog (No Sidebar)', 'digitiva');?></h1>
<!-- /wp:heading -->

<!-- wp:spacer {"height":"var:preset|spacing|75"} -->
<div style="height:var(--wp--preset--spacing--75)" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer --></div>
<!-- /wp:group -->

<!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:columns {"style":{"spacing":{"padding":{"right":"var:preset|spacing|container","left":"var:preset|spacing|container"},"blockGap":{"left":"var:preset|spacing|70"}}}} -->
<div class="wp-block-columns" style="padding-right:var(--wp--preset--spacing--container);padding-left:var(--wp--preset--spacing--container)"><!-- wp:column {"verticalAlignment":"top","width":""} -->
<div class="wp-block-column is-vertically-aligned-top"><!-- wp:query {"queryId":31,"query":{"perPage":"5","pages":"6","offset":"0","postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"enhancedPagination":true,"layout":{"type":"default"}} -->
<div class="wp-block-query"><!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|50"}},"layout":{"type":"grid","columnCount":2}} -->
<!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide"><!-- wp:post-featured-image {"isLink":true,"align":"wide","style":{"border":{"radius":"20px"}}} /-->

<!-- wp:post-title {"isLink":true} /--></div>
<!-- /wp:group -->

<!-- wp:post-date {"style":{"elements":{"link":{"color":{"text":"var:preset|color|custom-pink"}}}},"textColor":"custom-pink"} /-->

<!-- wp:spacer {"height":"var:preset|spacing|40"} -->
<div style="height:var(--wp--preset--spacing--40)" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:post-excerpt /-->

<!-- wp:read-more {"style":{"spacing":{"padding":{"top":"0.25rem","bottom":"0.25rem","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}},"border":{"width":"0px","style":"none","radius":"20px"}},"gradient":"custom-main-gradiant"} /--></div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:spacer {"height":"var:preset|spacing|55"} -->
<div style="height:var(--wp--preset--spacing--55)" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:query-pagination {"className":"pagination","layout":{"type":"flex","justifyContent":"center"}} -->
<!-- wp:query-pagination-previous /-->

<!-- wp:query-pagination-numbers /-->

<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination --></div>
<!-- /wp:query --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:spacer {"height":"var:preset|spacing|75"} -->
<div style="height:var(--wp--preset--spacing--75)" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer --></main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->