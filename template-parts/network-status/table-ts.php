<?php global $ts; ?>

<div id="ts" data-magellan-target="ts">
    <h4><i class='flaticon-home-telephone-top-view'></i>Telephony Services</h4>
    <span class='status-overview <?php echo status_overview($ts['percent_ts_devices_up']); ?>'>
        <?php echo $ts['num_ts_devices_up'] . '/' . $ts['num_ts_devices'] . ' Devices Online'; ?>
    </span>
    
    <table data-magellan>
        <thead>
            <th>IP Phone Name</th>
            <td>Extension</td>
            <th class="show-for-medium" style="text-align: center;">Voicemail</th>
            <th style="text-align: center;">Status</th>
        </thead>
        <tbody>

            <?php
                foreach ($ts['phones'] as $phone) {
                    echo '<tr>';
                        echo '<td>' . tip_lookup($phone['phone_name']) . '</td>';
                        echo '<td>' . $phone['extension'] . '</td>';
                        echo '<td class="show-for-medium" style="text-align: center;">';
                            echo $phone['voicemail'] == 1 ? "<i class='status-icon flaticon-checkmark-outlined-circular-button'></i>" : "";
                        echo '</td>';
                        echo '<td class="' . status_overview($phone['phone_status']) . '">' . $phone['phone_status'] . '</td>';
                    echo '</tr>';
                }
            ?>

        </tbody>
    </table>
</div>