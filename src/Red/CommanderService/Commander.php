<?php
/** Red Framework
 * Commander Class
 *
 * @author RedCoder
 * @version 1.0
 * @copyright 2019
 * http://redframework.ir
 */

namespace Red\CommanderService;

use Red\Output\Output;
use Red\EnvironmentProvider\Environment;

class Commander
{
    /**
     * @var array
     */
    private static $commands = array();

    /**
     * @param $command
     * @param $command_action
     */
    public static function register($command, $command_action)
    {

        $command = explode(" ", $command);

        if (isset($command[1])) {
            $parameters = array_slice($command, 1);

        }

        $command = $command[0];


        //Check If Any Parameter Is Inserted
        if (isset($parameters)) {
            self::$commands[$command] = array(
                'action' => $command_action,
                'parameters' => $parameters,
            );
        } else {
            self::$commands[$command] = array(
                'action' => $command_action,
            );
        }
    }

    public static function initialize($application_theme)
    {

        // Saving Route Analytics as JSON for Red Analytics Java App If Production Mode is On

        if (strtolower(Environment::get("PROJECT", "State")) == "production") {

            if (count(self::$commands) > 0){
                $route_analytics = json_encode(self::$commands, TRUE);
            } else {
                $route_analytics = "{}";
            }


            $file_handler = fopen(ROOT_PATH . 'storage' . DS . 'Analytics' . DS . 'Commands.json', "w");
            fwrite($file_handler, $route_analytics);
            fclose($file_handler);
        }


        $project_state = strtolower(Environment::get('PROJECT', 'State'));

        echo("\e[" . $application_theme . ";40m
    |**************************************************************************
    | " . Environment::get("PROJECT", "Name") . "
    |--------------------------------------------------------------------------
    | Version " . Environment::get("PROJECT", "Version") . "
    |--------------------------------------------------------------------------
    ");

        if ($project_state == "maintenance") {
            Environment::set("DEBUG", "Errors", "off");
        } else if ($project_state == "break") {
            Environment::set("DEBUG", "Errors", "off");
            echo("| Project is On Maintenance Break !" . PHP_EOL . "    |" . PHP_EOL . "    |**************************************************************************");
            echo "\e[" . Output::white . ";40m";
            echo(PHP_EOL);
            exit();
        }

        if (count(self::$commands)){
            echo "| Available Commands :" . PHP_EOL . "    |";

            $counter = 1;
            foreach (self::$commands as $command => $command_config) {
                echo PHP_EOL . "    | " . $command . " ";
                if (isset($command_config['parameters'])) {
                    foreach ($command_config['parameters'] as $parameter) {
                        echo $parameter . " ";
                    }
                }
                if ($counter == count(self::$commands)) {
                    echo PHP_EOL . "    |" . PHP_EOL .
                        "    |**************************************************************************"
                        . PHP_EOL . PHP_EOL;
                }
                $counter++;
            }

            echo "\e[" . Output::white . ";40m";

            while (true) {
                self::getCommand();
            }
        } else {
            echo("| No Command Has Been Found !" . PHP_EOL . "    |" . PHP_EOL . "    |**************************************************************************");
            echo(PHP_EOL);
            echo "\e[" . Output::white . ";40m";
            exit();
        }


    }

    public static function getCommand()
    {
        echo PHP_EOL . "    --> ";
        $handler = fopen("php://stdin", "r");
        $command = fgets($handler);

        $command = trim($command);

        static::callCommand($command);
    }

    /**
     * Route
     *
     * @param $command_from_user
     * @return bool
     *
     */

    public static function callCommand($command_from_user)
    {
        $command_from_user = trim($command_from_user);
        if ($command_from_user == '') {
            return false;
        }

        //Check If Any Command Registered
        if (count(self::$commands) > 0) {

            $command_from_user = explode(" ", $command_from_user);

            if (isset($command_from_user[1])) {
                $parametric = true;
                $parameters = array_slice($command_from_user, 1);
                $parameters_count = count($parameters);
            }


            foreach (self::$commands as $command => $config) {

                if ($command == $command_from_user[0]) {

                    if (isset($config['parameters'])){
                        $filtered_parameters_count = count(self::filterOptionalParams($config['parameters']));


                    if ($filtered_parameters_count > 0) {
                        if (isset($parametric)) {
                            if ($filtered_parameters_count > $parameters_count) {
                                Output::printC("No Such Command Found");
                                return false;
                            }
                        } else {
                            Output::printC("No Such Command Found");
                            return false;
                        }

                    }
                    }

                    if (is_callable($config['action'])) {

                        if (isset($parametric)){
                            call_user_func_array($config['action'], $parameters);
                        } else {
                            call_user_func($config['action']);
                        }

                        return TRUE;

                    } else {
                        $CM = explode("@", $config['action']);
                        $controller = $CM[0];
                        $method = $CM[1];

                        $namespace = "App" . DS . "Controllers" . DS . $controller;

                        if (file_exists($namespace . '.php')) {
                            $controller_instance = new $namespace();
                        } else {
                            if (strtolower(Environment::get("DEBUG", "Errors")) == "on") {
                                Output::printC("You Defined A Command Action Which Does Not Exists");
                            } else {
                                Output::printC("Sorry ! Unexpected Error Occurred");
                            }

                            return FALSE;
                        }


                        if (method_exists($controller_instance, $method)) {

                            if (isset($parametric) && isset($parameters_count)) {
                                call_user_func_array(array($controller_instance, $method), $parameters);
                            } else {
                                $controller_instance->$method();
                            }

                            return TRUE;
                        } else {
                            if (strtolower(Environment::get("DEBUG", "Errors")) == "on") {
                                Output::printC("You Defined A Command Action Which Does Not Exists");
                            } else {
                                Output::printC("Sorry ! Unexpected Error Occurred");
                            }
                            return FALSE;
                        }
                    }
                }
            }

            Output::printC("No Such Command Found");


        } else {
            Output::printC("No Command Found");
        }
        return FALSE;
    }


    public static function filterOptionalParams($params)
    {

        $parameters = [];
        foreach ($params as $param) {
            if (substr($param, 0, 1) != "?") {
                array_push($parameters, $param);
            }
        }

        return $parameters;

    }


}

