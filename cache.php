<?php
class cache
{
    protected $param = [];
    public function __construct($param = [])
    {
        $this->param = [
            // 目录分割符
            'dirDs'        =>DIRECTORY_SEPARATOR,
            // 有效期时间秒 0表示永久
            'entime'        => 0,
            // 是否分区
            'subdir'        => true,
            // 缓存文件名前缀
            'prefix'        => 'c',
            // 缓存文件后缀
            'dirext'        => '.php',
            // 缓存路径
            'path'          => dirname(__FILE__).DIRECTORY_SEPARATOR.'cache',
            // 是否启用压缩
            'compress' => false,
        ];
        if (!empty($param)) {
            $this->param = array_merge($this->param, $param);
        }
        // 检查目录
        $this->checkdir();
    }
    /**
     * [getCacheName 获取缓存文件名]
     * @param  [type] $key [缓存索引]
     */
    protected function getCacheName($key){
        $key=md5($key);
        $name=$this->param['prefix'].$key.$this->param['dirext'];
        if ($this->param['subdir']) {
            // 使用子目录
            $name=$this->param['prefix'].substr($key,0,1).$this->param['dirDs'].$name;
        }
        return $name;
    }
    /**
     * [checkdir 检查目录]
     * @return [type] [description]
     */
    private function checkdir()
    {
        // 没有则创建项目缓存目录
        if (!is_dir($this->param['path'])) {
            if (mkdir($this->param['path'], 0755, true)) {
                return true;
            }
        }
        return false;
    }
    /**
     * [cache 缓存入口]
     * @param  [all]   $key    [缓存索引]
     * @param  string  $value  [缓存值]
     * @param  integer $entime [有效期：秒]
     */
    public function cache($key, $value='',$entime=0){
        // 判断是否设置时间，否则以全局配置为主
        if ((int)$entime === 0) {
            $entime=$this->param['entime'];
        }
        // 拼接文件路径
        $filePath = $this->param['path'].$this->param['dirDs'].$this->getCacheName($key);
        // 写入缓存
        if ($value !== '') {
            // 值为null时删除缓存
            if (is_null($value)) {
                $this->delfile($filePath);
                return false;
            }
            $dir = dirname($filePath);
            // 判断目录是否存在
            if (!is_dir($dir)) {
                mkdir($dir,0777);
            }
            $jsonData = json_encode($value,JSON_UNESCAPED_UNICODE);
            $data = serialize($jsonData);
            if ($this->param['compress'] && function_exists('gzcompress')) {
                //数据压缩 
                $data = gzcompress($data,3);
            }
            return file_put_contents($filePath,$data);

        // 读取缓存    
        }else{
            if (!is_file($filePath)) {
                return false;
            }else{
                $data = file_get_contents($filePath);
                if ($data) {
                    if (0 != $entime && $_SERVER['REQUEST_TIME'] > filemtime($filePath) + $entime) {
                        $this->delfile($filePath);
                        return false;
                    }
                    $data = file_get_contents($filePath);
                    if ($this->param['compress'] && function_exists('gzcompress')) {
                        //解压数据
                        $data = gzuncompress($data);
                    }
                    $content = unserialize($data);
                    // 解析json字符串并返回
                    return json_decode($content,true);
                }else{
                    return false;
                }
                
            }
        }
    }
    /**
     * [delfile 删除文件]
     * @param  [string] $path [被删除文件完整路径]
     */
    private function delfile($path)
    {
        return is_file($path) && unlink($path);
    }
}