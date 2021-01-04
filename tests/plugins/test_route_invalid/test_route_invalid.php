<?php

function test_route_invalid_register_routes(): array
{
    return [
        [
            'method' => 'GET',
            'route' => 'not a route',
            'callable' => 'getFunction',
        ],
    ];
}
