<?php

/**
 * Bandwidth advanced rules controller.
 *
 * @category   apps
 * @package    bandwidth
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011-2012 ClearFoundation
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
// D E P E N D E N C I E S
///////////////////////////////////////////////////////////////////////////////

use \clearos\apps\bandwidth\Bandwidth as Bandwidth;
use \clearos\apps\network\Network as Network;
use \Exception as Exception;

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Bandwidth advanced rules controller.
 *
 * @category   apps
 * @package    bandwidth
 * @subpackage controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2011-2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/bandwidth/
 */

class Advanced extends ClearOS_Controller
{
    /**
     * Port forwarding overview.
     *
     * @return view
     */

    function index($detailed = '')
    {
        // Load libraries
        //---------------

        $this->lang->load('bandwidth');
        $this->load->library('bandwidth/Bandwidth');
        $this->load->library('network/Network');

        // Load view data
        //---------------

        try {
            $data['rules'] = $this->bandwidth->get_bandwidth_rules(Bandwidth::TYPE_ADVANCED);
            $data['types'] = $this->bandwidth->get_types();
            $data['report_type'] = $detailed;
            $mode = $this->network->get_mode();
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
 
        // Load views
        //-----------

        if ($data['report_type'] === 'detailed')
            $options['type'] = MY_Page::TYPE_WIDE_CONFIGURATION;
        else
            $options = array();

        if ($mode == Network::MODE_STANDALONE || $mode == Network::MODE_TRUSTED_STANDALONE)
            $this->page->view_form('bandwidth/advanced/unavailable', $data, lang('bandwidth_advanced_rules'), $options);
        else
            $this->page->view_form('bandwidth/advanced/summary', $data, lang('bandwidth_advanced_rules'), $options);
    }

    /**
     * Add advanced bandwidth rule.
     *
     * @return view
     */

    function add()
    {
        // Load libraries
        //---------------

        $this->lang->load('base');
        $this->lang->load('bandwidth');
        $this->load->library('bandwidth/Bandwidth');

        // Set validation rules
        //---------------------

        $this->form_validation->set_policy('name', 'bandwidth/Bandwidth', 'validate_name', TRUE);
        $this->form_validation->set_policy('direction', 'bandwidth/Bandwidth', 'validate_advanced_direction', TRUE);
        $this->form_validation->set_policy('iface', 'bandwidth/Bandwidth', 'validate_interface', TRUE);

        $this->form_validation->set_policy('address_type', 'bandwidth/Bandwidth', 'validate_type');
        $this->form_validation->set_policy('address', 'bandwidth/Bandwidth', 'validate_address', TRUE);

        $this->form_validation->set_policy('port_type', 'bandwidth/Bandwidth', 'validate_type');
        $this->form_validation->set_policy('port', 'bandwidth/Bandwidth', 'validate_port');

        $this->form_validation->set_policy('rate', 'bandwidth/Bandwidth', 'validate_rate', TRUE);
        $this->form_validation->set_policy('ceiling', 'bandwidth/Bandwidth', 'validate_rate');
        $this->form_validation->set_policy('priority', 'bandwidth/Bandwidth', 'validate_priority', TRUE);

        $form_ok = $this->form_validation->run();

        // Handle form submit
        //-------------------

        if ($this->input->post('submit') && $form_ok) {
            if ($this->input->post('direction') == Bandwidth::DIRECTION_FROM_NETWORK) {
                $download_rate = 0;
                $download_ceiling = 0;
                $upload_rate = $this->input->post('rate');
                $upload_ceiling = $this->input->post('ceiling');
                // Doh.  The user guide had the meanings reversed.  Sorry.
                $address_type = ($this->input->post('address_type') == 0) ? 1 : 0;
            } else {
                $download_rate = $this->input->post('rate');
                $download_ceiling = $this->input->post('ceiling');
                $upload_rate = 0;
                $upload_ceiling = 0;
                $address_type = $this->input->post('address_type');
            }

            $address = ($this->input->post('address')) ? $this->input->post('address') : '';

            try {
                $this->bandwidth->add_advanced_rule(
                    $this->input->post('name'),
                    $this->input->post('iface'),
                    $address_type,
                    $this->input->post('port_type'),
                    $address,
                    $this->input->post('port'),
                    $this->input->post('priority'),
                    $upload_rate,
                    $upload_ceiling,
                    $download_rate,
                    $download_ceiling
                );

                $this->page->set_status_added();
                redirect('/bandwidth/advanced');
            } catch (Exception $e) {
                $this->page->view_exception($e);
                return;
            }
        }

        // Load the view data 
        //------------------- 

        try {
            $data['types'] = $this->bandwidth->get_types();
            $data['directions'] = $this->bandwidth->get_advanced_directions();
            $data['priorities'] = $this->bandwidth->get_priorities();
            $data['interfaces'] = array_keys($this->bandwidth->get_interfaces());
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }

        // Load the views
        //---------------

        $this->page->view_form('bandwidth/advanced/item', $data, lang('base_add'));
    }

    /**
     * Delete bandwidth rule confirmation.
     *
     * @param string $name    rule name
     * @param string $service service
     * @param string $rate    rate
     *
     * @return view
     */

    function delete($iface, $address_type, $port_type, $ip, $port, $priority, $upstream, $upstream_ceil, $downstream, $downstream_ceil, $nickname)
    {
        $this->lang->load('bandwidth');

        $confirm_uri = '/app/bandwidth/advanced/destroy/' . 
            $iface . '/' . 
            $address_type . '/' . 
            $port_type . '/' . 
            $ip . '/' .
            $port . '/' .
            $priority . '/' .
            $upstream . '/' . 
            $upstream_ceil . '/' .
            $downstream . '/' .
            $downstream_ceil
        ;
        $cancel_uri = '/app/bandwidth/advanced';

        if (empty($ip))
            $ip_port = '';
        else
            $ip_port = (empty($port)) ? " - $ip" : "- $ip:$port";

        $ip_port = preg_replace('/_/', '/', $ip_port);

        $items = array("$nickname $ip_port");

        $this->page->view_confirm_delete($confirm_uri, $cancel_uri, $items);
    }

    /**
     * Destroys bandwidth rule.
     *
     * @param string $name rule name
     *
     * @return view
     */

    function destroy($iface, $address_type, $port_type, $ip, $port, $priority, $upstream, $upstream_ceil, $downstream, $downstream_ceil)
    {
        // Load libraries
        //---------------

        $this->load->library('bandwidth/Bandwidth');

        // Handle form submit
        //-------------------

        try {
            $ip = preg_replace('/_/', '/', $ip);

            $this->bandwidth->delete_advanced_rule($iface, $address_type, $port_type, $ip, $port, $priority, $upstream, $upstream_ceil, $downstream, $downstream_ceil);

            $this->page->set_status_deleted();
            redirect('/bandwidth/advanced');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
    }

    /**
     * Disables bandwidth rule.
     *
     * @param string $name name
     *
     * @return view
     */

    function disable($iface, $address_type, $port_type, $ip, $port, $priority, $upstream, $upstream_ceil, $downstream, $downstream_ceil)
    {
        $this->load->library('bandwidth/Bandwidth');

        try {
            $this->bandwidth->set_advanced_rule_state(FALSE, $iface, $address_type, $port_type, $ip, 
                $port, $priority, $upstream, $upstream_ceil, $downstream, $downstream_ceil
            );

            $this->page->set_status_disabled();
            redirect('/bandwidth/advanced');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
    }

    /**
     * Enables bandwidth rule
     *
     * @param string $name name
     *
     * @return view
     */

    function enable($iface, $address_type, $port_type, $ip, $port, $priority, $upstream, $upstream_ceil, $downstream, $downstream_ceil)
    {
        $this->load->library('bandwidth/Bandwidth');

        try {
            $this->bandwidth->set_advanced_rule_state(TRUE, $iface, $address_type, $port_type, $ip, 
                $port, $priority, $upstream, $upstream_ceil, $downstream, $downstream_ceil
            );

            $this->page->set_status_enabled();
            redirect('/bandwidth/advanced');
        } catch (Exception $e) {
            $this->page->view_exception($e);
            return;
        }
    }
}
