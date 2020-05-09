<?php
/** Red Framework
 * Standard Database Bad Query Exception
 * @author REDCODER
 * http://redframework.ir
 */


namespace Red\StandardExceptions\DB;


use Red\Base\RedException;

class BadQueryException extends RedException
{

    public function __construct($exception_message, $code = 0, \Exception $previous = null) {
        $exception_no = "Database Query Failure !";
        parent::__construct($exception_no, $exception_message, $code, $previous);
    }

}
