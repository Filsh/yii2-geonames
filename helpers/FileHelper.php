<?php

namespace filsh\geonames\helpers;

class FileHelper extends \yii\helpers\FileHelper
{
    /**
     * Lead the files found under the http link and return tmp file.
     * @param string $fileUrl the file under which will be loaded.
     * @param string $dst the destination directory.
     * @param array $options options for load file. Valid options are:
     *
     * - destDir: string, the destination directory
     * - onLoad: callback, a PHP callback that is called for on file loaded.
     * @return strin path to temp loaded file.
     */
    public static function loadFile($fileUrl, $options = [])
    {
        $file = static::createUniqueFile(isset($options['destDir']) ? $options['destDir'] : null);
        if($file === false) {
            return false;
        }
        
        $handle = @fopen($file, 'w');
        $curlOptions = [
            CURLOPT_FILE            => $handle,
            CURLOPT_TIMEOUT         => 10 * 60,
            CURLOPT_URL             => $fileUrl,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_HTTPHEADER      => [
                'User-Agent: Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0',
            ],
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, $curlOptions);
        curl_exec($ch);
        fclose($handle);
        
        if (isset($options['onLoad'])) {
            $result = call_user_func($options['onLoad'], $file);
            if (is_bool($result)) {
                return $result;
            }
        } else {
            return $file;
        }
        
        return false;
    }
    
    /**
     * Create temp file
     * @param string $dst the destination directory. If not provided should use system temp directory.
     * @return boolean|string path to created file or false.
     */
    public static function createUniqueFile($dst = null)
    {
        if($dst !== null) {
            if (!is_dir($dst)) {
                static::createDirectory($dst);
            }
        } else {
            $dst = sys_get_temp_dir();
        }
        
        $file = $dst.'/'.uniqid().'_'.time();
        
        if(!touch($file)) {
            return false;
        }
        
        return $file;
    }
}