<?php
/**
 * Title: index
 * Slug: digitiva/index
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
<h1 class="wp-block-heading has-text-align-center"><?php echo __('Blog', 'digitiva');?></h1>
<!-- /wp:heading -->

<!-- wp:spacer {"height":"var:preset|spacing|75"} -->
<div style="height:var(--wp--preset--spacing--75)" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer --></div>
<!-- /wp:group -->

<!-- wp:group {"layout":{"type":"default"}} -->
<div class="wp-block-group"><!-- wp:columns {"style":{"spacing":{"padding":{"right":"var:preset|spacing|container","left":"var:preset|spacing|container"}}}} -->
<div class="wp-block-columns" style="padding-right:var(--wp--preset--spacing--container);padding-left:var(--wp--preset--spacing--container)"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:query {"queryId":31,"query":{"perPage":6,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"enhancedPagination":true,"layout":{"type":"default"}} -->
<div class="wp-block-query"><!-- wp:post-template -->
<!-- wp:post-title {"isLink":true} /-->

<!-- wp:post-featured-image {"isLink":true,"align":"wide"} /-->

<!-- wp:post-excerpt /-->

<!-- wp:separator {"opacity":"css"} -->
<hr class="wp-block-separator has-css-opacity"/>
<!-- /wp:separator -->

<!-- wp:post-date /-->
<!-- /wp:post-template --></div>
<!-- /wp:query --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></main>
<!-- /wp:group -->

<!-- wp:spacer {"height":"var:preset|spacing|75"} -->
<div style="height:var(--wp--preset--spacing--75)" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->