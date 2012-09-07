<?php

/**
 * Bandwidth state controller.
 *
 * @category   Apps
 * @package    Bandwidth
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
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

use \clearos\apps\base\Daemon as Daemon;

///////////////////////////////////////////////////////////////////////////////
// C L A S S
///////////////////////////////////////////////////////////////////////////////

/**
 * Bandwidth enable/disable controller.
 *
 * @category   Apps
 * @package    Bandwidth
 * @subpackage Controllers
 * @author     ClearFoundation <developer@clearfoundation.com>
 * @copyright  2012 ClearFoundation
 * @license    http://www.gnu.org/copyleft/gpl.html GNU General Public License version 3 or later
 * @link       http://www.clearfoundation.com/docs/developer/apps/bandwidth/
 */

class Server extends ClearOS_Controller
{
    /**
     * Default controller.
     *
     * @return view
     */

    function index()
    {
        // Load dependencies
        //------------------

        $this->lang->load('base');
        $this->lang->load('bandwidth');

        $data['daemon_name'] = lang('bandwidth_app_name');
        $data['app_name'] = 'bandwidth';

        // Load views
        //-----------

        $options['javascript'] = array(clearos_app_htdocs('base') . '/daemon.js.php');

        $this->page->view_form('base/daemon', $data, lang('base_server_status'), $options);
    }

    /**
     * Status.
     *
     * @return view
     */

    function status()
    {
        header('Cache-Control: no-cache, must-revalidate');
        header('Content-type: application/json');

        $this->load->library('bandwidth/Bandwidth');

        $status['status'] = ($this->bandwidth->get_engine_state()) ? Daemon::STATUS_RUNNING : Daemon::STATUS_STOPPED;

        echo json_encode($status);
    }

    /**
     * Start bandwidth engine.
     *
     * @return view
     */

    function start()
    {
        $this->load->library('bandwidth/Bandwidth');

        try {
            $this->bandwidth->set_engine_state(TRUE);
        } catch (Exception $e) {
            //
        }
    }

    /**
     * Stop bandwidth engine.
     *
     * @return view
     */

    function stop()
    {
        $this->load->library('bandwidth/Bandwidth');

        try {
            $this->bandwidth->set_engine_state(FALSE);
        } catch (Exception $e) {
            //
        }
    }
}
