<?php global $ic; ?>

<div id="ic" data-magellan-target="ic">
    <h4><i class='flaticon-cloud-connection'></i>Internet Connectivity</h4>
    <span class='status-overview <?php echo status_overview($ic['percent_wan_connections_up']); ?>'>
        <?php echo $ic['num_wan_connections'] . '/' . $ic['num_wan_connections_up'] . ' Connections Available'; ?>
    </span>

    <table data-magellan>
        <thead>
            <tr>
                <th>Connection</th>
                <th class="hide-for-small-only">Current Traffic</th>
                <th class="status-title">Status</th>
            </tr>
        </thead>    
        <tbody>
            <?php
                foreach ($ic['wan_connections'] as $connection) {
                    echo '<tr>';
                        echo '<td>' . tip_lookup($connection['connection_name']) . '</td>';
                        echo '<td class="hide-for-small-only">' . $connection['connection_traffic'] . '</td>';
                        echo "<td class='" . status_overview($connection['connection_status']) . "'>" . $connection['connection_status'] . '</td>';
                    echo '</tr>';
                }
            ?>
        </tbody>
    </table>
</div>