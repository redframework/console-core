<?php
/** Red Framework
 * Filter Class
 * @author RedCoder
 * http://redframework.ir
 */

namespace Red\SanitizeService;


class Sanitize
{

    private static $roles = array();

    public static function initialize(){
        Rules::rules();
    }

    public static function sanitize($string, $method)
    {
        if ($method == 'digit') {
            return preg_replace('/([0-9])+/', '', $string);
        } else if ($method == 'space') {
            $string = preg_replace('/([\s\n])+/', '', $string);
            return $string;
        }

        foreach (self::$roles as $role){
            if ($role['role'] == $method){
                $result = call_user_func_array($role['callback'], [$string]);
                return $result;
            }
        }

        return FALSE;
    }

    /**
     * @param string $role
     * @param callable $callback
     * @return bool
     */
    public static function addRole($role, $callback)
    {

        array_push(self::$roles, ['role' => $role, 'callback' => $callback]);
        return TRUE;
    }

    /**
     * @return mixed
     */
    public static function getRoles()
    {
        return self::$roles;
    }


}