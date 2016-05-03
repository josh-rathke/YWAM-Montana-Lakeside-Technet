<?php global $ic; ?>

<table class="snapshot-container" style="margin-bottom: 0px !important;" data-magellan>
    <thead>
        <tr class="snapshot-title">
            <th class="snapshot-logo"><i class='flaticon-cloud-connection'></i></th>
            <th><h6>Internet Connectivity</h6></th>
            <th class="snapshot-overview <?php echo status_overview($ic['percent_wan_connections_up']); ?>"><div><?php echo $ic['num_wan_connections_up'] . '/' . $ic['num_wan_connections']; ?></div></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="3">
                <div class="ic-snapshot-traffic">
                    <div class="ic-snapshot-traffic-title">Traffic</div>
                    <div class="ic-snapshot-traffic-total"><?php echo $ic['total_traffic']; ?></div>
                    <div class="ic-snapshot-traffic-unit">Mbit/s</div>
                </div>
            </td>
        </tr>
    </tbody>
</table>
<table class="snapshot-container" data-magellan>
    <thead>
        <tr>
            <td>Internet Connection</td>
            <td width="20%">Status</td>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($ic['wan_connections'] as $connection) {
            echo '<tr>';
                echo '<td>' . tip_lookup($connection['connection_name']) . '</td>';
                echo '<td class="' . status_overview($connection['connection_status']) . '">' . $connection['connection_status'] . '</td>';
            echo '</tr>';
        } ?>
    </tbody>
</table>