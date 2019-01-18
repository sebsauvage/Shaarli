<?php

/**
 * Plugin addlink_toolbar.
 * Adds the addlink input on the linklist page.
 */

use Shaarli\Router;

/**
 * When linklist is displayed, add play videos to header's toolbar.
 *
 * @param array $data - header data.
 *
 * @return mixed - header data with addlink toolbar item.
 */
function hook_addlink_toolbar_render_header($data)
{
    if ($data['_PAGE_'] == Router::$PAGE_LINKLIST && $data['_LOGGEDIN_'] === true) {
        $form = array(
            'attr' => array(
                'method' => 'GET',
                'action' => '',
                'name'   => 'addform',
                'class'  => 'addform',
            ),
            'inputs' => array(
                array(
                    'type' => 'text',
                    'name' => 'post',
                    'placeholder' => t('URI'),
                ),
                array(
                    'type' => 'submit',
                    'value' => t('Add link'),
                    'class' => 'bigbutton',
                ),
            ),
        );
        $data['fields_toolbar'][] = $form;
    }

    return $data;
}

/**
 * This function is never called, but contains translation calls for GNU gettext extraction.
 */
function addlink_toolbar_dummy_translation()
{
    // meta
    t('Adds the addlink input on the linklist page.');
}
