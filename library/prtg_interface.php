<?php

class NetworkStatus {
    
    var $retrieved_xml;
    var $virtualization_services;
    var $internet_connectivity;
    var $network_distribution;
    var $wireless_connectivity;
    var $residential_infrastructure;
    var $telephony_services;
    var $web_services;
    var $surveillance_services;
    var $file_printing_services;
    
    /**
     *  Open A Connection to the PRTG Server
     *  This function will open a connection to the PRTG
     *  server and download the necessary XML file to begin
     *  sorting through the data.
     */
    public function retrieve_xml() {
        
        // Server settings from environment variables.
        global $protocol, $server, $port, $username, $passhash;
        
        $protocol = $_SERVER[ 'NETWORKSTATUS_PROTOCOL' ];
        $server = $_SERVER['NETWORKSTATUS_SERVER' ];
        $port = $_SERVER[ 'NETWORKSTATUS_SERVERPORT' ];
        $username = $_SERVER[ 'NETWORKSTATUS_USERNAME' ];
        $passhash = $_SERVER[ 'NETWORKSTATUS_PASSHASH' ];

        // Define Socket and File Information.
        global $socket, $credentials, $arrContextOptions;
        
        $socket = "{$protocol}://{$server}:{$port}";
        $credentials = "username={$username}&passhash={$passhash}";
        $filename = "{$socket}/api/table.xml?content=sensortree&{$credentials}";

        $arrContextOptions = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
        );

        if (($response_xml_data = file_get_contents("{$filename}", false, stream_context_create($arrContextOptions)))===false) {
            echo "Error fetching XML\n";
        } else {
           libxml_use_internal_errors(true);
           $data = simplexml_load_string($response_xml_data);
           if (!$data) {
               echo "Error loading XML\n";
               foreach(libxml_get_errors() as $error) {
                   echo "\t", $error->message;
               }
           }
        }
        
        // Trim the XML Document down to just the groups needed.
        $this->retrieved_xml = $data->sensortree->nodes->group->probenode;
    }
    
    /**
     *  Calculate Historic Data Dates
     *  This function will take the current date and retrieve data
     *  as far back as specified.
     */
    public function calculate_historic_data_dates($days_to_go_back) {
        // Get Current Date
        date_default_timezone_set ( 'America/Denver' );
        $cur_date = getdate();
        // Simplify Current Date to be Analyzed.
        $date = $cur_date['year'] . '-' . sprintf("%02d", $cur_date['mon']) . '-' . sprintf("%02d", $cur_date['mday']);
        // Define Time
        $time = sprintf("%02d", $cur_date['hours']) . '-' . sprintf("%02d", $cur_date['minutes']) . '-' . $cur_date['seconds'];
        // Subtract 1 Day, and Explode Parts into Array.
        $prev_date = date("Y-m-d", strtotime("{$date} -{$days_to_go_back} days"));
        
        $dates['end_date'] = $date . '-' . $time;
        $dates['start_date'] = $prev_date . '-' . $time;
        
        return $dates;
    }
    
    /**
     *  Build Historic Data Model
     *  This will query and retrieve a historic data set from PRTG and
     *  return the object in an XML format for use.
     */
    public function retrieve_historic_data($sensor_id, $start_date, $end_date, $avg) {
        
        // Define Connection Settings
        $historic_data = "{$GLOBALS['socket']}/api/historicdata.xml?";
        $query_string = "id={$sensor_id}&sdate={$start_date}&edate={$end_date}&avg={$avg}&{$GLOBALS['credentials']}";
        
        $filename = $historic_data . $query_string;
        
        if (($historic_data = file_get_contents("{$filename}", false, stream_context_create($GLOBALS['arrContextOptions'])))===false) {
            echo "Error fetching XML\n";
        } else {
           libxml_use_internal_errors(true);
           $historic_data = simplexml_load_string($historic_data);
           if (!$historic_data) {
               echo "Error loading XML\n";
               foreach(libxml_get_errors() as $error) {
                   echo "\t", $error->message;
               }
           }
        }
        
        return $historic_data;
    }
    
    
    /**
     *  Trim XML to Necessary Components
     *  This function extracts a piece of the retrieved XML document,
     *  and only returns the necessary components to cut down on code.
     */
    public function filter_object($parameter) {
        
        // Define Network Infrastructure Group
        foreach ($this->retrieved_xml->group as $group) {
            $network_infrastructure = $group->name == 'Network Infrastructure' ? $group : null;
        }
        
        // Return only the requested group.
        if ($parameter == 'Network Infrastructure') {
            return $network_infrastructure;
        } else {
            foreach($network_infrastructure->group as $group) {
                if ($group->name == $parameter) { return $group; }
            }
        }
    }
    
    public function virtualization_services() {
        $virtualization_services = $this->filter_object('Virtual Systems');
        
        $this->virtualization_services['num_vs_devices'] = 0;
        $this->virtualization_services['num_vs_devices_down'] = 0;
        $this->virtualization_services['num_vs_hosts'] = 0;
        $this->virtualization_services['num_vs_hosts_down'] = 0;
        $this->virtualization_services['num_vms'] = 0;
        $this->virtualization_services['num_vms_down'] = 0;
        
        // Find VMWare Group
        foreach ($virtualization_services->group as $group) {
            if ($group->name == 'VMWare') {
                
                $vs_index = 0;
                foreach ($group->device as $device) {
                    $this->virtualization_services['num_vs_devices']++;
                    
                    if (strpos($device->name, 'YWAMMT-ESXi') !== false) {
                        $this->virtualization_services['num_vs_hosts']++;
                        
                        foreach ($device->sensor as $sensor) {
                            if ($sensor->name == 'PING') {
                                
                                $this->virtualization_services['virtualization_hosts'][$vs_index]['host_name'] = (string) $device->name;
                                $this->virtualization_services['virtualization_hosts'][$vs_index]['host_status'] = (string) $sensor->status;
                                
                                if ($sensor->status == 'Down') {
                                    $this->virtualization_services['num_vs_devices_down']++;
                                    $this->virtualization_services['num_vs_hosts_down']++;
                                }
                            }
                            
                            // Record VMware Host CPU Usage
                            if (strpos($sensor->sensortype, 'VMware Host Performance') !== false) {
                                $this->virtualization_services['virtualization_hosts'][$vs_index]['host_cpu_usage'] = (string) $sensor->lastvalue;
                            }
                            
                            // Record Virtual Machine Data
                            if (strpos($sensor->sensortype, 'VMware Virtual Machine') !== false) {
                                $this->virtualization_services['num_vs_devices']++;
                                $this->virtualization_services['num_vms']++;
                                
                                $this->virtualization_services['virtual_machines'][] = array (
                                    'vm_name'   => (string) $sensor->name,
                                    'vm_usage'  => (string) $sensor->lastvalue,
                                    'vm_status' => (string) $sensor->status,
                                );
                                
                                if ($sensor->status == 'Down') {
                                    $this->virtualization_services['num_vs_devices_down']++;
                                    $this->virtualization_services['num_vms_down']++;
                                }
                            }
                        }
                    }
                    $vs_index++;
                }
            }
            
            if (strpos($group->name, 'Amazon') !== false) {
                foreach ($group->device as $device) {
                    $this->virtualization_services['num_vs_devices']++;
                    $this->virtualization_services['num_vms']++;
                    
                    foreach($device->sensor as $sensor) {
                        if ($sensor->name == 'Cloud Ping') {
                            $this->virtualization_services['virtual_machines'][] = array (
                                'vm_name'    => (string) $device->name,
                                'vm_usage' => 'N/A',
                                'vm_status'  => (string) $sensor->status,
                            );
                            
                            if ($sensor->status == 'Down') {
                                $this->virtualization_services['num_vms_down']++;
                            }
                        }
                    }   
                }
            }
        }
        
        // Calculate Virtual Services Device Totals
        $this->virtualization_services['num_vs_devices_up'] = 
            $this->virtualization_services['num_vs_devices'] - $this->virtualization_services['num_vs_devices_down'];
        $this->virtualization_services['percent_vs_devices_up'] = 
            ($this->virtualization_services['num_vs_devices_up'] / $this->virtualization_services['num_vs_devices'])*100;
        $this->virtualization_services['num_vs_hosts_up'] = 
            $this->virtualization_services['num_vs_hosts'] - $this->virtualization_services['num_vs_hosts_down'];
        $this->virtualization_services['percent_vs_hosts_up'] = 
            ($this->virtualization_services['num_vs_hosts_up'] / $this->virtualization_services['num_vs_hosts'])*100;
        $this->virtualization_services['num_vms_up'] = 
            $this->virtualization_services['num_vms'] - $this->virtualization_services['num_vms_down'];
        $this->virtualization_services['percent_vms_up'] =
            ($this->virtualization_services['num_vms_up'] / $this->virtualization_services['num_vms'])*100;
    }
    
    /**
     *  Gather Internet Statistics
     *  Here we will gather all of the WAN interfaces, and
     *  poll the traffic together to come up with an aggregate
     *  internet traffic value, along with generating basic uptime
     *  values.
     */
    public function internet_connectivity() {
        
        // Get the network infrastructure object.
        $network_infrastructure = $this->filter_object('Network Infrastructure');
        
        // Declare elements starting at zero.
        $this->internet_connectivity['total_traffic'] = 0;
        $this->internet_connectivity['num_wan_connections'] = 0;
        $this->internet_connectivity['num_wan_connections_down'] = 0;
        
        foreach ($network_infrastructure->device as $device) {
            if ($device->name == "YWAMMT-COREROUTER") {
                $core_router = $device;
                
                $s_index = 0;
                foreach ($core_router->sensor as $sensor) {
                    if (strpos($sensor->name, 'WAN') !== false) {
                        
                        $dates = $this->calculate_historic_data_dates(1);
                        $sdate = $dates['start_date'];
                        $edate = $dates['end_date'];
                        
                        // Grab historic data object for specific sensor
                        $historic_data = $this->retrieve_historic_data((string) $sensor->id, $sdate, $edate, '3600');
                        
                        $_24_hour_traffic = 0;
                        $int_index = 0;
                        foreach ($historic_data->item as $interval) {
                            
                            $this->internet_connectivity['wan_connections'][$s_index]['24_hour_data'][$int_index] = array (
                                'datetime'      =>  (string) $interval->datetime,
                                'total_traffic' =>  (string) $interval->value[0],
                                'traffic_down'  =>  (string) $interval->value[2],
                                'traffic_up'    =>  (string) $interval->value[4],
                                );
                            
                            // Add Up Intervals into Totals Array
                            $this->internet_connectivity['24_hour_totals_data'][$int_index]['datetime'] = (string) $interval->datetime;
                            $this->internet_connectivity['24_hour_totals_data'][$int_index]['total_traffic_intervals'][] = (string) $interval->value[0];
                            $this->internet_connectivity['24_hour_totals_data'][$int_index]['traffic_down_intervals'][] = (string) $interval->value[2];
                            $this->internet_connectivity['24_hour_totals_data'][$int_index]['traffic_up_intervals'][] = (string) $interval->value[4];
                            
                            // Add up Total Traffic
                            if (isset($this->internet_connectivity['24_hour_totals_data'][$int_index]['total_traffic'])) {
                                $this->internet_connectivity['24_hour_totals_data'][$int_index]['total_traffic'] +=
                                    intval((str_replace(' GByte', '', (string) $interval->value[0])));
                            } else {
                                $this->internet_connectivity['24_hour_totals_data'][$int_index]['total_traffic'] = 
                                    intval((str_replace(' GByte', '', (string) $interval->value[0])));
                            }
                            
                            // Add up Total Traffic Down
                            if (isset($this->internet_connectivity['24_hour_totals_data'][$int_index]['traffic_down'])) {
                                $this->internet_connectivity['24_hour_totals_data'][$int_index]['traffic_down'] +=
                                    intval((str_replace(' GByte', '', (string) $interval->value[2])));
                            } else {
                                $this->internet_connectivity['24_hour_totals_data'][$int_index]['traffic_down'] = 
                                    intval((str_replace(' GByte', '', (string) $interval->value[2])));
                            }
                            
                            // Add up Total Traffic Down
                            if (isset($this->internet_connectivity['24_hour_totals_data'][$int_index]['traffic_up'])) {
                                $this->internet_connectivity['24_hour_totals_data'][$int_index]['traffic_up'] +=
                                    intval((str_replace(' GByte', '', (string) $interval->value[4])));
                            } else {
                                $this->internet_connectivity['24_hour_totals_data'][$int_index]['traffic_up'] = 
                                    intval((str_replace(' GByte', '', (string) $interval->value[4])));
                            }
                                
                            
                            // Add Interval Traffic to Total 24 Hour Traffic
                            $_24_hour_traffic += $interval->value[0];
                            
                            $int_index++;
                        }
                        
                        // Set 24 Hour Traffic for Sensor
                        $this->internet_connectivity['wan_connections'][$s_index]['24_hour_traffic'] = $_24_hour_traffic;
                        
                        // Fill up a WAN Connections Array
                        $this->internet_connectivity['wan_connections'][$s_index]['connection_name'] = (string) $sensor->name;
                        $this->internet_connectivity['wan_connections'][$s_index]['connection_traffic'] = (string) $sensor->lastvalue;
                        $this->internet_connectivity['wan_connections'][$s_index]['connection_status'] = (string) $sensor->status;
                        
                        // Add 1 To Total WANs
                        $this->internet_connectivity['num_wan_connections']++;
                        // Check Sensor Status
                        $sensor->status == 'Down' ? $this->internet_connectivity['num_wan_connections_down']++ : null;
                        // Record Total Internet Traffic
                        $this->internet_connectivity['total_traffic'] += $sensor->lastvalue;
                    }

                    if($sensor->name == "PING") {
                        $this->internet_connectivity['core_router_status'] = (string) $sensor->status;
                    }
                    $s_index++;
                }
            }
        }
        // Define the number of WAN Connections Up
        $this->internet_connectivity['num_wan_connections_up'] = 
            $this->internet_connectivity['num_wan_connections'] - $this->internet_connectivity['num_wan_connections_down'];
        // Define Percentage of WAN Connections Up
        $this->internet_connectivity['percent_wan_connections_up'] = 
            ($this->internet_connectivity['num_wan_connections_up']/$this->internet_connectivity['num_wan_connections'])*100;   
    }
    
    /**
     *  Network Distribution
     *  Here we will gather all of the necessary information to represent
     *  devices that fall under network distribution.
     */
    public function network_distribution() {
        
        /**
         *  Gather Statistics for Switches
         *  Here we will gather statistics and counts for the distribution
         *  switches on the network.
         */
        $switches = $this->filter_object('Switches');
        
        // Declare elements starting at zero.
        $this->network_distribution['num_switches'] = 0;
        $this->network_distribution['num_switches_down'] = 0;
        $this->network_distribution['num_dist_switches'] = 0;
        $this->network_distribution['num_dist_switches_down'] = 0;
        
        // Iterate Through All Switches
        foreach ($switches as $switch) {
            if (!empty($switch->name)) {
                print_r($switch->name);
                $this->network_distribution['num_switches']++;

                // Iterate Through Switch Sensors
                foreach ($switch->sensor as $sensor) {

                    // Check PING Sensor
                    if ($sensor->name == 'PING') {

                        // Record Core Switch Statistics otherwise procedd normally.
                        if($switch->name == "YWAMMT | SWCH | Core Switch") {
                            $this->network_distribution['core_switch_status'] = (string) $sensor->status;

                            if ($sensor->status == "Down") {
                                $this->network_distribution['num_switches_down']++;
                            }  
                        } else {
                            if ($sensor->status == "Down") {
                                $this->network_distribution['num_switches_down']++;
                                $this->network_distribution['num_dist_switches_down']++;
                            }
                        }

                        $this->network_distribution['switches'][] = array (
                            'switch_name'    => (string) $switch->name,
                            'switch_latency' => (string) $sensor->lastvalue,
                            'switch_status'  => (string) $sensor->status,
                        );
                    }
                }
            }
        }
        
        // Remove Core Switch from Distribution Switches.
        $this->network_distribution['num_dist_switches'] = $this->network_distribution['num_switches'] - 1;

        // Calculate Switches Up
        $this->network_distribution['num_switches_up'] = 
            $this->network_distribution['num_switches'] - $this->network_distribution['num_switches_down'];
        $this->network_distribution['num_dist_switches_up'] = 
            $this->network_distribution['num_dist_switches'] - $this->network_distribution['num_dist_switches_down'];

        // Calculate Switches Up Percentage
        $this->network_distribution['percent_switches_up'] = 
            ($this->network_distribution['num_switches'] / $this->network_distribution['num_switches_up'])*100;
        $this->network_distribution['percent_dist_switches_up'] = 
            ($this->network_distribution['num_dist_switches'] / $this->network_distribution['num_dist_switches_up'])*100;
    
        // Sort Switches By Name
        function sort_by_switch_name ( $a, $b ) {
            return strcmp($a['switch_name'], $b['switch_name']);
        }
        
        usort($this->network_distribution['switches'], 'sort_by_switch_name');
        
        /**
         *  Wireless Backhaul Devices
         *  Here we will count all of the wireless backhaul devices
         *  used to wireless distribute the network over the beautful
         *  landscape that is Montana.
         */
        $wb_devices = $this->filter_object('Wireless Backhaul Devices');
        $ri_devices = $this->filter_object('Residential Infrastructure');
        
        $this->network_distribution['num_wb_devices'] = 0;
        $this->network_distribution['num_wb_devices_down'] = 0;
        $this->network_distribution['num_ri_wb_devices'] = 0;
        $this->network_distribution['num_ri_wb_devices_down'] = 0;
        
        foreach ($wb_devices->device as $device) {
            $this->network_distribution['num_wb_devices']++;
            
            foreach ($device->sensor as $sensor) {
                if ($sensor->name == "PING") {
                    $this->network_distribution['wb_devices'][] = array(
                        'device_name'   => (string) $device->name,
                        'device_latency'=>(string) $sensor->lastvalue,
                        'device_status' => (string) $sensor->status,
                    );
                    
                    if ($sensor->status == 'Down') {
                        $this->network_distribution['num_wb_devices_down']++;
                    }
                }
            }
        }
        
        /**
         *  Include Residential Infrastructure in Network Distribution
         *  Include appropriate Residential Infrastructure equipments 
         *  in Wirless Backhaul Devices
         */
        foreach($ri_devices->device as $device) {
            if (strpos($device->name, 'SCTR') !== false) {
                $this->network_distribution['num_wb_devices']++;
                $this->network_distribution['num_ri_wb_devices']++;
                
                foreach ($device->sensor as $sensor) {
                    if ($sensor->name == 'PING' && $sensor->status == 'Down') {
                        $this->network_distribution['num_wb_devices_down']++;
                        $this->network_distribution['num_ri_wb_devices_down']++;
                    }
                }
            }
        }
        
        // Include Stations on Houses in Network Distribution Stats
        foreach ($ri_devices->group as $group) {
            foreach ($group->device as $device) {
                if (strpos($device->name, 'YWAMMT | STA') !== false) {
                    $this->network_distribution['num_wb_devices']++;
                    $this->network_distribution['num_ri_wb_devices']++;
                    
                    foreach ($device->sensor as $sensor) {
                        if ($sensor->name == 'PING' && $sensor->status == 'Down') {
                            $this->network_distribution['num_wb_devices_down']++;
                            $this->network_distribution['num_ri_wb_devices_down']++;
                        }
                    }
                }
            }
        }
        
        // Calculate Wireless Backhaul Totlas
        $this->network_distribution['num_wb_devices_up'] =
            $this->network_distribution['num_wb_devices'] - $this->network_distribution['num_wb_devices_down'];
        $this->network_distribution['percent_wb_devices_up'] =
            ($this->network_distribution['num_wb_devices_up'] / $this->network_distribution['num_wb_devices'])*100;
        $this->network_distribution['num_ri_wb_devices_up'] =
            $this->network_distribution['num_ri_wb_devices'] - $this->network_distribution['num_ri_wb_devices_down'];
        $this->network_distribution['percent_ri_wb_devices_up'] =
            ($this->network_distribution['num_ri_wb_devices_up'] / $this->network_distribution['num_ri_wb_devices'])*100;
        
        // Sort Wireless Backhauls By Name
        function sort_by_wb_name ( $a, $b ) {
            return strcmp($a['device_name'], $b['device_name']);
        }

        usort($this->network_distribution['wb_devices'], 'sort_by_wb_name');
        
        /**
         *  Calculate Network Distribution Totals
         *  Here we calculate the totals for the network distribution
         *  statistics.
         */
        $this->network_distribution['num_nd_devices'] = 0;
        $this->network_distribution['num_nd_devices_down'] = 0;
        
        // Give static number for Core Devices
        $this->network_distribution['num_nd_devices'] = 
            2 + $this->network_distribution['num_dist_switches'] + $this->network_distribution['num_wb_devices'];
        $this->network_distribution['num_nd_devices_down'] = 
            $this->network_distribution['num_dist_switches_down'] + $this->network_distribution['num_wb_devices_down'];

        // Record if either the Core Switch or Core Router are down.
        $this->internet_connectivity['core_router_status'] == "Down" ? $this->network_distribution['num_nd_devices_down']++ : null;
        $this->network_distribution['core_switch_status'] == "Down" ? $this->network_distribution['num_nd_devices_down']++ : null;

        $this->network_distribution['num_nd_devices_up'] = 
            $this->network_distribution['num_nd_devices'] - $this->network_distribution['num_nd_devices_down'];
        $this->network_distribution['percent_nd_devices_up'] = 
            ($this->network_distribution['num_nd_devices_up']/$this->network_distribution['num_nd_devices'])*100;
        
        /** Calculate Total Wireless Backhaul Devices
         *  Subtract Residential Infrastructure from Wireless Backhauls since they are already displayed in
         *  Residential Infrastructure secion.
         */
        $this->network_distribution['num_nonri_nd_devices'] =
            $this->network_distribution['num_nd_devices'] - $this->network_distribution['num_ri_wb_devices'];
        $this->network_distribution['num_nonri_nd_devices_up'] = 
            $this->network_distribution['num_nd_devices_up'] - $this->network_distribution['num_ri_wb_devices_up'];
        $this->network_distribution['percent_nonri_nd_devices_up'] = 
            ($this->network_distribution['num_nonri_nd_devices_up'] / $this->network_distribution['num_nonri_nd_devices'])*100;
    }
    
    /**
     *  Count and Gather Access Point Statistics
     *  Here we will count and gather different statistics about our
     *  wireless access points around the campus.
     */
    public function wireless_connectivity() {
        
        $wireless_access_points = $this->filter_object('Wireless Access Points');
        
        // Define equipment properties within object.
        $this->wireless_connectivity['num_wireless_aps'] = $this->wireless_connectivity['num_wireless_aps_down'] = 0;
        $this->wireless_connectivity['num_ywam_aps'] = $this->wireless_connectivity['num_ywam_aps_down'] = 0;
        $this->wireless_connectivity['num_mbi_aps'] = $this->wireless_connectivity['num_mbi_aps_down'] = 0;
        $this->wireless_connectivity['num_ri_aps'] = $this->wireless_connectivity['num_ri_aps_down'] = 0;

        // Define traffic properties within object.
        $this->wireless_connectivity['ywam_gen_traffic'] = 0;
        $this->wireless_connectivity['ywam_staff_traffic'] = 0;
        $this->wireless_connectivity['ywam_ri_traffic'] = 0;
        $this->wireless_connectivity['_100foldstudio_traffic'] = 0;
        $this->wireless_connectivity['mbi_gen_traffic'] = 0;
        $this->wireless_connectivity['mbi_staff_traffic'] = 0;

        $ap_index = 0;
        foreach ($wireless_access_points->device as $wireless_ap) {

            // Count statistics for YWAM Montana APs
            if (strpos($wireless_ap->name, 'YWAMMT | AP') !== false) {
                $this->wireless_connectivity['num_wireless_aps']++;
                $this->wireless_connectivity['num_ywam_aps']++;

                // Loop through each AP and check the sensors
                foreach ($wireless_ap->sensor as $sensor) {
                    
                    if (strpos($sensor->name, 'Aggregate Traffic') !== false) {
                        $this->wireless_connectivity['campus_wireless_aps'][$ap_index]['ap_traffic'] = (string) $sensor->lastvalue;
                    }
                    
                    if ($sensor->name == 'PING') {
                        $this->wireless_connectivity['campus_wireless_aps'][$ap_index]['ap_name'] = (string) $wireless_ap->name;
                        $this->wireless_connectivity['campus_wireless_aps'][$ap_index]['ap_status'] = (string) $sensor->status;
                        
                        if ($sensor->status == "Down") {
                            $this->wireless_connectivity['num_wireless_aps_down']++;
                            $this->wireless_connectivity['num_ywam_aps_down']++;
                        }
                    }

                    if (strpos($sensor->name, 'YWAMMT-General Traffic') !== false) {
                        $this->wireless_connectivity['ywam_gen_traffic'] += sanitize_traffic_kbit($sensor->lastvalue);
                    }

                    if (strpos($sensor->name, 'YWAMMT-StaffWireless Traffic') !== false) {
                        $this->wireless_connectivity['ywam_staff_traffic'] += sanitize_traffic_kbit($sensor->lastvalue);
                    }

                    if (strpos($sensor->name, '100FoldStudio Traffic') !== false) {
                        $this->wireless_connectivity['_100foldstudio_traffic'] += sanitize_traffic_kbit($sensor->lastvalue);
                    }
                }
            }

            // Count Statistics for MBI APs
            if (strpos($wireless_ap->name, 'MBI | AP') !== false) {
                $this->wireless_connectivity['num_wireless_aps']++;
                $this->wireless_connectivity['num_mbi_aps']++;

                // Loop through each sensor and check the status
                foreach ($wireless_ap->sensor as $sensor) {
                    
                    if (strpos($sensor->name, 'Aggregate Traffic') !== false) {
                        $this->wireless_connectivity['campus_wireless_aps'][$ap_index]['ap_traffic'] = (string) $sensor->lastvalue;
                    }
                    
                    if ($sensor->name == 'PING') {
                        $this->wireless_connectivity['campus_wireless_aps'][$ap_index]['ap_name'] = (string) $wireless_ap->name;
                        $this->wireless_connectivity['campus_wireless_aps'][$ap_index]['ap_status'] = (string) $sensor->status;
                        
                        if ($sensor->status == 'Down') {
                            $this->wireless_connectivity['num_wireless_aps_down']++;
                            $this->wireless_connectivity['num_mbi_aps_down']++;
                        }
                    }

                    if (strpos($sensor->name, 'MBI-CoreServices Traffic') !== false) {
                        $this->wireless_connectivity['mbi_staff_traffic'] += sanitize_traffic_kbit($sensor->lastvalue);
                    }

                    if (strpos($sensor->name, 'MBI-General Traffic') !== false) {
                        $this->wireless_connectivity['mbi_gen_traffic'] += sanitize_traffic_kbit($sensor->lastvalue);
                    }
                }
            }
            $ap_index++;
        }

        // Get statistics on YWAM | General being broadcast in residential structures.
        $residential_infrastructure = $this->filter_object('Residential Infrastructure');
        
        foreach ($residential_infrastructure->group as $residence) {
            foreach ($residence->device as $device) {
                if (strpos($device->name, 'YWAMMT | AP') !== false) {
                    $this->wireless_connectivity['num_wireless_aps']++;
                    $this->wireless_connectivity['num_ri_aps']++;

                    // Loop through each AP and check the sensors
                    foreach ($device->sensor as $sensor) {
                        if ($sensor->name == 'PING' && $sensor->status == "Down") {
                            $this->wireless_connectivity['num_wireless_aps_down']++;
                            $this->wireless_connectivity['num_ri_aps_down']++;
                        }

                        if (strpos($sensor->name, 'YWAMMT-General Traffic') !== false) {
                            
                            // Remember value of general traffic to be used to derive value of residential traffic.
                            $device_ywam_gen_traffic = sanitize_traffic_kbit($sensor->lastvalue);
                            $this->wireless_connectivity['ywam_gen_traffic'] += sanitize_traffic_kbit($sensor->lastvalue);
                        }

                        if (strpos($sensor->name, 'YWAMMT-StaffWireless Traffic') !== false) {
                            $this->wireless_connectivity['ywam_staff_traffic'] += sanitize_traffic_kbit($sensor->lastvalue);
                        }

                        if (strpos($sensor->name, '100FoldStudio Traffic') !== false) {
                            $this->wireless_connectivity['_100foldstudio_traffic'] += sanitize_traffic_kbit($sensor->lastvalue);
                        }

                        if (strpos($sensor->name, 'Aggregate Traffic') !== false) {
                            $device_ywam_ri_traffic = sanitize_traffic_kbit($sensor->lastvalue);
                        }
                    }

                    // Only add residential traffic value to Houses Devices
                    if (strpos($device->name, 'House') !== false) {
                        $this->wireless_connectivity['ywam_ri_traffic'] += 
                            ($device_ywam_ri_traffic - $device_ywam_gen_traffic) < 0 ? 0 : $device_ywam_ri_traffic - $device_ywam_gen_traffic;
                    }
                }
            }
        }
        // Defind JS Data String for Chart
        $this->wireless_connectivity['js_traffic_string'] =  
            $this->wireless_connectivity['ywam_gen_traffic'] . ',' . 
            $this->wireless_connectivity['ywam_staff_traffic'] . ',' . 
            $this->wireless_connectivity['_100foldstudio_traffic'] . ',' . 
            $this->wireless_connectivity['ywam_ri_traffic'] . ',' . 
            $this->wireless_connectivity['mbi_staff_traffic'] . ',' . 
            $this->wireless_connectivity['mbi_gen_traffic'];
        
        // Add Up all Wireless Access Point Statistics
        $this->wireless_connectivity['num_wireless_aps_up'] = 
            $this->wireless_connectivity['num_wireless_aps'] - $this->wireless_connectivity['num_wireless_aps_down'];
        $this->wireless_connectivity['percent_wireless_aps_up'] = 
            ($this->wireless_connectivity['num_wireless_aps_up'] / $this->wireless_connectivity['num_wireless_aps'])*100;

        $this->wireless_connectivity['num_ywam_aps_up'] = 
            $this->wireless_connectivity['num_ywam_aps'] - $this->wireless_connectivity['num_ywam_aps_down'];
        $this->wireless_connectivity['percent_ywam_aps_up'] = 
            ($this->wireless_connectivity['num_ywam_aps_up'] / $this->wireless_connectivity['num_ywam_aps'])*100;

        $this->wireless_connectivity['num_ri_aps_up'] = 
            $this->wireless_connectivity['num_ri_aps'] - $this->wireless_connectivity['num_ri_aps_down'];
        $this->wireless_connectivity['percent_ri_aps_up'] = 
            ($this->wireless_connectivity['num_ri_aps_up'] / $this->wireless_connectivity['num_ri_aps'])*100;

        $this->wireless_connectivity['num_mbi_aps_up'] = 
            $this->wireless_connectivity['num_mbi_aps'] - $this->wireless_connectivity['num_mbi_aps_down'];
        $this->wireless_connectivity['percent_mbi_aps_up'] = 
            ($this->wireless_connectivity['num_mbi_aps_up'] / $this->wireless_connectivity['num_mbi_aps'])*100;
        
        //Calculate Campus AP Statistics Wireless APs - Residential
        $this->wireless_connectivity['num_campus_aps'] = $this->wireless_connectivity['num_wireless_aps'] - $this->wireless_connectivity['num_ri_aps'];
        $this->wireless_connectivity['num_campus_aps_down'] = $this->wireless_connectivity['num_wireless_aps_down'] - $this->wireless_connectivity['num_ri_aps_down'];
        $this->wireless_connectivity['num_campus_aps_up'] = $this->wireless_connectivity['num_wireless_aps_up'] - $this->wireless_connectivity['num_ri_aps_up'];
        $this->wireless_connectivity['percent_campus_aps_up'] = ($this->wireless_connectivity['num_campus_aps_up'] / $this->wireless_connectivity['num_campus_aps'])*100;
   
    // Sort Access Points By Name
    function sort_by_ap_name ( $a, $b ) {
        return strcmp($a['ap_name'], $b['ap_name']);
    }

    usort($this->wireless_connectivity['campus_wireless_aps'], 'sort_by_ap_name');
    
    }
    
    /**
     *  Count Residential Infrastructure Devices & Gather Statuses
     *  We use this section to gather statistics about how many devices
     *  currently reside in this group, along with interpreting data based
     *  the current status of specific sensors.
     */
    public function residential_infrastructure() {
        
        $residential_infrastructure = $this->filter_object('Residential Infrastructure');
        
        
        $this->residential_infrastructure['num_ri_sectors'] = 0;
        $this->residential_infrastructure['num_ri_sectors_down'] = 0;
        $this->residential_infrastructure['num_ri_devices'] = 0;
        $this->residential_infrastructure['num_ri_devices_down'] = 0;
        
        $sector_index = 0;
        foreach ($residential_infrastructure->device as $device) {
            if (strpos($device->name, 'YWAMMT | SCTR') !== false) {
                $this->residential_infrastructure['num_ri_sectors']++;
                $this->residential_infrastructure['num_ri_devices']++;
                
                foreach ($device->sensor as $sensor) {
                    if ($sensor->name == 'PING') {
                        
                        // Calculate Device Uptime
                        $device_uptime = round(100-($sensor->cumulateddowntime_raw/$sensor->cumulateduptime_raw), 4);
                        
                        $this->residential_infrastructure['sectors'][$sector_index] = array (
                            'sector_name'   => (string) $device->name,
                            'sector_status' => (string) $sensor->status,
                            'sector_uptime' => $device_uptime,
                        );
                        
                        if ($sensor->status == 'Down') {
                            $this->residential_infrastructure['num_ri_sectors_down']++;
                            $this->residential_infrastructure['num_ri_devices_down']++;
                        }
                    }
                    
                    if (strpos($sensor->name, 'Aggregate Traffic') !== false) {
                        $this->residential_infrastructure['sectors'][$sector_index]['sector_traffic'] = (string) $sensor->lastvalue;
                            
                    }
                }
            }
            $sector_index++;
        }
        // Declare number of Residential Sectors up
        $this->residential_infrastructure['num_ri_sectors_up'] =
            $this->residential_infrastructure['num_ri_sectors'] - $this->residential_infrastructure['num_ri_sectors_down'];
        $this->residential_infrastructure['percent_ri_sectors_up'] =
            ($this->residential_infrastructure['num_ri_sectors_up'] / $this->residential_infrastructure['num_ri_sectors'])*100;

        // Count Devices in Each Individual Residence Group.
        $residence_index = 0;
        foreach ($residential_infrastructure->group as $residence) {
            $this->residential_infrastructure['residences'][$residence_index]['residence_name'] = (string) $residence->name;
            
            foreach ($residence->device as $device) {
                $this->residential_infrastructure['num_ri_devices']++;
                foreach ($device->sensor as $sensor) {
                    if ($sensor->name == "PING") {
                        
                        // Calculate Device Uptime
                        $device_uptime = round(100-($sensor->cumulateddowntime_raw/$sensor->cumulateduptime_raw), 4);
                        
                        $this->residential_infrastructure['residences'][$residence_index]['devices'][] = array (
                            'device_name'   => (string) $device->name,
                            'device_status' => (string) $sensor->status,
                            'device_uptime' => $device_uptime,
                        );

                        if ($sensor->status == "Down") {
                            $this->residential_infrastructure['num_ri_devices_down']++;
                        }

                        if (strpos($device->name, 'STA') !== false) {
                            if ($sensor->status == "Down"){
                                $this->residential_infrastructure['num_wb_devices_down']++;
                            }
                        }
                    }
                }
            }
            $residence_index++;
        }
        // Declare Number of Residential Infrastructure Devics Up
        $this->residential_infrastructure['num_ri_devices_up'] = 
            $this->residential_infrastructure['num_ri_devices'] - $this->residential_infrastructure['num_ri_devices_down'];
        $this->residential_infrastructure['percent_ri_devices_up'] =
            ($this->residential_infrastructure['num_ri_devices_up'] / $this->residential_infrastructure['num_ri_devices'])*100;
    }
    
    /**
     *  Define Telephony Services Data Structure
     *  Here we will define all of the telephony services and
     *  devices.
     */
    public function telephony_services() {
        $telephony_devices = $this->filter_object('VOIP Infrastructure');
        
        // Declare Variables that Start at Zero
        $this->telephony_services['num_ts_devices'] = 0;
        $this->telephony_services['num_ts_devices_down'] = 0;
        $this->telephony_services['num_ts_phones'] = 0 ;
        $this->telephony_services['num_ts_phones_down'] = 0;
        
        foreach ($telephony_devices->device as $device) {
            if ($device->name == 'YWAMMT-CME') {
                $this->telephony_services['num_ts_devices']++;
                foreach($device->sensor as $sensor) {
                    if ($sensor->name == 'PING') {
                        $this->telephony_services['phone_server_status'] = (string) $sensor->status;
                        
                        if ($sensor->status == 'Down') {
                            $this->telephony_services['num_ts_devices_down']++;
                        }
                    }
                }
            }
            if ($device->name == 'YWAMMT-VOICEMAIL') {
                $this->telephony_services['num_ts_devices']++;
                foreach($device->sensor as $sensor) {
                    if ($sensor->name == 'PING') {
                        $this->telephony_services['voicemail_server_status'] = (string) $sensor->status;
                        
                        if ($sensor->status == 'Down') {
                            $this->telephony_services['num_ts_devices_down']++;
                        }
                    }
                }
            }
        }
        
        foreach($telephony_devices->group as $group) {
            if ($group->name == 'IP Phones') {
                foreach ($group->device as $phone) {
                    $this->telephony_services['num_ts_devices']++;
                    $this->telephony_services['num_ts_phones']++;
                    
                    foreach($phone->sensor as $sensor) {
                        if ($sensor->name == 'PING') {
                            
                            $this->telephony_services['phones'][] = array (
                                'phone_name'   => (string) $phone->name,
                                'phone_status' => (string) $sensor->status,
                                'voicemail'    => strpos($phone->tags, 'has_voicemail') !== false ? true : false,
                                'extension'    => preg_match('/ext_[0-9]{3}/', (string) $phone->tags, $matches) ? str_replace('ext_', '', $matches[0]) : '',
                            );
                            
                            if ($sensor->status == 'Down') {
                                $this->telephony_services['num_ts_devices_down']++;
                                $this->telephony_services['num_ts_phones_down']++;
                            }
                        }
                    }
                }
            }
        }
        
        // Sort Phones By Name
        function sort_by_phone_name ( $a, $b ) {
            return strcmp($a['phone_name'], $b['phone_name']);
        }
        
        usort($this->telephony_services['phones'], 'sort_by_phone_name');
        
        // Calculate Totals of Telephony Services Devices
        $this->telephony_services['num_ts_devices_up'] = $this->telephony_services['num_ts_devices'] - $this->telephony_services['num_ts_devices_down'];
        $this->telephony_services['percent_ts_devices_up'] = ($this->telephony_services['num_ts_devices_up'] / $this->telephony_services['num_ts_devices'])*100;
        $this->telephony_services['num_ts_phones_up'] = $this->telephony_services['num_ts_phones'] - $this->telephony_services['num_ts_phones_down'];
        $this->telephony_services['percent_ts_phones_up'] = ($this->telephony_services['num_ts_phones_up'] / $this->telephony_services['num_ts_phones'])*100;
    }
    
    public function web_services() {
        $web_services = $this->filter_object('Web Services');
        
        $this->web_services['num_web_services'] = 0;
        $this->web_services['num_web_services_down'] = 0;
        
        $service_index = 0;
        foreach ($web_services->device as $service) {
            $this->web_services['num_web_services']++;
            $this->web_services['services'][$service_index]['service_name'] = (string) $service->name;
            
            foreach ($service->sensor as $sensor) {
                if ($sensor->name == "PING") {
                    $this->web_services['services'][$service_index]['service_latency'] = (string) $sensor->lastvalue;
                    $this->web_services['services'][$service_index]['service_status'] = (string) $sensor->status;
                    
                    if ($sensor->status == 'Down') {
                        $this->web_services['num_web_services_down']++;
                    }
                }
            }
            
            $service_index++;
        }
        
        // Calculate Totals of Web Services
        $this->web_services['num_web_services_up'] = $this->web_services['num_web_services'] - $this->web_services['num_web_services_down'];
        $this->web_services['percent_web_services_up'] = ($this->web_services['num_web_services_up'] / $this->web_services['num_web_services'])*100;
        
    }
    
    public function surveillance_services() {
        $surveillance_services = $this->filter_object('Surveillance Infrastructure');
        
        $this->surveillance_services['num_ss_devices'] = 0;
        $this->surveillance_services['num_ss_devices_down'] = 0;
        $this->surveillance_services['num_ss_cameras'] = 0;
        $this->surveillance_services['num_ss_cameras_down'] = 0;
        
        foreach ($surveillance_services->device as $device) {
            $this->surveillance_services['num_ss_devices']++;
            $this->surveillance_services['num_ss_cameras']++;
            
            foreach ($device->sensor as $sensor) {
                if ($sensor->name == 'PING') {
                    
                    $this->surveillance_services['cameras'][] = array (
                        'camera_name'   => (string) $device->name,
                        'camera_status' => (string) $sensor->status,
                    );
                    
                    if ($sensor->status == 'Down') {
                        $this->surveillance_services['num_ss_devices_down']++;
                        $this->surveillance_services['num_ss_cameras_down']++;
                    }
                }
            }
        }
        
        foreach ($this->virtualization_services['virtual_machines'] as $vm) {
            if ($vm['vm_name'] == 'YWAMMT-SURVEILLANCE') {
                $this->surveillance_services['num_ss_devices']++;
                
                // Record Server Status
                $this->surveillance_services['ss_server_status'] = $vm['vm_status'];
                
                if ($vm['vm_status'] == 'Down') {
                    $this->surveillance_services['num_ss_devices_down']++;
                }
            }
        }
    
    $this->surveillance_services['num_ss_devices_up'] =
        $this->surveillance_services['num_ss_devices'] - $this->surveillance_services['num_ss_devices_down'];
    $this->surveillance_services['percent_ss_devices_up'] =
        ($this->surveillance_services['num_ss_devices_up'] / $this->surveillance_services['num_ss_devices'])*100;
    $this->surveillance_services['num_ss_cameras_up'] =
        $this->surveillance_services['num_ss_cameras'] - $this->surveillance_services['num_ss_cameras_down'];
    $this->surveillance_services['percent_ss_cameras_up'] =
        ($this->surveillance_services['num_ss_cameras_up'] / $this->surveillance_services['num_ss_cameras'])*100;
    
    // Sort Cameras By Name
    function sort_by_camera_name ( $a, $b ) {
        return strcmp($a['camera_name'], $b['camera_name']);
    }

    usort($this->surveillance_services['cameras'], 'sort_by_camera_name');
    }
    
    /**
     *  Gather Statistics about Network Services
     *  These will include things like printers, fax machines,
     *  and file servers.
     */
    public function file_printing_services() {
        $file_servers = $this->filter_object('File Servers');
        $printers = $this->filter_object('Printers');
        
        $this->file_printing_services['num_fps_devices'] = 0;
        $this->file_printing_services['num_fps_devices_down'] = 0;
        
        $this->file_printing_services['num_fs'] = 0;
        $this->file_printing_services['num_fs_down'] = 0;
        $this->file_printing_services['num_printers'] = 0;
        $this->file_printing_services['num_printers_down'] = 0;

        // Calculate and Populate File Server Information
        $fs_index = 0;
        foreach($file_servers->device as $device) {
            $this->file_printing_services['num_fps_devices']++;
            $this->file_printing_services['num_fs']++;
            
            
            $this->file_printing_services['fileservers'][$fs_index]['server_name'] = (string) $device->name;
            
            // Zero Out SNMP Traffic Sensor
            $this->file_printing_services['fileservers'][$fs_index]['server_traffic'] = 0;
            
            foreach ($device->sensor as $sensor) {
                
                if (strpos($sensor->sensortype, 'SNMP Synology System Health') !== false) {
                    $this->file_printing_services['fileservers'][$fs_index]['server_status'] = (string) $sensor->status;
                    $this->file_printing_services['fileservers'][$fs_index]['server_temp'] = (string) $sensor->lastvalue;
                    
                    if($sensor->status == 'Down') {
                        $this->file_printing_services['num_fps_devices_down']++;
                        $this->file_printing_services['num_fs_down']++;
                    }
                }
                
                if (strpos($sensor->sensortype, 'SNMP Traffic') !== false) {
                    $this->file_printing_services['fileservers'][$fs_index]['server_traffic'] += 
                        intval(str_replace(',', '', (str_replace('kbit/s', '', $sensor->lastvalue))));
                }
                
                if (strpos($sensor->sensortype, 'SNMP Synology Physical Disk') !== false) {
                    $this->file_printing_services['fileservers'][$fs_index]['disks'][] = (string) $sensor->status;
                }
            }
            
            $fs_index++;
        }
        
        // Calculate and Populate Priner Information
        foreach($printers->device as $printer) {
            $this->file_printing_services['num_fps_devices']++;
            $this->file_printing_services['num_printers']++;
            
            foreach ($printer->sensor as $sensor) {
                
                if(strpos($sensor->name, 'SNMP Printer') !== false) {
                    
                    $this->file_printing_services['printers'][] = array (
                        'printer_name'       => (string) $printer->name,
                        'printer_pages'      => (string) $sensor->lastvalue,
                        'printer_status_msg' => (string) $sensor->statusmessage,
                        'printer_status'     => (string) $sensor->status,
                    );
                    
                    if($sensor->status == 'Down') {
                        $this->file_printing_services['num_fps_devices_down']++;
                        $this->file_printing_services['num_printers_down']++;
                    }
                }
            }
        }
        
        // Calculate Totals for File & Printing Services
        $this->file_printing_services['num_fps_devices_up'] =
            $this->file_printing_services['num_fps_devices'] - $this->file_printing_services['num_fps_devices_down'];
        $this->file_printing_services['percent_fps_devices_up'] =
            ($this->file_printing_services['num_fps_devices_up'] / $this->file_printing_services['num_fps_devices'])*100;
        
        $this->file_printing_services['num_fs_up'] =
            $this->file_printing_services['num_fs'] - $this->file_printing_services['num_fs_down'];
        $this->file_printing_services['percent_fs_up'] =
            ($this->file_printing_services['num_fs_up'] / $this->file_printing_services['num_fs'])*100;
        
        $this->file_printing_services['num_printers_up'] =
            $this->file_printing_services['num_printers'] - $this->file_printing_services['num_printers_down'];
        $this->file_printing_services['percent_printers_up'] =
            ($this->file_printing_services['num_printers_up'] / $this->file_printing_services['num_printers'])*100;
    }
    
    /**
     *  Construct the Network Status Object
     *  This is the constructor function of the Network Status
     *  class and will build the object needed to retrieve data.
     */
    public function __construct() {
        $this->retrieve_xml();
        
        $this->virtualization_services();    // Build Virtualization Services Dataset
        $this->internet_connectivity();      // Build Internet Connectivity Dataset
        $this->network_distribution();       // Build Network Distribution Dataset
        $this->wireless_connectivity();      // Build Wireless Connectivity Dataset
        $this->residential_infrastructure(); // Build Residential Infrastructure Dataset
        $this->telephony_services();         // Build Telephony Services Dataset
        $this->web_services();               // Build Web Services Dataset
        $this->surveillance_services();      // Build Surveillance Services Dataset
        $this->file_printing_services();     // Build File & Networking Services Dataset
        
        // Add Up Everything
        $this->network_status['num_devices_services'] =
            $this->virtualization_services['num_vs_devices'] +
            $this->internet_connectivity['num_wan_connections'] +
            $this->network_distribution['num_nonri_nd_devices'] +
            $this->wireless_connectivity['num_campus_aps'] +
            $this->residential_infrastructure['num_ri_devices'] +
            $this->telephony_services['num_ts_devices'] +
            $this->web_services['num_web_services'] +
            $this->surveillance_services['num_ss_devices'] +
            $this->file_printing_services['num_fps_devices'];
        
        $this->network_status['num_devices_services_down'] =
            $this->virtualization_services['num_vs_devices_down'] +
            $this->internet_connectivity['num_wan_connections_down'] +
            $this->network_distribution['num_nonri_nd_devices_down'] +
            $this->wireless_connectivity['num_campus_aps_down'] +
            $this->residential_infrastructure['num_ri_devices_down'] +
            $this->telephony_services['num_ts_devices_down'] +
            $this->web_services['num_web_services_down'] +
            $this->surveillance_services['num_ss_devices_down'] +
            $this->file_printing_services['num_fps_devices_down'];
        
        $this->network_status['num_devices_services_up'] =
            $this->network_status['num_devices_services'] - $this->network_status['num_devices_services_down'];
        $this->network_status['percent_devices_services_up'] =
            ($this->network_status['num_devices_services_up'] / $this->network_status['num_devices_services'])*100;
        
        // Dump the retrieved XML data.
        unset($this->retrieved_xml);
    }
}

?>