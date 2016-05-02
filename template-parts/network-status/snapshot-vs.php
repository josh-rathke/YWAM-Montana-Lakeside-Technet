<?php global $vs; ?>

<table class="snapshot-container">
    <thead>
        <tr class="snapshot-title">
            <th class="snapshot-logo"><i class='flaticon-two-computer-connection'></i></th>
            <th><h6>Virtualization Services</h6></th>
            <th class="snapshot-overview <?php echo status_overview($vs['percent_vs_devices_up']) ?>"><div><?php echo $vs['num_vs_devices_up'] . '/' . $vs['num_vs_devices']; ?></div></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2">Virtualization Hosts</td>
            <td class="<?php echo status_overview($vs['percent_vs_hosts_up']); ?>"><?php echo $vs['num_vs_hosts_up'] . '/' . $vs['num_vs_hosts'] ?></td>
        </tr>
        <tr>
            <td colspan="2">Virtual Machines</td>
            <td class="<?php echo status_overview($vs['percent_vms_up']); ?>"><?php echo $vs['num_vms_up'] . '/' . $vs['num_vms']; ?></td>
        </tr>
    </tbody>
</table>