<?php

/**
 * Plugin addlink_toolbar.
 * Adds the addlink input on the linklist page.
 */

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
                    'placeholder' => 'URI',
                ),
                array(
                    'type' => 'submit',
                    'value' => 'Add link',
                    'class' => 'bigbutton',
                ),
            ),
        );
        $data['fields_toolbar'][] = $form;
    }

    return $data;
}
