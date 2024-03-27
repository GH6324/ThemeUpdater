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
        // 检测ZipArchive扩展是否启用
        if (!class_exists('ZipArchive', false)) {
            echo '<p>PHP ZipArchive 扩展未启用。</p>';
            return;
        }
        $themeDir = __TYPECHO_ROOT_DIR__ . __TYPECHO_THEME_DIR__;

        $backupFile = $themeDir . '/icefox_backup.zip';
        $icefoxThemeDir = $themeDir . '/icefox';

        $zip = new \ZipArchive();
        if ($zip->open($backupFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($icefoxThemeDir),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath =$file->getRealPath();
                    $relativePath = substr($filePath, strlen($icefoxThemeDir) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();

            // echo '<p>主题备份成功。你可以在这里下载备份文件: <a href="'.$themeDir.'/icefox_backup.zip">icefox_backup.zip</a></p>';
        } else {
            echo '<p>无法创建备份文件。</p>';
        }
        echo "1";
    }

    //第二步下载新版本
    public function second()
    {        
        $latest = $_GET['latest'];
        
        $themeDir = __TYPECHO_ROOT_DIR__ . __TYPECHO_THEME_DIR__;
        $tempDir = $themeDir . "/temp/";
        if(!is_dir($tempDir)){
            if(!mkdir($tempDir, 0777, true)){				
				echo "临时文件夹创建失败";
            }
        }
        
        $temp = $tempDir . basename($latest);
        
        if(file_put_contents($temp, file_get_contents($latest))){
            echo $temp;
        }
        else{
			echo "0";
        }
    }

    //第三步解压新版本
    public function third()
    {
        $zip = new \ZipArchive();
        $themeDir = __TYPECHO_ROOT_DIR__ . __TYPECHO_THEME_DIR__;
        $tempDir = $themeDir . "/temp/";
        
        // 打开ZIP文件
        echo $tempDir;
		if ($zip->open($tempDir.'latest.zip') === TRUE) {
			// 解压到指定目录
			$zip->extractTo($themeDir);

			// 关闭ZIP文件
			$zip->close();

			echo "文件解压成功";
		} else {
			echo "无法打开ZIP文件";
		}
    }

    //第四步清空临时文件
    public function fourth()
    {
        $themeDir = __TYPECHO_ROOT_DIR__ . __TYPECHO_THEME_DIR__;
        $tempDir = $themeDir . "/temp/";
        
        $this->clean($this->_dir . "/temp", true);
        if (!mkdir($this->_dir . "/temp", 0755, true)) {
            return false; // 创建目标文件夹失败
        }
    }
}

?>