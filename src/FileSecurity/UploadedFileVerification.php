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
        $size = $file["size"];
        if ($size >= $config['limit']) {
            $data['info']='图片大小不符合要求！';
            return $data;
        }
        if(!in_array($file['type'],array_values($config['type']))){
            $data['info']='不允许的文件格式！';
            return $data;
        }
        $fileAttr = $this->getUploadedFileType($file['tmp_name']);
        echo  $fileAttr;
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
    private function getUploadedFileType($file){
        $finfo=finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo,$file);
        finfo_close($finfo);
        return $mime;
    }

    private function validateFileName( $filename ) {
        $filename2HtmlEntities = preg_replace("/\\\\u([0-9abcdef]{4})/", "&#x$1;", $filename);
        $filenameUTF8 = mb_convert_encoding($filename2HtmlEntities, 'UTF-8', 'HTML-ENTITIES');
        $specialChars = array( '?', '[', ']', '/', '\\', '=', '<', '>', ':', ';', ',', "'", '"', '&', '$', '#', '*', '(', ')', '|', '~', '`', '!', '{', '}', '%', '+', chr( 0 ) );
        foreach ($specialChars as $specialChar){
            if(strpos($specialChar,$filenameUTF8)!==false){
                return false;
            }
        }
        return true;
    }
}