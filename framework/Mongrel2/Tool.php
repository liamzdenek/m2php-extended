<?php

namespace Mongrel2;

class Tool
{
    static public function parse_netstring($ns)
    {
        list($len, $rest) = explode(':', $ns, 2);
        $len = intval($len);

        return array(
            substr($rest, 0, $len),
            substr($rest, $len+1)
        );
    }

    static public function http_response($body, $code, $status, $headers)
    {
        $http = "HTTP/1.1 %s %s\r\n%s\r\n%s";

        if (is_null($headers)) {
            $headers = array();
        }
        $headers[] = "Content-Length: ".strlen($body);
        $hd = "";
        foreach($headers as $v) {
            $hd .= sprintf("%s\r\n", $v);
        }
        return sprintf($http, $code, $status, $hd, $body);
    }
}
