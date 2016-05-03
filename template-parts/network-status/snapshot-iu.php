<?php
global $ic;

// Crunch Data for the Graph
$_24_total_traffic = 0;
foreach ($ic['24_hour_totals_data'] as $interval) {
    $_24_hour_data_labels[] = $interval['datetime'];
    $_24_hour_traffic_down[] = $interval['traffic_down'];
    $_24_hour_traffic_up[] = $interval['traffic_up'];

    $_24_total_traffic += $interval['total_traffic'];
}

$_24_hour_data_labels = implode('", "', $_24_hour_data_labels);
$_24_hour_traffic_down = implode(', ', $_24_hour_traffic_down);
$_24_hour_traffic_up = implode(', ', $_24_hour_traffic_up);


?>
<table class="snapshot-container" style="margin-bottom: 0px !important;" data-magellan>
    <thead>
        <tr class="snapshot-title">
            <th class="snapshot-logo"><i class='flaticon-graphic'></i></th>
            <th><h6>Daily Internet Usage</h6></th>
            <th class="snapshot-overview"><div><?php echo $_24_total_traffic; ?> GB</div></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="3">
                <canvas id="icUsageChart" width="300" height="120" style="margin: 20px 0px 0px -8px"></canvas>
                    <script>
                        var ctx = document.getElementById("icUsageChart");
                        var data = {
                            labels: ["<?php echo $_24_hour_data_labels; ?>"],
                            datasets: [
                                {
                                    label: "Download Traffic",
                                    backgroundColor: "#5D9ECB",

                                    borderWidth: 0,
                                    data: [<?php echo $_24_hour_traffic_down; ?>],
                                    tension: .35,
                                },
                                {
                                    label: "Upload Traffic",
                                    backgroundColor: "#3adb76",

                                    borderWidth: 0,
                                    data: [<?php echo $_24_hour_traffic_up; ?>],
                                    tension: .35,
                                },
                            ]
                        };

                        new Chart(ctx, {
                            data: data,
                            type: 'bar',
                            options: {
                                responsive: true,
                                legend: {
                                    display: false,
                                },
                                scales: {
                                    yAxes: [{
                                        ticks: {
                                            display: true,
                                        }
                                    }],
                                    xAxes: [{
                                        display: false,
                                    }]
                                },
                                tooltips: {
                                    callbacks: {
                                        afterBody: function() {
                                            return 'Measured in Gigabytes';
                                        }
                                    }
                                }
                            }
                        });
                    </script>
            </td>
        </tr>
    </tbody>
</table>

<table class='snapshot-container' data-magellan>
    <thead>
        <tr>
            <th>Connection Name</th>
            <th>Usage</th>
        </tr>
    </thead>
    <tbody>
        <?php
            foreach ($ic['wan_connections'] as $connection) {
                echo '<tr>';
                    echo '<td>' . tip_lookup($connection['connection_name']) . '</td>';
                    echo '<td>' . $connection['24_hour_traffic'] . ' GB</td>';
                echo '</tr>';
            }

        ?>
    </tbody>
</table>