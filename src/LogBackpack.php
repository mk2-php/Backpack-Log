<?php

/**
 * ===================================================
 * 
 * PHP Framework "Mk2"
 *
 * LogBackpack
 * 
 * Backpack class base file.
 * 
 * URL : https://www.mk2-php.com/
 * 
 * Copylight : Nakajima-Satoru 2021.
 *           : Sakaguchiya Co. Ltd. (https://www.teastalk.jp/)
 * 
 * ===================================================
 */

namespace mk2\backpack_log;

use Mk2\Libraries\Backpack;

class LogBackpack extends Backpack{

    public $temporary=MK2_PATH_TEMPORARY;

    public $path="log";
    public $fileName="{Y}{m}{d}.log";
    public $errorFileName="error-{Y}{m}{d}.log";

    public $header="{datetime} {request.method} {request.root} {response.code} {request.query} {request.remoteip} {response.message}";

    /**
     * out
     * @param string $message
     * @param $option = null
     */
    public function out($message, $option=null){
        $this->_out($message,$option);
        return $this;
    }

    /**
     * error
     * @param string $exception
     * @param $option = null
     */
    public function error($exception, $option = null){
        $option["fileName"] = $this->errorFileName;
        return $this->out($exception,$option);
    }

    /**
     * _out
     * @param $message
     * @param $option
     */
    private function _out($message, $option){

        $temporary=$this->temporary;
        if(!empty($option["temporary"])){
            $temporary = $option["temporary"];
        }

        $path=$this->path;
        if(!empty($option["path"])){
            $path = $option["path"];
        }

        $filePath = $temporary."/".$path;

        if(!is_dir($filePath)){
            mkdir($filePath,0777,true);
        }

        $fileName=$this->fileName;
        if(!empty($option["fileName"])){
            $fileName=$option["fileName"];
        }

        $fileName=$this->_convertFileName($fileName);
        
        $header=$this->header;
        if(!empty($option["header"])){
            $header=$option["header"];
        }

        error_log($this->_convert($message,$header),3,$filePath."/".$fileName);

    }

    /**
     * _convertFileName
     * @param $fileName
     */
    private function _convertFileName($fileName){

        $fileName=str_replace("{Y}",date("Y"),$fileName);
        $fileName=str_replace("{m}",date("m"),$fileName);
        $fileName=str_replace("{d}",date("d"),$fileName);
        $fileName=str_replace("{h}",date("h"),$fileName);
        $fileName=str_replace("{i}",date("i"),$fileName);
        $fileName=str_replace("{s}",date("s"),$fileName);

        return $fileName;
    }

    /**
     * _convert
     * @param $message
     * @param $headers
     */
    private function _convert($message,$headers){
        
        $headers=str_replace("{datetime}",date_format(date_create("now"),"Y/m/d H:i:s"),$headers);
        $headers=str_replace("{request.method}",$this->Request->params("method"),$headers);
        $headers=str_replace("{request.root}",$this->Request->params("root"),$headers);
        $headers=str_replace("{request.remoteip}",$this->Request->params("remoteIp"),$headers);
        $headers=str_replace("{request.port}",$this->Request->params("port"),$headers);
        $headers=str_replace("{request.url}",$this->Request->params("url"),$headers);
        $headers=str_replace("{request.host}",$this->Request->params("host"),$headers);
        $headers=str_replace("{request.controller}",$this->Request->params("controller"),$headers);
        $headers=str_replace("{request.action}",$this->Request->params("action"),$headers);
        $headers=str_replace("{request.path}",$this->Request->params("path"),$headers);
        $headers=str_replace("{request.protocol}",$this->Request->params("protocol"),$headers);

        if($this->Request->params("method")=="GET"){
            if($this->Request->query()->exists()){
                $headers=str_replace("{request.query}",json_encode($this->Request->query()->get(),JSON_UNESCAPED_UNICODE),$headers);
            }
            else{
                $headers=str_replace("{request.query}","",$headers);
            }
            $headers=str_replace("{request.body}","",$headers);
        }
        else{
            $headers=str_replace("{request.query}","",$headers);
            if($this->Request->post()->exists()){
                $headers=str_replace("{request.body}",json_encode($this->Request->post()->get(),JSON_UNESCAPED_UNICODE),$headers);
            }
            else if($this->Request->put()->exists()){
                $headers=str_replace("{request.body}",json_encode($this->Request->put()->get(),JSON_UNESCAPED_UNICODE),$headers);
            }
            else if($this->Request->delete()->exists()){
                $headers=str_replace("{request.body}",json_encode($this->Request->delete()->get(),JSON_UNESCAPED_UNICODE),$headers);
            }
        }

        $headers=str_replace("{response.code}",$this->Response->getCode(),$headers);

        $headers=str_replace("{response.message}",$message,$headers);

        return $headers."\n";
    }
}
