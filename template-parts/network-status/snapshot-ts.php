<?php global $ts; ?>

<table class="snapshot-container">
    <thead>
        <tr class="snapshot-title">
            <th class="snapshot-logo"><i class='flaticon-home-telephone-top-view'></i></th>
            <th><h6>Telephony Services</h6></th>
            <th class="snapshot-overview <?php echo status_overview($ts['percent_ts_devices_up']) ?>"><div><?php echo $ts['num_ts_devices_up'] . '/' . $ts['num_ts_devices']; ?></div></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2">Phone Server</td>
            <td class="<?php echo status_overview($ts['phone_server_status']); ?>"><?php echo $ts['phone_server_status'] ?></td>
        </tr>
        <tr>
            <td colspan="2">Voicemail Server</td>
            <td class="<?php echo status_overview($ts['voicemail_server_status']); ?>"><?php echo $ts['voicemail_server_status']; ?></td>
        </tr>
        <tr>
            <td colspan="2">IP Phones</td>
            <td class="<?php echo status_overview($ts['percent_ts_phones_up']); ?>"><?php echo $ts['num_ts_phones_up'] . '/' . $ts['num_ts_phones']; ?></td>
        </tr>
    </tbody>
</table>