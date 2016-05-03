<?php global $fps; ?>

<div id="fps" data-magellan-target="fps">
    <h4><i class='flaticon-xerox-machine'></i>File & Printing Services</h4>
    <span class='status-overview <?php echo status_overview($fps['percent_fps_devices_up']); ?>'>
        <?php echo $fps['num_fps_devices_up'] . '/' . $fps['num_fps_devices'] . ' Devices Online'; ?>
    </span>
    
    <table data-magellan>
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
    
    <table data-magellan>
        <thead>
            <th>Printer Name</th>
            <th>Pages Printed</th>
            <th>Status Message</th>
            <th style="text-align: center;">Status</th>
        </thead>
        <tbody>

            <?php
                foreach ($fps['printers'] as $printer) {
                    echo '<tr>';
                        echo '<td>' . $printer['printer_name'] . '</td>';
                        echo '<td>' . $printer['printer_pages'] . '</td>';
                        echo '<td>';
                            echo '<span data-tooltip aria-haspopup="true" class="has-tip" data-disable-hover="false" tabindex="1" title="' . $printer['printer_status_msg'] . '">View Message</span>';
                        echo '</td>';
                        echo '<td class="' . status_overview($printer['printer_status']) . '">' . $printer['printer_status'] . '</td>';
                    echo '</tr>';
                }
            ?>

        </tbody>
    </table>
</div>