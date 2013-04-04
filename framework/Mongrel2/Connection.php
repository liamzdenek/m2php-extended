<?php

namespace Mongrel2;

class Connection extends \Pollable
{
    private $sender_id;
    public $on_recv;
    public $serv;

    public function __construct($sender_id, $sub_addr, $pub_addr, $serv, $on_recv, $rcv_timeout=10)
    {
        $this->sender_id = $sender_id;

        $ctx = new \ZMQContext();
        $reqs = $ctx->getSocket(\ZMQ::SOCKET_UPSTREAM);
        $reqs->connect($sub_addr);
        $reqs->setSockOpt(\ZMQ::SOCKOPT_RCVTIMEO, $rcv_timeout);

        $resp = $ctx->getSocket(\ZMQ::SOCKET_PUB);
        $resp->connect($pub_addr);
        $resp->setSockOpt(\ZMQ::SOCKOPT_IDENTITY, $sender_id);

        $this->sub_addr = $sub_addr;
        $this->pub_addr = $pub_addr;

        $this->reqs = $reqs;
        $this->resp = $resp;

        $this->serv    = $serv;
        $this->on_recv = $on_recv;
    }

    public function on_poll()
    {
        if($recv = $this->reqs->recv())//\ZMQ::MODE_DONTWAIT))
        {
            $req = Request::parse($recv);
            $recv = $this->on_recv;
            $recv($this->serv,$req);
            return true;
        }
        return false;
    }

    public function reply($req, $msg)
    {
        $this->send($req->sender, $req->conn_id, $msg);
    }

    public function send($uuid, $conn_id, $msg)
    {
        $header = sprintf('%s %d:%s,', $uuid, strlen($conn_id), $conn_id);
        $this->resp->send($header . " " . $msg);
    }

    public function reply_http($req, $body, $code = 200, $status = "OK", $headers = null)
    {
        $this->reply($req, Tool::http_response($body, $code, $status, $headers));
    }

    public function deliver($uuid, $idents, $data)
    {
        $this->send($uuid, join(' ', $idents),  $data);
    }

    public function deliver_http($uuid, $idents, $body, $code = 200, $status = "OK", $headers = null)
    {
        $this->deliver($uuid, $idents, Tool::http_response($body, $code, $status, $headers));
    }

}
