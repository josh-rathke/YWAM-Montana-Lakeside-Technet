<?php global $wc; ?>

<table class="snapshot-container" style="margin-bottom: 0px !important;">
    <thead>
        <tr class="snapshot-title">
            <th class="snapshot-logo"><i class='flaticon-internet-full-signal'></i></th>
            <th><h6>Wireless Connectivity</h6></th>
            <th class="snapshot-overview <?php echo status_overview($wc['percent_wireless_aps_up']); ?>"><div><?php echo $wc['num_wireless_aps_up'] . '/' . $wc['num_wireless_aps']; ?></div></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="3">
                <canvas id="myChart" width="300" height="120" style="margin: 20px 0px 0px -8px"></canvas>
                    <script>
                        var ctx = document.getElementById("myChart");
                        var data = {
                            labels: ["YWAM | General", "YWAM | Staff", "100 Fold Studio", "YWAM Residential", "MBI | General", "MBI | Staff"],
                            datasets: [
                                {
                                    label: "Wireless Traffic",
                                    backgroundColor: "#5D9ECB",

                                    borderColor: "#888888",
                                    borderWidth: .1,

                                    pointBorderColor: "#F9F9F9",
                                    pointBackgroundColor: "#5D9ECB",
                                    pointBorderWidth: 1.5,

                                    data: [<?php echo $wc['js_traffic_string']; ?>],
                                    tension: 0,
                                },
                            ]
                        };

                        new Chart(ctx, {
                            data: data,
                            type: 'line',
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
                                }
                            }
                        });
                    </script>
            </td>
        </tr>
    </tbody>
</table>

<table class="snapshot-container">
    <thead>
        <tr>
            <td>Access Points</td>
            <td width="20%">Status</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>YWAM Montana | Campus</td>
            <td class="<?php echo status_overview($wc['percent_ywam_aps_up']); ?>">
                <?php echo $wc['num_ywam_aps_up'] . '/' . $wc['num_ywam_aps']; ?>
            </td>
        </tr>

        <tr>
            <td>YWAM Montana | Residential</td>
            <td class="<?php echo status_overview($wc['percent_ri_aps_up']); ?>">
                <?php echo $wc['num_ri_aps_up'] . '/' . $wc['num_ri_aps']; ?>
            </td>
        </tr>

        <tr>
            <td>Mission Builders</td>
            <td class="<?php echo status_overview($wc['percent_mbi_aps_up']); ?>">
                <?php echo $wc['num_mbi_aps_up'] . '/' . $wc['num_mbi_aps']; ?>
            </td>
        </tr>
    </tbody>
</table>