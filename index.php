<?php
require $_SERVER['DOCUMENT_ROOT'].'/cache.php';
$config=[
			// 目录分割符
            'dirDs'        =>DIRECTORY_SEPARATOR,
            // 有效期时间秒 0表示永久
            'entime'        => 0,
            // 是否分区
            'subdir'        => true,
            // 缓存文件后缀
            'dirext'        => '.php',
            // 缓存路径
            'path'          => dirname(__FILE__).DIRECTORY_SEPARATOR.'/cache',
            // 是否启用压缩
            'compress' => true,
        ];
$cache=new cache($config);
$dxycontent = file_get_contents('http://www.baidu.com/');
// print_r($dxycontent);die;
$cache->cache('a',$dxycontent);
print_r($cache->cache('a'));
