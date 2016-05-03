<?php global $ss; ?>

<div id="ss" data-magellan-target="ss">
    <h4><i class='flaticon-security-camera'></i>Surveillance Services</h4>
    <span class='status-overview <?php echo status_overview($ss['percent_ss_devices_up']); ?>'>
        <?php echo $ss['num_ss_devices'] . '/' . $ss['num_ss_devices_up'] . ' Devices Online'; ?>
    </span>
    
    <table>
        <thead>
            <th>Camera Name</th>
            <th width="16%" style="text-align: center;">Status</th>
        </thead>
        <tbody>

            <?php
                foreach ($ss['cameras'] as $camera) {
                    echo '<tr>';
                        echo '<td>' . tip_lookup($camera['camera_name']) . '</td>';
                        echo '<td class="' . status_overview($camera['camera_status']) . '">' . $camera['camera_status'] . '</td>';
                    echo '</tr>';
                }
            ?>

        </tbody>
    </table>
</div>