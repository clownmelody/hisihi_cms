<?php
/**
 * Created by PhpStorm.
 * Author: RFly
 * Date: 2/4/15
 * Time: 4:20 PM
 */

namespace App\Exception;
use Think\Exception;

class ReturnException extends Exception {
    private $result;

    public function __construct($return) {
        $this->result = $return;
    }

    public function getResult() {
        return $this->result;
    }
}