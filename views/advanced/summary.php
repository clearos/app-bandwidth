<?php

/**
 * Bandwidth advanced rules view.
 *
 * @category   apps
 * @package    bandwidth
 * @subpackage views
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/bandwidth/
 */

///////////////////////////////////////////////////////////////////////////////
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.  
//
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
// Load dependencies
///////////////////////////////////////////////////////////////////////////////

$this->lang->load('network');
$this->lang->load('bandwidth');
$this->lang->load('firewall');

///////////////////////////////////////////////////////////////////////////////
// Headers
///////////////////////////////////////////////////////////////////////////////

if ($report_type === 'detailed') {
    $headers = array(
        lang('firewall_nickname'),
        lang('bandwidth_match_address'),
        lang('network_ip'),
        lang('bandwidth_match_port'),
        lang('network_port'),
        lang('bandwidth_rate'),
        lang('bandwidth_ceiling'),
    );
} else {
    $headers = array(
        lang('firewall_nickname'),
        lang('network_ip'),
        lang('network_port'),
    );
}

///////////////////////////////////////////////////////////////////////////////
// Anchors 
///////////////////////////////////////////////////////////////////////////////

if ($report_type === 'detailed') {
    $anchors = array(
        anchor_cancel('/app/bandwidth/advanced'),
        anchor_add('/app/bandwidth/advanced/add')
    );
} else {
    $anchors = array(
        anchor_custom('/app/bandwidth/advanced/index/detailed', lang('base_detailed_view')),
        anchor_add('/app/bandwidth/advanced/add')
    );
}

///////////////////////////////////////////////////////////////////////////////
// Items
///////////////////////////////////////////////////////////////////////////////

foreach ($rules as $id => $details) {
    $port = empty($details['port']) ? '0' : $details['port'];
    $host = empty($details['host']) ? '0' : preg_replace('/\//', '_', $details['host']);

    $key = $details['wanif'] . '/' .
        $details['address_type'] . '/' .
        $details['port_type'] . '/' .
        $host . '/' .
        $port . '/' .
        $details['priority'] . '/' .
        $details['upstream'] . '/' .
        $details['upstream_ceil'] . '/' .
        $details['downstream'] . '/' .
        $details['downstream_ceil'] . '/' .
        $details['name'];

    $state = ($details['enabled']) ? 'disable' : 'enable';
    $state_anchor = 'anchor_' . $state;

    // Doh.  The user guide had the meanings reversed.  Sorry.
    if (($details['upstream_ceil'] > 0) || ($details['upstream'] > 0))
        $details['address_type'] = ($details['address_type'] == 0) ? 1 : 0;

    $rate = (!empty($details['upstream'])) ? $details['upstream'] : $details['downstream'];
    $rate = ($rate == 0) ? '' : $rate;
    $ceiling = (!empty($details['upstream_ceil'])) ? $details['upstream_ceil'] : $details['downstream_ceil'];
    $ceiling = ($ceiling == 0) ? '' : $ceiling;
    $address_type = (empty($details['host'])) ? '' : $types[$details['address_type']];
    $port_type = (empty($details['port'])) ? '' : $types[$details['port_type']];

    $item['title'] = $details['name'];
    $item['action'] = '/app/bandwidth/advanced/delete/' . $key;
    $item['anchors'] = button_set(
        array(
            $state_anchor('/app/bandwidth/advanced/' . $state . '/' . $key, 'high'),
            anchor_delete('/app/bandwidth/advanced/delete/' . $key, 'low')
        )
    );

    if ($report_type === 'detailed') {
        $item['details'] = array(
            $details['name'],
            $address_type,
            $details['host'],
            $port_type,
            $details['port'],
            $rate,
            $ceiling
        );
    } else {
        $item['details'] = array(
            $details['name'],
            $details['host'],
            $details['port'],
        );
    }

    $items[] = $item;
}

sort($items);

///////////////////////////////////////////////////////////////////////////////
// Summary table
///////////////////////////////////////////////////////////////////////////////

echo summary_table(
    lang('bandwidth_advanced_rules'),
    $anchors,
    $headers,
    $items
);
