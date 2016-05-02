<?php global $fps; ?>

<div id="fps" data-magellan-target="fps">
    <h4><i class='flaticon-xerox-machine'></i>File & Printing Services</h4>
    <span class='status-overview <?php echo status_overview($wc['percent_campus_aps_up']); ?>'>
        <?php echo $wc['num_campus_aps_up'] . '/' . $wc['num_campus_aps'] . ' Devices Online'; ?>
    </span>
    
    <table>
        <thead>
            <th>Fileserver Name</th>
            <th>Traffic</th>
            <th>Temperature</th>
            <th>Hard Drives</th>
            <th style="text-align: center;">Status</th>
        </thead>
        <tbody>

            <?php
                foreach ($fps['fileservers'] as $fs) {
                    echo '<tr>';
                        echo '<td>' . $fs['server_name'] . '</td>';
                        echo '<td>' . $fs['server_traffic'] . ' Mbit/s</td>';
                        echo '<td>' . $fs['server_temp'] . '</td>';
                        echo '<td>';
                            foreach ($fs['disks'] as $disk) {
                                if ($disk == 'Up') {
                                    echo '<i class="flaticon-checkmark-outlined-circular-button variable-status-icon" style="color: #3adb76; "></i>';
                                } elseif ($disk == 'Warning') {
                                    echo '<i class="flaticon-close variable-status-icon" style="color: #ffae00; "></i>';
                                } else {
                                    echo '<i class="flaticon-close variable-status-icon" style="color: #ec5840; "></i>';
                                }
                            }
                        echo '</td>';
                        echo '<td class="' . status_overview($fs['server_status']) . '">' . $fs['server_status'] . '</td>';
                    echo '</tr>';
                }
            ?>

        </tbody>
    </table>
</div>