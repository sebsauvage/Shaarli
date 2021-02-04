<?php

use Shaarli\Bookmark\Bookmark;

/**
 * Hook for test.
 *
 * @param array $data - data passed to plugin.
 *
 * @return mixed altered data.
 */
function hook_test_random($data)
{
    if (isset($data['_PAGE_']) && $data['_PAGE_'] == 'test') {
        $data[1] = 'page test';
    } elseif (isset($data['_LOGGEDIN_']) && $data['_LOGGEDIN_'] === true) {
        $data[1] = 'loggedin';
    } elseif (array_key_exists('_LOGGEDIN_', $data)) {
        $data[1] = 'loggedin';
        $data[2] = $data['_LOGGEDIN_'];
    } else {
        $data[1] = $data[0];
    }

    return $data;
}

function hook_test_error()
{
    new Unknown();
}

function test_register_routes(): array
{
    return [
        [
            'method' => 'GET',
            'route' => '/test',
            'callable' => 'getFunction',
        ],
        [
            'method' => 'POST',
            'route' => '/custom',
            'callable' => 'postFunction',
        ],
    ];
}

function hook_test_filter_search_entry(Bookmark $bookmark, array $context): bool
{
    return $context['_result'];
}
