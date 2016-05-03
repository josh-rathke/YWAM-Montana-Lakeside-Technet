<?php
/**
 * Network Status Monitor
 * This network status monitor is set up to interface
 * with PRTG and use information provided to display a
 * simple responsive view of the status of network devices.
 * 
 * Template Name: Network Status
 */

// Build the Network Status object.
$network_status = new NetworkStatus();

// Filter out specific Object Properties for quicker access.
$vs = $network_status->virtualization_services;
$ic = $network_status->internet_connectivity;
$nd = $network_status->network_distribution;
$wc = $network_status->wireless_connectivity;
$ri = $network_status->residential_infrastructure;
$ts = $network_status->telephony_services;
$ws = $network_status->web_services;
$ss = $network_status->surveillance_services;
$fps = $network_status->file_printing_services;

get_header(); ?>

<!-- <meta http-equiv="refresh" content="20" /> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.0.0/Chart.js"></script>

<div class="template-network-status">

    <div class="row">
        <div class="medium-8 columns">
            <h1>Network Status <sup style="color: #3adb76; font-weight: bold; font-size: 16px; top: -1.25em; text-transform: uppercase;">Beta</sup></h1>
            <p>Ever wish you could check and see if the wifi is down? Well wish no longer, below you'll find a comprehensive list regarding the status of different parts of our network. Information represented may be slightly behind realtime due to each sensor's checkup interval, but the information for the most part should be representative of the last 60 seconds. Don't worry about refreshing the page, it will refresh itself every 20 seconds.</p>
        </div>
        <div class="medium-4 columns">
            <?php network_status_widget($network_status); ?>
        </div>
    </div>
    
    <div class="column row">
        <div class="network-status-hr">
            <h2 class="network-status-hr-title">Network Status Dashboard</h2>
        </div>
    </div>
    
    <div class="row">
        <div class="large-4 medium-6 columns">
            <?php get_template_part('template-parts/network-status/snapshot', 'ic'); // Internet Connectivity Snapshot ?>
            <?php get_template_part('template-parts/network-status/snapshot', 'ws') // Web Services Snapshot ?>
            <?php get_template_part('template-parts/network-status/snapshot', 'vs') // Virtualization Services Snapshot ?>
        </div>

        <div class="large-4 medium-6 columns">
            <?php get_template_part('template-parts/network-status/snapshot', 'iu') // Internet Usage Snapshot ?>
            <?php get_template_part('template-parts/network-status/snapshot', 'nd') // Network Distribution Snapshot ?>
            <?php get_template_part('template-parts/network-status/snapshot', 'ts') // Telephony Services Snapshot ?>
        </div>

        <div class="large-4 medium-6 columns">
            <?php get_template_part('template-parts/network-status/snapshot', 'wc') // Wireless Connectivity Snapshot ?>
            <?php get_template_part('template-parts/network-status/snapshot', 'ss') // Surveillance Services Snapshot ?>
            <?php get_template_part('template-parts/network-status/snapshot', 'fps') // File & Printing Services Snapshot ?>
        </div>
    </div>

    <div class="column row">
        <div class="network-status-hr">
            <h2 class="network-status-hr-title">Devices & Services Drilldown</h2>
        </div>
    </div>


    <div class="row">
        <div id="tablesContainer" class="large-8 columns">    

        <?php
    
        /**
         *  Display Device Drilldown Tables
         *  Here we'll pull in all the device drilldown tables.
         */
        get_template_part('template-parts/network-status/table', 'ic'); // Internet Connectivity Table
        get_template_part('template-parts/network-status/table', 'ws'); // Web Services Table
        get_template_part('template-parts/network-status/table', 'nd'); // Network Distribution
        get_template_part('template-parts/network-status/table', 'wc'); // Wireless Connectivity
        get_template_part('template-parts/network-status/table', 'vs'); // Virtual Services Table
        get_template_part('template-parts/network-status/table', 'fps');// File & Printing Services Table
        get_template_part('template-parts/network-status/table', 'ts'); // Telephony Services Table
        get_template_part('template-parts/network-status/table', 'ri'); // Residential Infrastructure Table
        get_template_part('template-parts/network-status/table', 'ss'); // Surveillance Services Table

        /**
         *  Display Tip Sections
         *  These sections allow the user of the web app to gather
         *  a little more information on the acronyms used within the page.
         */
        if(have_rows('equipment_acronyms') ):
            echo '<div class="tip-container">';
            while( have_rows('equipment_acronyms') ) : the_row();  ?>

                <section id="tip-<?php echo strtolower(get_sub_field('equipment_acronym')); ?>" data-magellan-target="tip-<?php echo strtolower(get_sub_field('equipment_acronym')); ?>">
                    <h5 class="tip"><?php echo get_sub_field('equipment_acronym') . ' - ' . get_sub_field('equipment_name'); ?></h5>
                    <?php the_sub_field('equipment_description'); ?>
                </section>

            <?php endwhile;
            echo '</div>';
        else : endif; ?>


        </div>


        <div class="large-4 columns ns-sidebar" data-sticky-container>
            <div class="sticky sticky-sidebar" data-sticky data-anchor="tablesContainer">
                <table class="medium-12 columns" data-magellan>
                    <tr>
                        <td class="icon-container"><i class='flaticon-cloud-connection'></i></td>
                        <td><a href="#ic">Internet Connectivity</a></td>
                        <td class="status-container <?php echo status_overview($ic['percent_wan_connections_up']); ?>">
                            <?php echo $ic['num_wan_connections_up'] . '/' . $ic['num_wan_connections']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="icon-container"><i class='flaticon-computing-cloud-with-signal'></i></td>
                        <td><a href="#ws">Web Services</a></td>
                        <td class="status-container <?php echo status_overview($ws['percent_web_services_up']) ?>">
                            <?php echo $ws['num_web_services_up'] . '/' . $ws['num_web_services']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="icon-container"><i class='flaticon-connection-system'></i></td>
                        <td><a href="#nd">Network Distribution</a></td>
                        <td class="status-container <?php echo status_overview($nd['percent_nonri_nd_devices_up']) ?>">
                            <?php echo $nd['num_nonri_nd_devices_up'] . '/' . $nd['num_nonri_nd_devices']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="icon-container"><i class='flaticon-internet-full-signal'></i></td>
                        <td><a href="#wc">Wireless Connectivity</a></td>
                        <td class="status-container <?php echo status_overview($wc['percent_campus_aps_up']) ?>">
                            <?php echo $wc['num_campus_aps_up'] . '/' . $wc['num_campus_aps']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="icon-container"><i class='flaticon-two-computer-connection'></i></td>
                        <td><a href="#vs">Virtualization Services</a></td>
                        <td class="status-container <?php echo status_overview($vs['percent_vs_devices_up']) ?>">
                            <?php echo $vs['num_vs_devices_up'] . '/' . $vs['num_vs_devices']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="icon-container"><i class='flaticon-xerox-machine'></i></td>
                        <td><a href="#fps">File & Printing Services</a></td>
                        <td class="status-container <?php echo status_overview($fps['percent_fps_devices_up']) ?>">
                            <?php echo $fps['num_fps_devices_up'] . '/' . $fps['num_fps_devices']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="icon-container"><i class='flaticon-home-telephone-top-view'></i></td>
                        <td><a href="#ts">Telephony Services</a></td>
                        <td class="status-container <?php echo status_overview($ts['percent_ts_devices_up']) ?>">
                            <?php echo $ts['num_ts_devices_up'] . '/' . $ts['num_ts_devices']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="icon-container"><i class='flaticon-shelter'></i></td>
                        <td><a href="#ri">Residential Infrastructure</a></td>
                        <td class="status-container <?php echo status_overview($ri['percent_ri_devices_up']); ?>">
                            <?php echo $ri['num_ri_devices_up'] . '/' . $ri['num_ri_devices'] ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="icon-container"><i class='flaticon-security-camera'></i></td>
                        <td><a href="#ss">Surveillance Services</a></td>
                        <td class="status-container <?php echo status_overview($ss['percent_ss_devices_up']) ?>">
                            <?php echo $ss['num_ss_devices_up'] . '/' . $ss['num_ss_devices']; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>