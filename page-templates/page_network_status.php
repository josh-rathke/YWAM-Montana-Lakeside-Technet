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

print_r($fps);

get_header();
        
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
    $string = str_replace('STA', '<a class="tip-link" href="#tip-sta">STA</a>', $string);
    $string = str_replace('AP', '<a class="tip-link" href="#tip-ap">AP</a>', $string);
    $string = str_replace('WAN', '<a class="tip-link" href="#tip-wan">WAN</a>', $string);
    return $string;
}

function sanitize_traffic_kbit($raw_traffic) {
    return intval(str_replace(',', '', str_replace( ' kbit/s', '', $raw_traffic)));
}

?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.0.0/Chart.js"></script>

<div class="template-network-status">

    <div class="row">
        <div class="medium-8 columns">
            <h1>Network Status</h1>
            <p>Ever wish you could check and see if the wifi is down? Well wish no longer, below you'll find all of the information you could ever want to know regarding the status of the network. Information represented may be slightly behind realtime due to each sensor's checkup interval, but the information should be representative of the last 60 seconds. Don't worry about refreshing the page, it will refresh itself every 20 seconds.</p>
        </div>
        <div class="medium-4 columns">
            Some stuff
        </div>
    </div>
    
    <div class="row">
        <div class="large-4 medium-6 columns">
            <?php get_template_part('template-parts/network-status/snapshot' , 'ic'); // Internet Connectivity Snapshot ?>
            <?php get_template_part('template-parts/network-status/snapshot' , 'ws') // Web Services Snapshot ?>
            <?php get_template_part('template-parts/network-status/snapshot' , 'vs') // Virtualization Services Snapshot ?>
        </div>

        <div class="large-4 medium-6 columns">
            <?php get_template_part('template-parts/network-status/snapshot' , 'iu') // Internet Usage Snapshot ?>
            <?php get_template_part('template-parts/network-status/snapshot' , 'nd') // Network Distribution Snapshot ?>
            <?php get_template_part('template-parts/network-status/snapshot' , 'ts') // Telephony Services Snapshot ?>
        </div>

        <div class="large-4 medium-6 columns">
            <?php get_template_part('template-parts/network-status/snapshot' , 'wc') // Wireless Connectivity Snapshot ?>
            <?php get_template_part('template-parts/network-status/snapshot' , 'ss') // Surveillance Services Snapshot ?>
        </div>
    </div>

    <div class="row">
        <div class="medium-12 columns">
            <div class="device-drilldown">
                <h2 class="device-drilldown-title">Devices & Services Drilldown</h2>
            </div>
        </div>
    </div>


    <div class="row">
        <div id="tablesContainer" class="large-8 columns">    

        <?php
        get_template_part('template-parts/network-status/table', 'ic'); // Internet Connectivity Table
        get_template_part('template-parts/network-status/table', 'ws'); // Web Services Table
        get_template_part('template-parts/network-status/table', 'nd'); // Network Distribution
        get_template_part('template-parts/network-status/table', 'wc'); // Wireless Connectivity
        get_template_part('template-parts/network-status/table', 'vs'); // Virtual Services Table
        get_template_part('template-parts/network-status/table', 'fps');// File & Printing Services Table
        get_template_part('template-parts/network-status/table', 'ts'); // Telephony Services Table
        get_template_part('template-parts/network-status/table', 'ri'); // Residential Infrastructure Table
        get_template_part('template-parts/network-status/table', 'ss'); // Surveillance Services Table
        ?>


            <div class="tip-container">
                <section id="tip-sta" data-magellan-target="tip-sta">
                    <h5 class="tip"><i class="flaticon-technology-1"></i> STA - Station</h5>
                    <p>A station (STA) is an endpoint in a Point to Point (PtP), or a Point to Multi-Point (PtMP) directional link. In most cases this provides some sort of network connectivity to the structure it is installed on.</p>
                </section>

                <section id="tip-ap" data-magellan-target="tip-ap">
                    <h5 class="tip"><i class="flaticon-technology-2"></i> AP - Access Point</h5>
                    <p>An access point (AP) is a device that, typically, transmits a signal that can be used by any WiFi compatible client to provide network connectivity. All wireless clients whether it be a phone, computer or a tablet access the network through a wireless access point.</p>
                </section>

                <section id="tip-wan" data-magellan-target="tip-wan">
                    <h5 class="tip"><i class="flaticon-technology-3"></i> WAN - Wide Area Network</h5>
                    <p>A wide area network (WAN) is a telecommunications network or computer network that extends over a large geographical distance. These types of connections are most commonly known as internet connections that provide routing to publicly available services.</p>
                </section>
            </div>


        </div>


        <div class="large-4 columns ns-sidebar" data-sticky-container>
            <div class="sticky sticky-sidebar" data-sticky data-anchor="tablesContainer">
                <table class="medium-12 columns" data-magellan>
                    <tr>
                        <td class="icon-container"><i class='flaticon-cloud-connection'></i></td>
                        <td><a href="#ic">Internet Connectivity</a></td>
                        <td class="success-status status-container">999/999</td>
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
                        <td class="status-container <?php echo status_overview($wc['percent_campus_aps_up']) ?>">
                            <?php echo $wc['num_campus_aps_up'] . '/' . $wc['num_campus_aps']; ?>
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
                        <td class="success-status status-container">999/999</td>
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