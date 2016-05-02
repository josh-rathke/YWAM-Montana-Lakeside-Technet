<?php global $ws; ?>

<table class="snapshot-container">
    <thead>
        <tr class="snapshot-title">
            <th class="snapshot-logo"><i class='flaticon-computing-cloud-with-signal'></i></th>
            <th><h6>Web Services</h6></th>
            <th class="snapshot-overview <?php echo status_overview($ws['percent_web_services_up']) ?>"><div><?php echo $ws['num_web_services_up'] . '/' . $ws['num_web_services']; ?></div></th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($ws['services'] as $service) {
            echo '<tr>';
                echo '<td colspan="2">' . $service['service_name'] . '</td>';
                echo '<td class="' . status_overview($service['service_status']) . '">' . $service['service_status'] . '</td>';
            echo '</tr>';
        }
        
        ?>
    </tbody>
</table>