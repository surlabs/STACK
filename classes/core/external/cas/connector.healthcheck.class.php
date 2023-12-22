<?php
// This file is part of Stack - http://stack.maths.ed.ac.uk/
//
// Stack is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Stack is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Stack.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This class supports the healthcheck functions..
 *
 * @copyright  2023 The University of Edinburgh
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace classes\core\external\cas;

use classes\core\external\cas\castext2\castext2_evaluatable;
use classes\platform\StackPlatform;

class stack_cas_healthcheck {
    /* This variable holds the state of the healthcheck. */
    protected $ishealthy = true;

    protected $config = null;

    protected $tests = array();

    public function __construct($config) {
        global $CFG;
        $this->config = $config;

        // Record the platform in the summary.
        $test = array();
        $test['tag'] = 'platform';
        $test['result'] = null;
        $test['summary'] = $config->platform;
        $test['details'] = null;
        $this->tests[] = $test;

        // Check if the current options for library packages are permitted (maximalibraries).
        list($result, $message, $livetestcases) = stack_cas_configuration::validate_maximalibraries();
        // The livetestcases are used below, once we have a live maxima or image ready to test.
        if (!$result) {
            $this->ishealthy = false;
            $test = array();
            $test['tag'] = 'settingmaximalibraries';
            $test['result'] = $result;
            $test['summary'] = $message;
            $test['details'] = StackPlatform::createTag('p', $message);
            $test['details'] .= StackPlatform::createTag('p', StackPlatform::getTranslation('settingmaximalibraries_failed', null));
            $test['details'] .= StackPlatform::createTag('p', StackPlatform::getTranslation('settingmaximalibraries_desc', null));
            $this->tests[] = $test;
        }

        // Try to connect to create maxima local.
        stack_cas_configuration::create_maximalocal();

        // Make sure we are in a position to call maxima.
        switch ($config->platform) {
            case 'win':
                $maximalocation = stack_cas_configuration::confirm_maxima_win_location();
                if ('' != $maximalocation) {
                    $test = array();
                    $test['tag'] = 'stackmaximalibraries';
                    $test['result'] = null;
                    $test['summary'] = null;
                    $test['details'] = StackPlatform::createTag('p', StackPlatform::getTranslation('healthcheckconfigintro1', null).' '.
                        StackPlatform::createTag('tt', $maximalocation));
                    $this->tests[] = $test;
                } else {
                    $this->ishealthy = false;
                    $test = array();
                    $test['result'] = false;
                    $test['summary'] = "Could not confirm the location of Maxima";
                    $this->tests[] = $test;
                }

                stack_cas_configuration::copy_maxima_bat();

                if (!is_readable($CFG->dataroot . '/stack/maxima.bat')) {
                    $this->ishealthy = false;
                    $test = array();
                    $test['tag'] = 'healthcheckmaximabat';
                    $test['result'] = false;
                    $test['summary'] = StackPlatform::getTranslation('healthcheckmaximabatinfo', $CFG->dataroot);
                    $test['details'] = StackPlatform::createTag('p', StackPlatform::getTranslation('healthcheckmaximabatinfo', $CFG->dataroot));
                    $this->tests[] = $test;
                }

                break;
            case 'linux':
                // On a raw linux server list the versions of Maxima available.
                $connection = stack_connection_helper::make();
                $test = array();
                $test['tag'] = 'healthcheckmaximaavailable';
                $test['result'] = null;
                $test['summary'] = null;
                $test['details'] = StackPlatform::createTag('pre', $connection->get_maxima_available());
                $this->tests[] = $test;
                break;
            case 'server':
                if (!empty($CFG->proxyhost) && !is_proxybypass(StackPlatform::getConfig('maximacommandserver'))) {
                    $test = [];
                    $test['tag'] = 'healthcheckproxysettings';
                    $test['result'] = null;
                    $test['summary'] =
                    $this->tests[] = $test;
                    break;
                }
            default:
                // Server-proxy/optimised.
                // TODO: add in any specific tests for these setups?
                break;
        }

        // Record the contents of the maximalocal file.
        if ($this->ishealthy) {
            $test = array();
            $test['tag'] = 'healthcheckmaximalocal';
            $test['result'] = null;
            $test['summary'] = null;
            $test['details'] = StackPlatform::createTag('textarea', stack_cas_configuration::generate_maximalocal_contents(),
                array('readonly' => 'readonly', 'wrap' => 'virtual', 'rows' => '32', 'cols' => '100'));
            $this->tests[] = $test;
        }

        // Test an *uncached* call to the CAS.  I.e. a genuine call to the process.
        if ($this->ishealthy) {
            list($message, $genuinedebug, $result) = stack_connection_helper::stackmaxima_genuine_connect();
            $this->ishealthy = $result;

            $test = array();
            $test['tag'] = 'healthuncached';
            $test['result'] = $result;
            $test['summary'] = $message;
            $test['details'] = StackPlatform::createTag('p', StackPlatform::getTranslation('healthuncachedintro', null)) . $message;
            $test['details'] .= $genuinedebug;
            $this->tests[] = $test;
        }

        // Test Maxima connection.
        if ($this->ishealthy) {
            // Intentionally use get_string for the sample CAS and plots, so we don't render
            // the maths too soon.
            $this->output_cas_text('healthcheckconnect',
                StackPlatform::getTranslation('healthcheckconnectintro', null), get_string('healthchecksamplecas', 'qtype_stack'));
            $this->output_cas_text('healthcheckconnectunicode',
                StackPlatform::getTranslation('healthcheckconnectintro', null), get_string('healthchecksamplecasunicode', 'qtype_stack'));
            $this->output_cas_text('healthcheckplots',
                StackPlatform::getTranslation('healthcheckplotsintro', null), get_string('healthchecksampleplots', 'qtype_stack'));
        }

        // If we have a linux machine, and we are testing the raw connection then we should
        // attempt to automatically create an optimized maxima image on the system.
        if ($this->ishealthy && $config->platform === 'linux') {
            list($message, $debug, $result, $commandline, $rawcommand)
                = stack_connection_helper::stackmaxima_auto_maxima_optimise($genuinedebug);
            $test = array();
            $test['tag'] = 'healthautomaxopt';
            $test['result'] = $result;
            $test['summary'] = $message;
            $test['details'] = StackPlatform::createTag('p', StackPlatform::getTranslation('healthautomaxoptintro', null));
            $test['details'] .= StackPlatform::createTag('pre', $debug);
            $this->tests[] = $test;
        }

        if ($this->ishealthy) {
            list($message, $details, $result) = stack_connection_helper::stackmaxima_version_healthcheck();
            $test = array();
            $test['tag'] = 'healthchecksstackmaximaversion';
            $test['result'] = $result;
            $test['summary'] = StackPlatform::getTranslation($message, $details);
            $test['details'] = StackPlatform::getTranslation($message, $details);
            $this->tests[] = $test;
        }

        // Check that each library really is loaded into the current connection.
        if ($this->ishealthy) {
            // At this point everything _should_ be working so we use a regular session connection.
            $s = array();
            foreach ($livetestcases as $lib => $test) {
                $s[$lib] = stack_ast_container::make_from_teacher_source($test, 'test_library', new stack_cas_security());
            }
            $session = new stack_cas_session2($s);
            if ($session->get_valid()) {
                $session->instantiate();
            }

            $result = true;
            $message = 'healthchecksstacklibrariesworkingok';
            $details = '';
            if ($session->is_instantiated()) {
                $failed = array();
                foreach ($livetestcases as $lib => $test) {
                    // We assume the maxima expression testing each library must return true if and only if it works.
                    if ($s[$lib]->get_value() != 'true') {
                        $failed[] = $lib;
                    }
                }
                if ($failed != array()) {
                    $this->ishealthy = false;
                    $result = false;
                    $message = 'healthchecksstacklibrariesworkingfailed';
                    $details = array('err' => implode(', ', $failed));
                }
            } else {
                $this->ishealthy = false;
                $result = false;
                $message = 'healthchecksstacklibrariesworkingsession';
                $details = array('err' => $session->get_errors(true));
            }

            $test = array();
            $test['tag'] = 'healthchecksstacklibrariesworking';
            $test['result'] = $result;
            $test['summary'] = StackPlatform::getTranslation($message, $details);
            $test['details'] = StackPlatform::getTranslation($message, $details);
            $this->tests[] = $test;
        }
        // List the requested maxima packages in the summary.
        $test = array();
        $test['tag'] = 'settingmaximalibraries';
        $test['result'] = null;
        $test['summary'] = $config->maximalibraries;
        $test['details'] = null;
        $this->tests[] = $test;

        // Record whether caching is taking place in the summary.
        $test = array();
        $test['tag'] = 'settingcasresultscache';
        $test['result'] = null;
        $test['summary'] = StackPlatform::getTranslation('healthcheckcache_' . $config->casresultscache, null);
        $test['details'] = null;
        $this->tests[] = $test;
    }

    /*
     * Try and evaluate the raw castext and build a result entry.
     */
    private function output_cas_text($title, $intro, $castext) {
        $ct = castext2_evaluatable::make_from_source($castext, 'healthcheck');
        $session = new stack_cas_session2([$ct]);
        $session->instantiate();

        $test = array();
        $test['tag'] = $title;
        $test['result'] = null;
        $test['summary'] = null;
        $test['details'] = StackPlatform::createTag('p', $intro) . StackPlatform::createTag('pre', $castext);

        if ($session->get_errors()) {
            $this->ishealthy = false;
            $test['result'] = false;
            $test['summary'] = StackPlatform::getTranslation('errors', null) . $ct->get_errors();
            $test['details'] .= StackPlatform::getTranslation('errors', null) . $ct->get_errors();
            $test['details'] .= StackPlatform::getTranslation('debuginfo', null) . $session->get_debuginfo();
        } else {
            $test['details'] .= StackPlatform::createTag('p', stack_ouput_castext($ct->get_rendered()));
        }
        $this->tests[] = $test;
    }

    /*
     * This function returns a summary of the status of the healthcheck.
     */
    public function get_test_results() {
        return $this->tests;
    }

    /*
     * Return overall results.
     */
    public function get_overall_result() {
        return $this->ishealthy;
    }
}
