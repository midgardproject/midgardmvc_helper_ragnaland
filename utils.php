<?php
/**
 * Register PHP function as string formatter to the Midgard formatting engine.
 * @see http://www.midgard-project.org/documentation/reference-other-mgd_register_filter/
 */
function mgd_register_filter($name, $function)
{
}

/**
 * Invalidate Midgard's element cache
 *
 * @todo Caching the elements found by mgd_element() might be a good idea
 */
function mgd_cache_invalidate()
{
}

/**
 * Include an element
 */
function mgd_element($name)
{
    static $style = null;

    $element = $name[1];

    // Sensible fallback if we don't have a style or ROOT element
    $root_fallback = '<html><head><?php $_MIDCOM->print_head_elements(); ?><title><?php echo $_MIDCOM->get_context_data(MIDCOM_CONTEXT_PAGETITLE); ?></title></head><body><?php $_MIDCOM->content(); $_MIDCOM->uimessages->show(); $_MIDCOM->toolbars->show(); ?></body></html>';

    switch ($element)
    {
        case 'title':
        case 'content':
            return midcom_core_midcom::get_instance()->context->page->$element;
            break;
        default:
            // TODO: Element inheritance, page elements
            if (!isset(midcom_core_midcom::get_instance()->context->style))
            {
                if ($element == 'ROOT')
                {
                    return $root_fallback;
                }
                return '';
            }
            $style = midcom_core_midcom::get_instance()->context->style;
            $qb = new midgard_query_builder('midgard_element');
            $qb->add_constraint('name', '=', $element);
            $qb->add_constraint('style', '=', $style->id);
            $elements = $qb->execute();
            if (count($elements) == 0)
            {
                if ($element == 'ROOT')
                {
                    return $root_fallback;
                }
                return '';
            }
            else
            {
                $value = $elements[0]->value;
            }
            return preg_replace_callback("/<\\(([a-zA-Z0-9 _-]+)\\)>/", 'mgd_element', $value);
    }
}

/**
 * Show a variable
 */
function mgd_variable($variable)
{
    //echo "<br />\nxxX{$variable[1]}Xxx";
    $variable_parts = explode(':', $variable[1]);
    // TODO: Formatter support
    return "<?php echo \${$variable_parts[0]}; ?>";
}

/**
 * Preparse a string to handle element inclusion and variable 
 *
 * @see mgd_preparse
 * @see http://trac.midgard-project.org/ticket/1222
 */
function mgd_preparse_compat($code)
{
    // Get style elements
    $code = preg_replace_callback("/<\\(([a-zA-Z0-9 _-]+)\\)>/", 'mgd_element', $code);
    // Echo variables
    $code = preg_replace_callback("%&\(([^)]*)\);%i", 'mgd_variable', $code);
    return $code;
}
?>