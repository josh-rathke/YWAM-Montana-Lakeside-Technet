<?php global $fps; ?>

<table class="snapshot-container">
    <thead>
        <tr class="snapshot-title">
            <th class="snapshot-logo"><i class='flaticon-xerox-machine'></i></th>
            <th><h6>File & Printing Services</h6></th>
            <th class="snapshot-overview <?php echo status_overview($fps['percent_fps_devices_up']) ?>"><div><?php echo $fps['num_fps_devices_up'] . '/' . $fps['num_fps_devices']; ?></div></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2">File Servers</td>
            <td class="<?php echo status_overview($fps['percent_fs_up']); ?>"><?php echo $fps['num_fs_up'] . '/' . $fps['num_fs']; ?></td>
        </tr>
        <tr>
            <td colspan="2">Printers</td>
            <td class="<?php echo status_overview($fps['percent_printers_up']); ?>"><?php echo $fps['num_printers_up'] . '/' . $fps['num_printers']; ?></td>
        </tr>
    </tbody>
</table>