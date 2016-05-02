<?php global $ss; ?>

<table class="snapshot-container">
    <thead>
        <tr class="snapshot-title">
            <th class="snapshot-logo"><i class='flaticon-security-camera'></i></th>
            <th><h6>Surveillance Services</h6></th>
            <th class="snapshot-overview <?php echo status_overview($ss['percent_ss_devices_up']) ?>"><div><?php echo $ss['num_ss_devices_up'] . '/' . $ss['num_ss_devices']; ?></div></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2">Surveillance Server</td>
            <td class="<?php echo status_overview($ss['ss_server_status']); ?>"><?php echo $ss['ss_server_status'] ?></td>
        </tr>
        <tr>
            <td colspan="2">Surveillance Cameras</td>
            <td class="<?php echo status_overview($ss['percent_ss_cameras_up']); ?>"><?php echo $ss['num_ss_cameras_up'] . '/' . $ss['num_ss_cameras']; ?></td>
        </tr>
    </tbody>
</table>