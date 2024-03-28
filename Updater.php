<?php

namespace TypechoPlugin\ThemeUpdater;

use Widget\Base\Contents;
use Typecho\Widget\Helper\Form;
use Utils\Helper;

/**
 * 主题更新
 */
class Updater extends Contents
{
    public function panel()
    {
        $step = $this->request->step;
        $latest = $this->request->latest;
        if (method_exists($this, $step))
            call_user_func(array($this, $step));
        else
            $this->zero();
    }

    //第一步先备份
    public function first()
    {
        echo "1";
        // // 检测ZipArchive扩展是否启用
        // if (!class_exists('ZipArchive', false)) {
        //     echo '<p>PHP ZipArchive 扩展未启用。</p>';
        //     return;
        // }
        // $themeDir = __TYPECHO_ROOT_DIR__ . __TYPECHO_THEME_DIR__;

        // $backupFile = $themeDir . '/icefox_backup.zip';
        // $icefoxThemeDir = $themeDir . '/icefox';

        // $zip = new \ZipArchive();
        // if ($zip->open($backupFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
        //     $files = new \RecursiveIteratorIterator(
        //         new \RecursiveDirectoryIterator($icefoxThemeDir),
        //         \RecursiveIteratorIterator::LEAVES_ONLY
        //     );

        //     foreach ($files as $name => $file) {
        //         if (!$file->isDir()) {
        //             $filePath = $file->getRealPath();
        //             $relativePath = substr($filePath, strlen($icefoxThemeDir) + 1);
        //             $zip->addFile($filePath, $relativePath);
        //         }
        //     }

        //     $zip->close();

        //     echo "1";
        //     // echo '<p>主题备份成功。你可以在这里下载备份文件: <a href="'.$themeDir.'/icefox_backup.zip">icefox_backup.zip</a></p>';
        // } else {
        //     echo '0';
        // }
    }

    //第二步下载新版本
    public function second()
    {
        $latest = $_GET['latest'];

        $themeDir = __TYPECHO_ROOT_DIR__ . __TYPECHO_THEME_DIR__;
        $tempDir = $themeDir . "/temp/";
        if (!is_dir($tempDir)) {
            if (!mkdir($tempDir, 0777, true)) {
                echo "临时文件夹创建失败";
            }
        }

        $temp = $tempDir . 'latest.zip';

        $source = fopen($latest, "rb");
        if ($source)
            $target = fopen($temp, "wb");
        if ($target) {
            while (!feof($source)) {
                $res = fwrite($target, fread($source, 1024 * 8), 1024 * 8);
                if (!$res) {
                    echo "错误";
                    return;
                }
            }
        }
        if ($source)
            fclose($source);
        if ($target)
            fclose($target);

        echo "1";
        // $ch = curl_init($latest);

        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        // curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function ($resource, $download_size, $downloaded, $upload_size, $uploaded) {
        //     static $previousProgress = 0;
        //     if ($download_size > 0) {
        //         $progress = round($downloaded / $download_size * 100);
        //         if ($progress > $previousProgress) {
        //             $previousProgress = $progress;
        //             // echo "下载进度: $progress%\n";
        //         }
        //     }
        // });

        // // 打开本地文件句柄，准备写入
        // $fp = fopen($temp, 'w+');

        // // 设置cURL选项，将数据写入文件
        // curl_setopt($ch, CURLOPT_FILE, $fp);

        // // 执行cURL会话
        // curl_exec($ch);

        // // 关闭cURL会话
        // curl_close($ch);

        // // 关闭本地文件句柄
        // fclose($fp);

        // echo "1";
        // if(file_put_contents($temp, file_get_contents($latest))){
        //     echo $temp;
        // }
        // else{
        // 	echo "0";
        // }
    }

    //第三步解压新版本
    public function third()
    {
        $zip = new \ZipArchive();
        $themeDir = __TYPECHO_ROOT_DIR__ . __TYPECHO_THEME_DIR__;
        $tempDir = $themeDir . "/temp/";

        // 打开ZIP文件
        if ($zip->open($tempDir . 'latest.zip') === TRUE) {
            // 解压到指定目录
            $zip->extractTo($themeDir . '/icefox');

            // 关闭ZIP文件
            $zip->close();

            echo "1";
        } else {
            echo "0";
        }
    }

    //第四步清空临时文件
    public function fourth()
    {
        $themeDir = __TYPECHO_ROOT_DIR__ . __TYPECHO_THEME_DIR__;
        $tempDir = $themeDir . "/temp/";
        $file = $tempDir . 'latest.zip';

        // if(is_file($file)){
        // 	unlink($file);
        // }

        echo "1";
    }
}

?>