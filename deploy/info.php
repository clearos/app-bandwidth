<?php

/////////////////////////////////////////////////////////////////////////////
// General information
/////////////////////////////////////////////////////////////////////////////

$app['basename'] = 'bandwidth';
$app['version'] = '2.1.6';
$app['release'] = '1';
$app['vendor'] = 'ClearFoundation';
$app['packager'] = 'ClearFoundation';
$app['license'] = 'GPLv3';
$app['license_core'] = 'LGPLv3';
$app['description'] = lang('bandwidth_app_description');
$app['tooltip'] = lang('bandwidth_tooltip_description');

/////////////////////////////////////////////////////////////////////////////
// App name and categories
/////////////////////////////////////////////////////////////////////////////

$app['name'] = lang('bandwidth_app_name');
$app['category'] = lang('base_category_network');
$app['subcategory'] = lang('base_subcategory_bandwidth_control');

/////////////////////////////////////////////////////////////////////////////
// Controllers
/////////////////////////////////////////////////////////////////////////////

$app['controllers']['bandwidth']['title'] = lang('bandwidth_app_name');
$app['controllers']['ifaces']['title'] = lang('bandwidth_network_interfaces');
$app['controllers']['basic']['title'] = lang('bandwidth_basic_rules');
$app['controllers']['advanced']['title'] = lang('bandwidth_advanced_rules');

/////////////////////////////////////////////////////////////////////////////
// Packaging
/////////////////////////////////////////////////////////////////////////////

$app['requires'] = array(
    'app-network',
);

$app['core_requires'] = array(
    'app-network-core',
    'app-firewall-core >= 1:1.5.18',
);

$app['core_directory_manifest'] = array(
    '/var/clearos/bandwidth' => array(),
    '/var/clearos/bandwidth/backup/' => array(),
);

$app['core_file_manifest'] = array(
    'bandwidth.conf' => array(
        'target' => '/etc/clearos/bandwidth.conf',
        'mode' => '0644',
        'owner' => 'root',
        'group' => 'root',
        'config' => TRUE,
        'config_params' => 'noreplace',
    ),
);

$app['delete_dependency'] = array(
    'app-bandwidth-core'
);
