<?php
/**
 * Created by MOMOSEC.
 * User: thecastle <https://github.com/IIComing>
 * Date: 2019/5/16
 * Time: 下午6:11
 */
namespace Security\FileSecurity;

/**
 * Class UploadedFileVerification
 * @package Security\FileSecurity
 */
class UploadedFileVerification{

    /**
     * 上传文件安全校验
     * @param $file
     * @param $config
     *$config=array('limit'=>5 * 1024 * 1024,
     *    'type'=>array(
     *         "gif"=>"image/gif",
     *         "jpg"=>"image/jpeg",
     *         "png"=>"image/png")
     *);
     * @return array
     */
    public function verifyUploadFile($file, $config){
        $data=array('flag'=>false,'info'=>'上传失败！','ext'=>'');
        if(!isset($file) || empty($file) || count($file)==0){
            return $data;
        }
        if (isset($file["error"]) && $file["error"] > 0) {
            return $data;
        }
        if($this->validateFileName($file['name'])!==true){
            $data['info']='文件名包含非法字符！';
            return $data;
        }
        $extension=substr(strrchr($file['name'], '.'), 1);
        if(!in_array($extension,array_keys($config['type']))){
            $data['info']='不允许的文件后缀！';
            return $data;
        }

        if(!in_array($file['type'],array_values($config['type']))){
            $data['info']='不允许的文件格式！';
            return $data;
        }
        if($file['type']!==$config['type'][$extension]){
            $data['info']='文件后缀与MIME类型不一致！';
            return $data;
        }

        $size = $file["size"];
        if ($size >= $config['limit']) {
            $data['info']='图片大小不符合要求！';
            return $data;
        }

        $fileAttr = $this->getUploadedFileType($file['tmp_name']);
        if(!$fileAttr){
            $data['info']='不允许的文件格式！';
            return $data;
        }

        foreach($config['type'] as $key=> $value){
            if($fileAttr===$value){
                $data['ext']='.'.$key;
                $data['info']='上传成功！';
                $data['flag']=true;
                break;
            }
        }
        return $data;
    }

    /** 获取上传文件类型
     * @param $file 上传文件临时路径
     * @return mixed
     */
    private function getUploadedFileType($file){
        $finfo=finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo,$file);
        finfo_close($finfo);
        return $mime;
    }

    /**
     * 文件名归一化后，1、如果包含特殊字符，拒绝。2、文件后缀名超过1个，拒绝
     * @param $file 上传文件名
     * @return bool
     */
    function validateFileName($file ) {

        $parts=explode(".",$file);
        $filename=array_shift($parts);
        $filename2HtmlEntities = preg_replace("/\\\\u([0-9abcdef]{4})/", "&#x$1;", $filename);
        $filenameUTF8 = mb_convert_encoding($filename2HtmlEntities, 'UTF-8', 'HTML-ENTITIES');
        $specialChars = array( '?', '[', ']', '/', '\\', '=', '<', '>', ':', ';', ',', "'", '"', '&', '$', '#', '*', '(', ')', '|', '~', '`', '!', '{', '}', '%', '+', chr( 0 ) );
        foreach ($specialChars as $specialChar){
            if(strpos($specialChar,$filenameUTF8)!==false){
                return false;
            }
        }
        if(count($parts)>1){
            return false;
        }
        return true;
    }
}