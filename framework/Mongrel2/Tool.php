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
        
        $hd = "";
        if(!is_null($headers))
        {
            foreach($headers as $v) {
                $hd .= sprintf("%s\r\n", $v);
            }
        }

        return "HTTP/1.1 $code $status\r\n".
            $hd.
            "Content-Length: ".strlen($body)."\r\n\r\n".
            $body;

    }
}
