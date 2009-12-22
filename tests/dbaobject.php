<?php
/**
 * @package midgardmvc_helper_ragnaland
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

require_once('tests/testcase.php');

require_once('ragnaroek/lib/midcom/baseclasses/core/object.php');
require_once('ragnaroek/lib/midcom/core/dbaobject.php');

/**
 * @package midgardmvc_helper_ragnaland
 */
class midgardmvc_helper_ragnaland_tests_dbaobject extends midgardmvc_tests_testcase
{
    public function test_methods()
    {
        $mgdschema = new midgard_reflection_class('midgard_person');
        
        $mgdschema_methods = $mgdschema->getMethods();
        foreach ($mgdschema_methods as $key => $method)
        {
            $this->assertTrue(method_exists('midcom_core_dbaobject', $method->name), "Method {$method->name} must be defined in midcom_core_dbaobject");
        }
    }
}
