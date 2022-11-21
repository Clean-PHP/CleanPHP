<?php
namespace library\upload;

/**
 * Class FileUpload
 * Created By ankio.
 * Date : 2022/11/14
 * Time : 10:57 上午
 * Description : 文件上传处理类
 */

class Upload {
    public string $path = '';  //上传文件保存的路径
    public array $allow_type = ['jpg','gif','png','jpeg']; //设置限制上传文件的类型
    public int $max_size = 1000000; //限制文件上传大小（字节）
    public bool $check_php = false;//是否检测php
    private array $uploads = [];

    /**
     * 调用该方法上传文件，如果出现问题则抛出异常。
     * @throws UploadException
     */
    function upload()
    {
        /* 检查文件路径合法 */
        $this->checkFilePath();
        /* 格式化文件上传数组 */
        $upload_files = $this->formatFiles($_FILES);
        //检查文件字段
        if(sizeof($upload_files)==0){
            throw new UploadException("没有任何文件被上传。",10000);
        }
        $this->uploads = [];
        //执行上传文件
        foreach ($upload_files as $upload){
            $file = $this->setFiles($upload['name'],$upload['tmp_name'],$upload['size'],$upload['error']);

            if($upload['error']!==0) throw new UploadException($file->error,$upload['error']+10020,$file);
            /* 上传之前先检查一下大小 */
            $this->checkFileSize($file);
            /* 检查文件类型 */
            $this->checkFileType($file);
            /* 检查php代码 */
            if($this->check_php)  $this->checkIsPHP($file);
            /* 为上传文件设置新文件名 */
            $this->setNewFileName($file);
           /** 复制临时文件到目标目录 */
            $this->copyFile($file);
            /**  加入上传队列 */
            $this->uploads[] = $file;
        }

    }

    /**
     * 返回上传的数据数组
     * @return array 只上传一个文件也返回数组，数组类型是{@link UploadFile}
     */
    function getUploadFiles(): array
    {
        return $this->uploads;
    }


    /**
     * 匹配是否为PHP
     * 大小写亦可
     * 匹配16进制中的 <% %>
     * 匹配16进制中的 <? ?>
     * 匹配16进制中的 <script /script>
     * 匹配16进制中的 <?php ?>
     * 匹配16进制中的 <?PHP ?>
     * 匹配16进制中的 <SCRIPT /SCRIPT>
     * @param UploadFile $uploadFile
     * @return void
     * @throws UploadException
     */
    private function checkIsPHP(UploadFile $uploadFile)
    {
        $hexCode = bin2hex(file_get_contents($uploadFile->tmp_name));
        if (preg_match("/3C3F706870|3F3E|3C3F|3C736372697074|2F7363726970743E|3C25|253E|3C3F504850|3C534352495054|2F5343524950543E/is", $hexCode)) {
            throw new UploadException("检测到PHP代码",10003,$uploadFile);
        }
    }
    /**
     * 检查文件大小
     * @param UploadFile $file
     * @throws UploadException
     */
    private function checkFileSize(UploadFile &$file)
    {
        if(!$this->max_size > $file->size){
           throw new UploadException("文件超过最大值：{$this->max_size}",10001,$file);
        }
    }

    /**
     * 判断是否为允许的文件类型
     * @param UploadFile $file
     * @throws UploadException
     */
   private function checkFileType(UploadFile &$file)
    {
        if(!in_array(strtolower($file->type), $this->allow_type)){
            throw new UploadException("未允许的上传类型",10001,$file);
        }
    }
    /**
     * 格式化文件上传数组
     * @param $files
     * @return array
     */
    private function formatFiles($files): array
    {
        $file_array = array();
        $n = 0;
        foreach ($files as $key => $file)
        {
            if (is_array($file['name']))
            {
                $keys = array_keys($file);
                $count = count($file['name']);
                for ($i = 0; $i < $count; $i++)
                {
                    $file_array[$n]['key'] = $key;
                    foreach ($keys as $_key)
                    {
                        $file_array[$n][$_key] = $file[$_key][$i];
                    }
                    $n++;
                }
            }
            else
            {
                $file_array[$n] = $file;
                $file_array[$n]['key'] = $key;
                $n++;
            }
        }

        return $file_array;
    }




    /**
     * 设置和$_FILES有关的内容
     * @param string $name
     * @param string $tmp_name
     * @param int $size
     * @param int $error_num
     * @return UploadFile
     */

    private function setFiles(string $name="", string $tmp_name="", int $size=0, int $error_num=0): UploadFile
    {
        $result = strrchr($tmp_name,'.');
        $ext = "";
        if($result !== false)
            $ext = substr($result,1);
        switch ($error_num){
            case 4: $error = "没有文件被上传"; break;
            case 3: $error = "文件只有部分被上传"; break;
            case 2: $error = "上传文件的大小超过了HTML表单中MAX_FILE_SIZE选项指定的值"; break;
            case 1: $error = "上传的文件超过了php.ini中upload_max_file_size选项限制的值"; break;
            default: $error = "上传成功";
        }

        return new UploadFile([
            "error"=>$error,
            "name"=>$name,
            "tmp_name"=>$tmp_name,
            "size"=>$size,
            "type"=>$ext
        ]);
    }


    /**
     * 设置上传后的文件名称
     */

    private function setNewFileName(UploadFile &$file) {
        $file->new_name = uniqid("upload_").$file->type;
    }


    /**
     * 检查是否有存放上传文件的目录
     * @throws UploadException
     */

    private function checkFilePath()
    {
        if(empty($this->path)){
           throw new UploadException("指定的上传路径为空");
        }
        if(!is_dir($this->path)){
           mkdir($this->path,0777,true);
        }
    }


    /**
     * 复制上传文件到指定的位置
     * @param UploadFile $file
     * @return void
     * @throws UploadException
     */

    private function copyFile(UploadFile &$file): void
    {
        if (!move_uploaded_file($file->tmp_name, $this->path) && !copy($file->tmp_name, $this->path))
            throw new UploadException("文件写入失败，可能由于目标目录没有写入权限",10002,$file);
    }

}