<?php
/**
 * @package midgardmvc_helper_ragnaland
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

require_once(dirname(__FILE__) . '/../../tests/helpers.php');

/**
 * @package midgardmvc_helper_ragnaland
 */
class midgardmvc_helper_ragnaland_tests_all
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite("Midgard MVC " . __CLASS__);
        
        $tests = midgardmvc_core_tests_helpers::get_tests(__FILE__, __CLASS__);
        foreach ($tests as $test)
        {
            $suite->addTestSuite($test);
        }
 
        return $suite;
    }
}
