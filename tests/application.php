<?php
/**
 * @package midgardmvc_helper_ragnaland
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

require_once('tests/testcase.php');

require_once('ragnaroek/lib/midcom/application.php');

/**
 * @package midgardmvc_helper_ragnaland
 */
class midgardmvc_helper_ragnaland_tests_application extends midgardmvc_tests_testcase
{
    public function test_methods()
    {
        $connection = new midgard_reflection_class('midgard_connection');
        
        $methods_not_proxied = array
        (
            '__construct',
            '__destruct',
            'get_instance',
            'open',
            'open_config',
            'connect',
            'get_user',
            'list_auth_types',
        );
        
        $legacy_methods = array
        (
            'get_sitegroup',
            'set_sitegroup',
            'get_lang',
            'set_lang',
            'set_default_lang',
            'get_default_lang',
        );
        
        $connection_methods = $connection->getMethods();
        foreach ($connection_methods as $key => $method)
        {
            if (in_array($method->name, $methods_not_proxied))
            {
                // Constructor, destructor and get_instance don't need to be proxied
                continue;
            }
            $this->assertTrue(method_exists('midcom_application', $method->name), "Method {$method->name} must be defined in midcom_application");
        }
        
        foreach ($legacy_methods as $method)
        {
            $this->assertTrue(method_exists('midcom_application', $method), "Method {$method} must be defined in midcom_application");
        }
    }
}
