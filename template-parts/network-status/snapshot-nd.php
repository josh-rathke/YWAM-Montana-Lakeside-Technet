<?php global $ic, $nd; ?>


<table class="snapshot-container">
    <thead>
        <tr class="snapshot-title">
            <th class="snapshot-logo"><i class='flaticon-connection-system'></i></th>
            <th><h6>Network Distribution</h6></th>
            <th class="snapshot-overview <?php echo status_overview($nd['percent_nd_devices_up']); ?>"><div><?php echo $nd['num_nd_devices_up'] . '/' . $nd['num_nd_devices']; ?></div></th>
        </tr>
    </thead>
    <tbody>

        <tr>
            <td colspan="2">Core Router</td>
            <td class="<?php echo status_overview($ic['core_router_status']); ?>"><?php echo $ic['core_router_status']; ?></td>
        </tr>
        <tr>
            <td colspan="2">Core Switch</td>
            <td class="<?php echo status_overview($nd['core_switch_status']); ?>"><?php echo $nd['core_switch_status']; ?></td>
        </tr>
        <tr>
            <td colspan="2">Distribution Switches</td>
            <td class="snapshot-status <?php echo status_overview($nd['percent_dist_switches_up']); ?>"><?php echo $nd['num_dist_switches'] . '/' . $nd['num_dist_switches_up']; ?></td>
        </tr>
        <tr>
            <td colspan="2">Wireless Backhaul Devices</td>
            <td class="snapshot-status <?php echo status_overview($nd['percent_wb_devices_up']); ?>"><?php echo $nd['num_wb_devices'] . '/' . $nd['num_wb_devices_up']; ?></td>
        </tr>
    </tbody>
</table>