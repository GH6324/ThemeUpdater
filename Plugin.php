<?php

namespace TypechoPlugin\ThemeUpdater;

use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Utils\Helper;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * icefox主题在线更新插件
 *
 * @package icefox主题在线更新插件
 * @author 小胖脸
 * @version 0.3
 * @link https://xiaopanglian.com/
 */
class Plugin implements PluginInterface
{
    /**
     * 激活插件方法，如果激活失败，抛出异常将禁用此插件。
     */
    public static function activate()
    {
        // \Typecho\Plugin::factory('admin/footer.php')->end = __CLASS__ . '::render';
        // Helper::addRoute('themeUpdater', '/themeUpdater/[step]', Updater::class, 'panel', 'index');
        // \Typecho\Plugin::factory('admin/options-plugin.php')->config = __CLASS__ . '::render';
        \Typecho\Plugin::factory('Widget_Upload')->uploadHandle = __CLASS__ . '::handleUpload';
    }

    /**
     * 停用插件方法，如果停用失败，抛出异常将启用此插件。
     */
    public static function deactivate()
    {
        // Helper::removeRoute('themeUpdater');
    }

    /**
     * 插件配置面板呈现方法。
     *
     * @param Form $form
     */
    public static function config(Form $form)
    {
        try {
            ?>
        <form action="?panel=MyFileUploader" method="post" enctype="multipart/form-data">
            <input type="file" name="file" />
            <input type="submit" value="上传" />
        </form>

        <?php
        $updateUrl = new Form\Element\Text(
                'updateUrl',
                null,
                'https://icefox.xiaopanglian.com/version.json',
                _t('更新主题包下载地址'),
                _t('填入主题更新包的下载地址')
            );

            $form->addInput($updateUrl);
        } catch (\Exception $exception) {
        }
    }

    /**
     * 插件个人配置面板渲染方法。
     *
     * @param Form $form
     */
    public static function personalConfig(Form $form)
    {
    }

    /**
     * 主题配置页面添加主题更新按钮
     * @return void
     */
    public static function render()
    {
        // $themeVersion = "未知";
        // try {
        //     if (defined('__THEME_VERSION__')) {
        //         $themeVersion = \__THEME_VERSION__;
        //     }
        // } catch (Exception $exc) {
        // }
        // // 获取插件配置
        // $options = \Typecho\Widget::widget('Widget_Options');
        // $pluginOptions = $options->plugin('ThemeUpdater');
        // $updateUrl = $pluginOptions->updateUrl;
        // $currentPageUrl = $_SERVER['REQUEST_URI'];
        // if (defined('__TYPECHO_PLUGIN_DIR__')) {
        //     $pluginDir = \__TYPECHO_PLUGIN_DIR__;
        // }

        // if (strpos(strtolower($currentPageUrl), strtolower('admin/options-theme.php')) !== false) {
        //     echo '<input id="updateUrl" value="' . $updateUrl . '" style="display:none;"/>';
        //     echo '<input id="currentVersion" value="' . $themeVersion . '" style="display:none;"/>';
        //     $html = '<script src="' . $pluginDir . '\ThemeUpdater\themeUpdater.js"></script>';

        //     echo $html;
        // }


    }

    public static function handleUpload($file)
    {
        return $file;
    }
}