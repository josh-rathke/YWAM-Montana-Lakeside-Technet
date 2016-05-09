<?php
/**
 * The sidebar containing the main widget area
 *
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

?>
<aside class="sidebar">
	<?php network_status_widget(); ?>
    <?php echo do_shortcode('[gravityform id="5" title="true" description="true"]'); ?>
</aside>
