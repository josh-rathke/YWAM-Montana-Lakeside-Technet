<?php 

/**
 *  Home Page Template
 *  Template Name: Home Page
 *
 */

get_header(); ?>

    <div class="hero-header" data-parallax="scroll" data-image-src="<?php echo wp_get_attachment_url( get_post_thumbnail_id($post->ID)); ?>">        
        <div class="hero-description row">
            <div class="hero-description-text">
                <h2>Spreading the Gospel</h2>
                <h3>One Internet Connection At A Time</h3>
            </div>
        </div>
    </div>

    <div class="hp-quick-links row">
        <div class="medium-10 columns medium-centered">
            <?php
            // Display Quick Links
            if( have_rows('quick_links') ):
                echo '<div class="medium-up-2 large-up-4" data-equalizer data-equalize-on="medium">';

                // loop through the rows of data
                while ( have_rows('quick_links') ) : the_row();

                    echo '<div class="column quick-link-container" data-equalizer-watch>';
                    // display a sub field value
                    echo '<h5 style="font-weight: bold; text-transform: uppercase;">' . get_sub_field('quick_link_title') . '</h5>';
                    the_sub_field('quick_link_description');
                    echo '<a href="' . get_sub_field('quick_link_page_link') . '" class="button expanded hollow">View Page</a>';

                    echo '</div>';

                endwhile;

                echo '</div>';
            else : endif; ?>
        </div>
    </div>

<?php get_footer(); ?>