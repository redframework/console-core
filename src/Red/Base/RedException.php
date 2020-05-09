<?php
/** Red Framework
 * Exception Base Class
 * @author REDCODER
 * http://redframework.ir
 */

namespace Red\Base;


class RedException extends \Exception
{
    protected $exception_no = "Red Exception";

    public function __construct($exception_no, $exception_message, $code = 0, \Exception $previous = null) {
        parent::__construct($exception_message, $code, $previous);
        $this->exception_no = $exception_no;
    }


    public function getExceptionNo()
    {
        return $this->exception_no;
    }


    public function __toString() {
        return __CLASS__ . " : [{$this->exception_no}] : {$this->message}\n";
    }
}
