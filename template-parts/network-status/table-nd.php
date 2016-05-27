<?php global $nd; ?>


<div id="nd" data-magellan-target="nd">
    <h4><i class='flaticon-connection-system'></i>Network Distribution</h4>
    <span class='status-overview <?php echo status_overview($nd['percent_nonri_nd_devices_up']); ?>'>
        <?php echo $nd['num_nonri_nd_devices_up'] . '/' . $nd['num_nonri_nd_devices'] . ' Devices Online'; ?>
    </span>
    
    <table data-magellan>
        <thead>
            <tr>
                <th>Switch Name</th>
                <th class="hide-for-small-only">Response Time</th>
                <th width="13%" class="status-title">Status</th>
            </tr>
        </thead>    
        <tbody>
            <?php
                foreach ($nd['switches'] as $switch) {
                    echo '<tr>';
                        echo '<td>' . tip_lookup($switch['switch_name']) . '</td>';
                        echo '<td class="hide-for-small-only">' . $switch['switch_latency'] . '</td>';
                        echo "<td class='" . status_overview($switch['switch_status']) . "'>" . $switch['switch_status'] . '</td>';
                    echo '</tr>';
                }
            ?>
        </tbody>
    </table>
    
    <table data-magellan>
        <thead>
            <tr>
                <th>Wireless Backhaul Name</th>
                <th class="show-for-medium">Response Time</th>
                <th style="text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($nd['wb_devices'] as $wb) {
                echo '<tr>';
                    echo '<td>' . tip_lookup($wb['device_name']) . '</td>';
                    echo '<td class="show-for-medium">' . $wb['device_latency'] . '</td>';
                    echo '<td class="' . status_overview($wb['device_status']) . '">' . $wb['device_status'] . '</td>';
                echo '</tr>';
            }
            
            
            ?>
        </tbody>
    </table>
</div>