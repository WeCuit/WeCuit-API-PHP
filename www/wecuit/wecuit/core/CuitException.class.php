<?php
class CuitException extends Exception{
    private $data;
    const errMsg = array(
        0 => 'Unknown ERR',
        10500 => 'Server Net ERR',
        60401 => '未登录计算中心'

    );
    function __construct($msg = '', $code = 0, Throwable $previous = null, $data = null)
    {
        // $this->errMsg[$code];
        parent::__construct($msg, $code, $previous);
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}