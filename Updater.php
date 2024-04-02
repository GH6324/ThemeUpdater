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

        $saveAs = $tempDir . 'latest.zip';

        try {
            if (is_file($saveAs)) {
                unlink($saveAs);
            }
        } catch (Exception $exception) {

        }

        $ch = curl_init($latest);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// 如果是HTTPS连接，可以考虑关闭SSL证书验证
        // curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        // curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // 允许重定向
        // 执行cURL会话
        $response = curl_exec($ch);

        // 检查是否有错误发生
        if (curl_errno($ch)) {
            // cURL错误处理
            echo '网络异常，下载失败';
        } else {
            // 保存文件到磁盘
            if (file_put_contents($saveAs, $response) === false) {
                // 文件写入错误处理
                echo '下载新版本出现错误';
            } else {
                echo '1';
            }
        }

        // 关闭cURL会话
        curl_close($ch);

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
        include "class-pclzip.php";
        $themeDir = __TYPECHO_ROOT_DIR__ . __TYPECHO_THEME_DIR__;
        $tempDir = $themeDir . "/temp/";
        $file = $tempDir . "latest.zip";

        $dir = dirname($file);

        $zip = new \PclZip($file);

        if (!$zip->extract(\PCLZIP_OPT_PATH, $dir) === 0) {
            echo "0";
        } else {
            // 更新
            $lastestDir = $dir;
            if (is_dir($lastestDir))
                $this->moveFileOrFolder($lastestDir, $themeDir);

            echo "1";
        }
    }

    //第四步清空临时文件
    public function fourth()
    {
        $themeDir = __TYPECHO_ROOT_DIR__ . __TYPECHO_THEME_DIR__;
        $tempDir = $themeDir . "/temp/";
        $file = $tempDir . 'latest.zip';

        try {
            if (is_file($file)) {
                unlink($file);
            }
        } catch (Exception $exception) {

        }

        echo "1";
    }
    /**
     * 移动文件或文件夹到指定目录
     *
     * @param string $sourcePath 源文件或文件夹的路径
     * @param string $destinationPath 目标目录的路径
     * @return bool 返回移动操作是否成功
     */
    private function moveFileOrFolder($sourcePath, $destinationPath)
    {
        if (!file_exists($sourcePath)) {
            return false; // 源文件或文件夹不存在
        }

        if (is_dir($sourcePath)) {
            // 如果是文件夹，使用递归方式移动
            if (file_exists($destinationPath)) {
                // 如果目标文件夹已存在，则先删除它
                if (!self::removeDirectory($destinationPath)) {
                    return false; // 删除目标文件夹失败
                }
            }

            // 创建目标文件夹
            if (!mkdir($destinationPath, 0777, true)) {
                return false; // 创建目标文件夹失败
            }

            $items = glob($sourcePath . '/*');
            foreach ($items as $item) {
                $newDestination = $destinationPath . '/' . basename($item);
                if (!moveFileOrFolder($item, $newDestination)) {
                    return false; // 递归移动失败
                }
            }

            // 移动完成后删除源文件夹
            return @rmdir($sourcePath);
        } else {
            // 如果是文件，直接移动
            return rename($sourcePath, $destinationPath);
        }
    }
    /**
     * 递归删除目录及其内容
     *
     * @param string $directoryPath 目录路径
     * @return bool 返回删除操作是否成功
     */
    function removeDirectory($directoryPath)
    {
        if (!file_exists($directoryPath)) {
            return true; // 目录不存在，无需删除
        }

        if (!is_dir($directoryPath)) {
            return false; // 如果不是目录，无法删除
        }

        $items = array_diff(scandir($directoryPath), ['.', '..']);
        foreach ($items as $item) {
            $itemPath = $directoryPath . '/' . $item;
            if (is_dir($itemPath)) {
                if (!self::removeDirectory($itemPath)) {
                    return false; // 递归删除失败
                }
            } else {
                if (!unlink($itemPath)) {
                    return false; // 删除文件失败
                }
            }
        }

        return @rmdir($directoryPath); // 删除目录
    }
}
?>