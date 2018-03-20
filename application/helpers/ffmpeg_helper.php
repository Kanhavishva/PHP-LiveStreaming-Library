<?php

/**
 * Helper Library for task related to FFMPEG, including Process management
 */

/**
 * Dependencies Loaded
 */
use Cocur\BackgroundProcess\BackgroundProcess;

/**
 * Installation path of ffmpeg
 */
define('FFMPEG_PATH', 'ffmpeg');

/**
 * Complete Command should be something like this
 *
 * e.g. ffmpeg -re -i ./emi.mp4 -acodec copy -vcodec copy -f flv "rtmp://live-api-a.facebook.com:80/rtmp/1734066443569412?ds=1&a=ATi-uY_zz48mRNqw"
 *
 */
define('FFMPEG_STREAM_COMMPAND_PART_1', FFMPEG_PATH.' -re -i ');
/**
 * 2nd part of the FFMPEG COMMAND.
 */
define('FFMPEG_STREAM_COMMPAND_PART_2', ' -acodec copy -vcodec copy -f flv ');

/**
 * Extra Tweaking Variables
 */
define('ENV_IS_DEBUG', true);
/**
 * To determine which type of commands should be used, Based on the HOST OS.
 * It must be configured.
 */
define('IS_LINUX', TRUE);


/**
 * Starts Stream from @source to @destination using ffmpeg on RTMP Protocol
 * Returns true if success and false if something goes wrong.
 * @param $source
 * @param $destination
 * @return bool|Process
 */
function FFMPEG_START_STREAM($source, $destination) {
    ini_set('max_execution_time', 0);
    set_time_limit(0);

    $response = false;
    $streamCommand_string = "";    //Streaming URL Rendering

    try {   //Just incase something comes unexpected

        $streamCommand_string .= FFMPEG_STREAM_COMMPAND_PART_1;
        $streamCommand_string .= '"'.$source.'"';
        $streamCommand_string .= FFMPEG_STREAM_COMMPAND_PART_2;
        $streamCommand_string .= '"'.$destination.'"';

        $process = new BackgroundProcess($streamCommand_string);
        $process->run();

        return $process;    //return whole process obj

    } catch (Exception $exception) {    //Overall any type of exception.
        //In case Debuging active
        if(ENV_IS_DEBUG) {
            echo $exception->getCode().": ".$exception->getMessage();
        }
        return false;
    }

    return $response;
}

/**
 * Terminate the started process via PID
 * @param $PID
 * @return bool
 */
function FFMPEG_KILL_PROCESS($PID) {

    if(IS_LINUX) {  //Select command according to environment.
        $kill_command = "pkill ";
    } else {
        $kill_command = "taskkill /PID ";
    }

    $kill_command .= $PID;

    $process = BackgroundProcess::createFromPID($PID);
    $process->isRunning(); // -> true
    return $process->stop();      // -> true
}

