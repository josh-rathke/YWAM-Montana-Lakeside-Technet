<?php global $vs; ?>

<div id="vs" data-magellan-target="vs">
    <h4><i class='flaticon-two-computer-connection'></i>Virtualization Services</h4>
    <span class='status-overview <?php echo status_overview($vs['percent_vs_devices_up']); ?>'>
        <?php echo $vs['num_vs_devices_up'] . '/' . $vs['num_vs_devices'] . ' Systems Available'; ?>
    </span>
    
    <table>
        <thead>
            <tr>
                <th>Virtualization Host</th>
                <th class="hide-for-small-only">Current Usage</th>
                <th width="13%" class="status-title">Status</th>
            </tr>
        </thead>    
        <tbody>
            <?php
                foreach ($vs['virtualization_hosts'] as $host) {
                    echo '<tr>';
                        echo '<td>' . tip_lookup($host['host_name']) . '</td>';
                        echo '<td class="hide-for-small-only">' . $host['host_cpu_usage'] . '</td>';
                        echo "<td class='" . status_overview($host['host_status']) . "'>" . $host['host_status'] . '</td>';
                    echo '</tr>';
                }
            ?>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>Virtual Machine</th>
                <th class="hide-for-small-only">Current Usage</th>
                <th width="13%" class="status-title">Status</th>
            </tr>
        </thead>    
        <tbody>
            <?php
                foreach ($vs['virtual_machines'] as $vm) {
                    echo '<tr>';
                        echo '<td>' . tip_lookup($vm['vm_name']) . '</td>';
                        echo '<td class="hide-for-small-only">' . $vm['vm_usage'] . '</td>';
                        echo "<td class='" . status_overview($vm['vm_status']) . "'>" . $vm['vm_status'] . '</td>';
                    echo '</tr>';
                }
            ?>
        </tbody>
    </table>
</div>