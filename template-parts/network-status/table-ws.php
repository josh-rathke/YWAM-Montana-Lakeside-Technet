<?php global $ws; ?>

<div id="ws" data-magellan-target="ws">
    <h4><i class='flaticon-computing-cloud-with-signal'></i>Web Services</h4>
    <span class='status-overview <?php echo status_overview($ws['percent_web_services_up']); ?>'>
        <?php echo $ws['num_web_services'] . '/' . $ws['num_web_services_up'] . ' Services Up'; ?>
    </span>
    
    <table>
        <thead>
            <th>Service Name</th>
            <th>Response Time</th>
            <th style="text-align: center;">Status</th>
        </thead>
        <tbody>

            <?php
                foreach ($ws['services'] as $service) {
                    echo '<tr>';
                        echo '<td>' . $service['service_name'] . '</td>';
                        echo '<td>' . $service['service_latency'] . '</td>';
                        echo '<td class="' . status_overview($service['service_status']) . '">' . $service['service_status'] . '</td>';
                    echo '</tr>';
                }
            ?>

        </tbody>
    </table>
</div>