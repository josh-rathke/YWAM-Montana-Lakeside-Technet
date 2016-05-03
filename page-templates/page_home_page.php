<?php 

/**
 *  Home Page Template
 *  Template Name: Home Page
 *
 */

get_header(); ?>

    <div class="hero-header" data-parallax="scroll" data-image-src="http://localhost:8888/technet.ywammontana.org/wp-content/uploads/2016/05/photo-1448960968772-b63b3f40dfc1.jpeg">        
        <div class="hero-description row">
            <div class="hero-description-text">
                <h2>Spreading the Gospel</h2>
                <h3>One Internet Connection At A Time</h3>
            </div>
        </div>
    </div>

    <div class="hp-quick-links">
    <?php
    // Display Quick Links
    if( have_rows('quick_links') ):
        echo '<div class="row medium-up-3 large-up-4" data-equalizer data-equalize-on="medium">';

        // loop through the rows of data
        while ( have_rows('quick_links') ) : the_row();

            echo '<div class="column quick-link-container" data-equalizer-watch>';
            // display a sub field value
            echo '<h4>' . get_sub_field('quick_link_title') . '</h4>';
            the_sub_field('quick_link_description');
            echo '<a href="' . get_sub_field('quick_link_page_link') . '" class="button expanded hollow">View Page</a>';
            
            echo '</div>';

        endwhile;
        
        echo '</div>';
    else : endif; ?>
    </div>

<?php get_footer(); ?>