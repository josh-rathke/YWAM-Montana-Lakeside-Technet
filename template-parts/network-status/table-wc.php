<?php global $wc; ?>

<div id="wc" data-magellan-target="wc">
    <h4><i class='flaticon-internet-full-signal'></i>Wireless Connectivity</h4>
    <span class='status-overview <?php echo status_overview($wc['percent_campus_aps_up']); ?>'>
        <?php echo $wc['num_campus_aps_up'] . '/' . $wc['num_campus_aps'] . ' Devices Online'; ?>
    </span>
    
    <table data-magellan>
        <thead>
            <th>Access Point Name</th>
            <td>Traffic</td>
            <th style="text-align: center;">Status</th>
        </thead>
        <tbody>

            <?php
                foreach ($wc['campus_wireless_aps'] as $ap) {
                    echo '<tr>';
                        echo '<td>' . tip_lookup($ap['ap_name']) . '</td>';
                        echo '<td>';
                            echo isset($ap['ap_traffic']) ? $ap['ap_traffic'] : '';
                        echo '</td>';
                        echo '<td class="' . status_overview($ap['ap_status']) . '">' . $ap['ap_status'] . '</td>';
                    echo '</tr>';
                }
            ?>

        </tbody>
    </table>
</div>