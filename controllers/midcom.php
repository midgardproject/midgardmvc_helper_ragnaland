<?php
/**
 * @package org_routamc_positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Controller running MidCOM 8.09 Ragnaroek
 *
 * @package org_routamc_positioning
 */
class midgardmvc_helper_ragnaland_controllers_midcom
{
    public function __construct(midgardmvc_core_component_interface $instance)
    {
        $this->configuration = $instance->configuration;
    }

    /**
     * Prepare $_MIDGARD superglobal to be Ragnaroek-compatible
     *
     * @see http://www.midgard-project.org/documentation/midgard-php-superglobals/
     */
    private function prepare_midgard_superglobal()
    {
        $midgardmvc = midgardmvc_core::get_instance();
        $_MIDGARD = array();
        
        // User information
        if ($midgardmvc->authentication->is_user())
        {
            $_MIDGARD['user'] = $midgardmvc->authentication->get_person()->id;
            $_MIDGARD['admin'] = $midgardmvc->authentication->get_user()->is_admin();
            $_MIDGARD['root'] = $midgardmvc->authentication->get_user()->is_admin();
            $_MIDGARD['auth'] = true;
        }
        else
        {
            $_MIDGARD['user'] = 0;
            $_MIDGARD['admin'] = false;
            $_MIDGARD['root'] = false;
            $_MIDGARD['auth'] = false;
        }
        $_MIDGARD['cookieauth'] = false;
        
        
        // URLs and request path
        $_MIDGARD['uri'] = $midgardmvc->context->uri;
        $_MIDGARD['self'] = $midgardmvc->context->self;
        $_MIDGARD['prefix'] = substr($midgardmvc->context->self, 0, -1);
        $_MIDGARD['argv'] = $midgardmvc->context->argv;
        $_MIDGARD['argc'] = count($_MIDGARD['argv']);
        
        // General host setup
        $_MIDGARD['lang'] = 0;
        $_MIDGARD['sitegroup'] = 0;
        $_MIDGARD['page'] = $midgardmvc->context->page->id;
        $_MIDGARD['debug'] = false;
        
        $_MIDGARD['host'] = null;
        $_MIDGARD['style'] = $midgardmvc->context->templatedir_id;
        $_MIDGARD['author'] = 0;
        $_MIDGARD['config'] = array
        (
            'prefix' => '',
            'multilang' => false,
            'quota' => false,
            'sitegroup' => false,
            'ragnaland' => true,
        );

        $_MIDGARD['schema'] = array
        (
            'types' => array(),
        );
        $types = $midgardmvc->dispatcher->get_mgdschema_classes();
        foreach ($types as $type)
        {
            $_MIDGARD['schema']['types'][$type] = '';
        }

        $_MIDGARD['config']['unique_host_name'] = $midgardmvc->templating->get_cache_identifier();
        $_MIDGARD['config']['auth_cookie_id'] = $midgardmvc->context->page->id;

        $_MIDGARD_CONNECTION = $midgardmvc->dispatcher->get_midgard_connection();
        
        $_GET = $midgardmvc->context->query;
    }

    /**
     * Match midgard_error log levels to MidCOM 8.09 levels
     *
     * @param string $log_level midgard_error log level
     * @return int
     */
    private function midgardmvc_loglevel_to_midcom($log_level)
    {
        switch ($log_level)
        {
            case 'debug':
                // MIDCOM_LOG_DEBUG
                return 4;
            case 'message':
            case 'info':
                // MIDCOM_LOG_INFO
                return 3;
            case 'warning':
                // MIDCOM_LOG_WARN
                return 2;
            case 'error':
                // MIDCOM_LOG_ERROR
                return 1;
            case 'critical':
                // MIDCOM_LOG_CRIT
                return 0;
        }
        return 0;
    }
  
    private function prepare_midcom_config()
    {
        $GLOBALS['midcom_config_local'] = array();
        $GLOBALS['midcom_config_local']['log_filename'] = '/home/bergie/devel/runtime/project/log/ragnaroek.log';
        $GLOBALS['midcom_config_local']['log_level'] = $this->midgardmvc_loglevel_to_midcom(midgardmvc_core::get_instance()->configuration->log_level);
        $GLOBALS['midcom_config_local']['midcom_root_topic_guid'] = $this->get_midcom_root_topic_guid();
        $GLOBALS['midcom_config_local']['midcom_services_rcs_enable'] = false;
        
        if (midgardmvc_core::get_instance()->firephp)
        {
            // Enable FirePHP in MidCOM 8.09 too
            $GLOBALS['midcom_config_local']['log_firephp'] = true;
        }
    }
    
    private function get_midcom_root_topic_guid()
    {
        $qb = new midgard_query_builder('midgard_topic');
        $qb->add_constraint('name', '=', midgardmvc_core::get_instance()->context->page->guid);
        $topics = $qb->execute();
        if ($topics)
        {
            return $topics[0]->guid;
        }
        
        // Create a new root topic for MidCOM 8.09
        // The convention is that the root topic's name matches GUID of the page used to run MidCOM
        $topic = new midgard_topic();
        $topic->name = midgardmvc_core::get_instance()->context->page->guid;
        $topic->component = 'net.nehmer.static';
        $topic->extra = midgardmvc_core::get_instance()->context->page->guid;
        $topic->title = midgardmvc_core::get_instance()->context->page->guid;
        $topic->create();
        return $topic->guid;
    }

    private function initialize_midcom()
    {
        if (!ini_get('midgard.superglobals_compat'))
        {
            throw new Exception('You need to set midgard.superglobals_compat=On in your php.ini to run MidCOM 8.09 on Midgard MVC');
        }
        
        if (!class_exists('midgard_topic'))
        {
            throw new Exception('You need to install midgardmvc_helper_ragnaland MgdSchemas from configuration/mgdschema.xml to run MidCOM 8.09 on Midgard MVC');
        }

        if (!defined('MIDCOM_ROOT'))
        {
            define('MIDCOM_ROOT', MIDGARDMVC_ROOT . '/ragnaroek/lib');
        }
        $this->prepare_midgard_superglobal();
        $this->prepare_midcom_config();
        
        ini_set('memory_limit', '68M');
        
        if (!function_exists('mgd_register_filter'))
        {
            require(MIDGARDMVC_ROOT . '/midgardmvc_helper_ragnaland/utils.php');
        }
        
        try
        {
            // Send reasonable default content type, MidCOM may still override this
            midgardmvc_core::get_instance()->dispatcher->header('Content-type: text/html; charset=utf-8');
            
            // Load Ragnaroek MidCOM
            require_once MIDCOM_ROOT . '/midcom.php';

            // Run request processing
            $_MIDCOM->codeinit();
        }
        catch (Exception $e)
        {
            // TODO: In some cases we may want to recast exceptions
            throw $e;
        }
    }

    /**
     * Run a GET request to MidCOM 8.09
     */
    public function get_run(array $args)
    {
        $this->initialize_midcom();
        
        // Run Ragnaroek pseudo-templating
        eval('?>' . mgd_preparse('<(ROOT)>'));
        die();
    }

    /**
     * Run a POST request to MidCOM 8.09
     */
    public function post_run(array $args)
    {
        // Same handling as GET
        $this->get_run($args);
    }
}
?>
