<?php global $ri; ?>

<div id="ri" data-magellan-target="ri">
    <h4><i class='flaticon-shelter'></i>Residential Infrastructure</h4>
    <span class='status-overview <?php echo status_overview($ri['percent_ri_devices_up']); ?>'>
        <?php echo $ri['num_ri_devices_up'] . '/' . $ri['num_ri_devices'] . ' Devices Online'; ?>
    </span>

    <table data-magellan>
        <thead>
            <tr>
                <td>Sector Name</td>
                <td>Traffic</td>
                <td class="show-for-medium">Uptime</td>
                <td width="15%">Status</td>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach($ri['sectors'] as $sector) {
                echo '<tr>';
                    echo '<td>' . tip_lookup($sector['sector_name']) . '</td>';
                    echo '<td>' . $sector['sector_traffic'] . '</td>';
                    echo '<td class="show-for-medium">' . $sector['sector_uptime'] . '%</td>';
                    echo '<td class="' . status_overview($sector['sector_status']) . '">' . $sector['sector_status'] . '</td>';
                echo '</tr>';
            }

            ?>
        </tbody>
    </table>
    <table data-magellan>
        <thead>
            <tr>
                <th class="show-for-large">House</th>
                <th>Device</th>
                <th class="hide-for-small-only">Uptime</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>

            <?php
            $residence_counter = 0; // Set Residence Counter
            foreach ($ri['residences'] as $residence) {
                $residence_class = $residence_counter % 2 == 0 ? 'even_cell' : 'odd_cell';
                $device_counter = 0; // Reset Counter on each Residence

                echo '<tr>';
                    echo "<td class='{$residence_class} show-for-large' rowspan='2'>" . $residence['residence_name'] . '</td>';

                foreach ($residence['devices'] as $device) {
                    echo $device_counter == 0 ? '' : '</tr>';
                        $device['device_name'] = tip_lookup($device['device_name']);

                        echo '<td>' . $device['device_name'] . '</td>';  
                        echo '<td class="hide-for-small-only">' . $device['device_uptime'] . '%</td>';
                        echo "<td class='" . status_overview($device['device_status']) . "'>" . $device['device_status'] . '</td>';


                    echo '</tr>';

                    $device_counter++;
                }
                $residence_counter++;
            }
            ?>

        </tbody>
    </table>
</div>