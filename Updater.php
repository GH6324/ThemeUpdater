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
//        // 设置页面标题
//        $step = $this->request->step;
//        $latest = $this->request->latest;
//        $html = '<div class="main"><ul></ul></div>' . $step;
//        $this->response->throwContent($html, 'text/html');
        if (method_exists($this, $this->request->step))
            call_user_func(array($this, $this->request->step));
        else
            $this->zero();
    }

    //升级初章开启进程线
    public function zero()
    {
        $url = Helper::options()->siteUrl . "index.php/themeUpdater/";
        $adminUrl = Helper::options()->adminUrl;
        ?>
        <h2>更新icefox主题</h2>
        <div id="progress"></div>
        <script type="text/javascript">
            function update() {
                var steps = ['first', 'second', 'third', 'fourth', 'fifth', 'sixth'];
                var notices = [
                    '正在备份当前版本',
                    '正在下载最新版本',
                    '正在解压缩升级文件',
                    '正在复制升级最新版本',
                    '正在清除临时文件',
                    'icefox主题更新成功'
                ];

                function printmsg(text, loading) {
                    loading = arguments.length > 1 ? loading : true;
                    text = loading ? text + "<span class='loading'>...</span>" : text;
                    text = "<p>" + text + "</p>";
                    document.getElementById("progress").innerHTML += text;
                }

                function ajax(url, callback) {
                    var xhr;
                    if (window.XMLHttpRequest) xhr = new XMLHttpRequest();
                    else if (window.ActiveXObject) xhr = new ActiveXObject("Microsoft.XMLHTTP");
                    else return alert("Your browser does not support XMLHTTP.");
                    ;

                    xhr.onreadystatechange = function () {
                        if (xhr.readyState == 4) {
                            if (xhr.status == 200) callback(xhr.responseText);
                            else return alert("Network Error.");
                        }
                    }
                    xhr.open("GET", url, true);
                    xhr.send(null);
                }

                (function step(s) {
                    [].slice.call(document.querySelectorAll(".loading")).forEach(function (item) {
                        item.innerHTML = '....';
                        item.className = ''
                    });
                    //终章结语
                    if (s == steps.length) {
                        setTimeout(function () {
                            location.href = "<?php echo $adminUrl; ?>";
                        }, 2000);
                        return printmsg("欢迎使用最新版icefox主题，我们将自动为你跳转到后台。如果没有自动跳转，请<a href='<?php echo $adminUrl; ?>'>点击这里</a>。", false);
                    }
                    printmsg(notices[s]);
                    ajax("<?php echo $url; ?>" + steps[s], function (data) {
                        if (data != "") return printmsg(data);
                        step(s + 1);
                    });
                })(0)

                setInterval(function () {
                    l = document.querySelector(".loading");
                    if (l.innerHTML.length == 4) l.innerHTML = '';
                    l.innerHTML += '.';
                }, 500);
            }

            update();
        </script>
        <?php
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
        echo 'icefox主题目录' . $icefoxThemeDir;

        $zip = new ZipArchive();
        if ($zip->open($backupFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($icefoxThemeDir),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath =$file->getRealPath();
                    $relativePath = substr($filePath, strlen($icefoxThemeDir) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();

            echo '<p>主题备份成功。你可以在这里下载备份文件: <a href="'.Helper::options()->themeUrl.'/theme_backup.zip">theme_backup.zip</a></p>';
        } else {
            echo '<p>无法创建备份文件。</p>';
        }
    }

    //第二步下载新版本
    public function second()
    {
        $latest = $this->request->latest;
        $temp = $this->_dir . "/temp/" . basename(self::$latest);
        $proxy = $this->settings->proxy;
        if ($proxy) {
            $url = $proxy . self::$latest;
        } else {
            $url = self::$latest;
        }

        $source = fopen($url, "rb");
        if ($source) $target = fopen($temp, "wb");
        if ($target) {
            while (!feof($source)) {
                $res = fwrite($target, fread($source, 1024 * 8), 1024 * 8);
                if (!$res) return $this->log("下载新版本写入本地错误");
            }
        }
        if ($source) fclose($source);
        if ($target) fclose($target);
        return $temp;
    }

    //第三步解压新版本
    public function third()
    {
        include "class-pclzip.php";
        $file = $this->_dir . "/temp/typecho.zip";
        $dir = dirname($file);
        $zip = new PclZip($file);
        if (!$zip->extract(PCLZIP_OPT_PATH, $dir) === 0) {
            return $this->log($zip->errorInfo(true));
        }
        return $dir;
    }

    //第四步更新
    public function fourth()
    {
        $lastestDir = $this->_dir . "/temp/typecho";
        $overWrite = array(
            "admin" => __TYPECHO_ROOT_DIR__ . __TYPECHO_ADMIN_DIR__,
            "var" => __TYPECHO_ROOT_DIR__ . "/var/",
            "index.php" => __TYPECHO_ROOT_DIR__ . "/index.php"
        );
        foreach ($overWrite as $name => $to) {
            $from = "$lastestDir/$name";
            if (is_dir($from)) $this->moveFileOrFolder($from, $to);
        }
    }

    //第五步清空临时文件
    public function fifth()
    {
        $this->clean($this->_dir . "/temp", true);
        if (!mkdir($this->_dir . "/temp", 0755, true)) {
            return false; // 创建目标文件夹失败
        }
    }

    //第六步终章提示升级完毕进入后台
    public function sixth()
    {
    }

    private function clean($d, $r = false)
    {
        foreach (glob("$d/*") as $f) {
            if (is_dir($f)) {
                $this->clean($f, $r);
            } else {
                if (!unlink($f)) {
                    return $this->log("删除文件 $f 错误");
                }
            }
        }

        // 此处的代码将删除空文件夹
        if ($r) {
            if (!@rmdir($d)) {
                return $this->log("删除文件夹 $d 错误");
            }
        }
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

    private function log($text)
    {
        $text = date("Y-m-d h:i:s") . " $text\r\n";
        error_log($text, 3, $this->_dir . "/error.log");
        echo $text;
    }

    public function action()
    {
        $this->on($this->request);
    }
}

?>