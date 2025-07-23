<?php

if (!function_exists('get_request_value')) {
    function get_request_value($key)
    {
        $data = request()->all();
        return $data[$key] ?? null;
    }
}
