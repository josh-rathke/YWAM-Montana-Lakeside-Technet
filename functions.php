<?php
/**
 * Author: Ole Fredrik Lie
 * URL: http://olefredrik.com
 *
 * FoundationPress functions and definitions
 *
 * Set up the theme and provides some helper functions, which are used in the
 * theme as custom template tags. Others are attached to action and filter
 * hooks in WordPress to change core functionality.
 *
 * @link https://codex.wordpress.org/Theme_Development
 * @package FoundationPress
 * @since FoundationPress 1.0.0
 */

/** Various clean up functions */
require_once( 'library/cleanup.php' );

/** Required for Foundation to work properly */
require_once( 'library/foundation.php' );

/** Register all navigation menus */
require_once( 'library/navigation.php' );

/** Add menu walkers for top-bar and off-canvas */
require_once( 'library/menu-walkers.php' );

/** Create widget areas in sidebar and footer */
require_once( 'library/widget-areas.php' );

/** Return entry meta information for posts */
require_once( 'library/entry-meta.php' );

/** Enqueue scripts */
require_once( 'library/enqueue-scripts.php' );

/** Add theme support */
require_once( 'library/theme-support.php' );

/** Add Nav Options to Customer */
require_once( 'library/custom-nav.php' );

/** Change WP's sticky post class */
require_once( 'library/sticky-posts.php' );

/** Configure responsive image sizes */
require_once( 'library/responsive-images.php' );

/** If your site requires protocol relative url's for theme assets, uncomment the line below */
// require_once( 'library/protocol-relative-theme-assets.php' );

require_once( 'library/prtg_interface.php' );

/**
 *  Define Status Overview Class
 *  This class determines what color the status overview
 *  is anytime its displayed.
 */
function status_overview($status) {
    if ($status == 100 || $status == 'Up') {
        return 'success-status';
    } elseif ($status > 90 && $status < 100 || $status == 'Warning') {
        return 'warning-status';
    } else {
        return 'alert-status';
    }
}

function tip_lookup($string) {
    
    // Check for Equipment Acronyms
    if(have_rows('equipment_acronyms') ):
        while( have_rows('equipment_acronyms') ) : the_row();
            
            // Find Lowercase Version of Acronym
            $acronym = get_sub_field('equipment_acronym');
            $lc_acronym = strtolower($acronym);
            
            // Replace Accronym if Found
            if (strpos($string, $acronym) !== false) {
                $string = preg_replace( "/\b{$acronym}\b/", "<a class='tip-link' href='#tip-{$lc_acronym}'>{$acronym}</a>", $string);
            }
    
        endwhile;
    else : endif;
    
    // Check for Location Acronyms
    if(have_rows('location_acronyms') ):
        while( have_rows('location_acronyms') ) : the_row();
            
            $acronym = get_sub_field('location_acronym');
            
            // Replace Accronym if Found
            if (strpos($string, $acronym) !== false) {
                $tooltip = "<span data-tooltip aria-haspopup='true' class='has-tip' data-disable-hover='false' tabindex='1' title='" . get_sub_field('location_name') . "'>" . $acronym . "</span>";
                $string = preg_replace( "/\b{$acronym}\b/", $tooltip, $string);
            }
    
        endwhile;
    else : endif;
    
    return $string;
}

function sanitize_traffic_kbit($raw_traffic) {
    return intval(str_replace(',', '', str_replace( ' kbit/s', '', $raw_traffic)));
}

function network_status_widget( $network_status = null ) {
    
    // Regenerate Object if Not Already Generated
    if ( is_null($network_status) ) {
        $network_status = new NetworkStatus();
    }?>

    <div class="network-status-widget">
        <table class="snapshot-container">
            <thead>
                <tr class="snapshot-title">
                    <th class="snapshot-logo"><i class='flaticon-connection-system'></i></th>
                    <th><h6>Network Status</h6></th>
                    <th class="snapshot-overview <?php echo status_overview($network_status->network_status['percent_devices_services_up']); ?>">
                        <div><?php echo $network_status->network_status['num_devices_services_up'] . '/' . $network_status->network_status['num_devices_services']; ?></div>
                    </th>
                </tr>
            </thead>
            <tbody class="network-status-widget-body">
                <tr>
                    <td style="text-align: center;"><i class="flaticon-checkmark-outlined-circular-button variable-status-icon"></i></td>
                    <td>
                        <?php 
                        if ($network_status->network_status['num_devices_services_up'] == 1 ) {
                            echo $network_status->network_status['num_devices_services_up'] . ' Device Up';
                        } else {
                            echo $network_status->network_status['num_devices_services_up'] . ' Devices Up';
                        } ?>
                    </td>
                        <?php
                        // Display the correct icon for network status
                        if ($network_status->network_status['percent_devices_services_up'] < 90) {
                            echo '<td rowspan="3" width="30%" class="network-status-widget-icon-container alert-status">';
                            echo '<i class="flaticon-close variable-status-icon"></i>';
                            echo '</td>';
                        } else {
                            echo '<td rowspan="3" width="30%" class="network-status-widget-icon-container success-status">';
                            echo '<i class="flaticon-checkmark-outlined-circular-button variable-status-icon"></i>';
                            echo '</td>';
                        } ?>
                </tr>
                <tr>
                    <td style="text-align: center;"><i class="flaticon-close variable-status-icon"></i></td>
                    <td>
                        <?php 
                        if ($network_status->network_status['num_devices_services_down'] == 1 ) {
                            echo $network_status->network_status['num_devices_services_down'] . ' Device Down';
                        } else {
                            echo $network_status->network_status['num_devices_services_down'] . ' Devices Down';
                        } ?>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center;"><i class="flaticon-arrow variable-status-icon"></i></td>
                    <td><?php echo number_format($network_status->network_status['percent_devices_services_up'], 2); ?>% Up</td>
                </tr>
                <tr>
                    <td colspan="3" class="network-status-widget-message">
                        <?php
                        // Display the correct icon for network status
                        if ( $network_status->network_status['percent_devices_services_up'] == 100) {
                            echo '<span style="color: #3adb76; font-weight: bold;">All systems are currently operational.</span>';
                        } elseif ( $network_status->network_status['percent_devices_services_up'] > 90 && $network_status->network_status['percent_devices_services_up'] < 100) {
                            echo '<span style="color: #3adb76; font-weight: bold;">Most systems are currently operational.</span><br />';
                            echo '<span style="color: #ffae00;">We are currently working out the few hiccups that exists.';
                        } elseif ( $network_status->network_status['percent_devices_services_up'] < 90 ) {
                            echo "<span style='color: #ec5840;'>Houston we have a problem. Don't worry though, we are aware and looking into it.</span>";
                        }?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
<?php } ?>