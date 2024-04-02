<?php

namespace TypechoPlugin\ThemeUpdater;

use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Utils\Helper;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * icefox主题自动更新插件，不用再手动从FTP上传文件更新了
 *
 * @package 主题更新插件
 * @author xiaopanglian
 * @version 0.1
 * @link https://xiaopanglian.com/
 */
class Plugin implements PluginInterface
{
    /**
     * 激活插件方法，如果激活失败，抛出异常将禁用此插件。
     */
    public static function activate()
    {
        \Typecho\Plugin::factory('admin/footer.php')->end = __CLASS__ . '::render';
        Helper::addRoute('themeUpdater', '/themeUpdater/[step]', Updater::class, 'panel', 'index');
    }

    /**
     * 停用插件方法，如果停用失败，抛出异常将启用此插件。
     */
    public static function deactivate()
    {
        Helper::removeRoute('themeUpdater');
    }

    /**
     * 插件配置面板呈现方法。
     *
     * @param Form $form
     */
    public static function config(Form $form)
    {
        $updateUrl = new Form\Element\Text(
            'updateUrl',
            null,
            'https://icefox.xiaopanglian.com/version.json',
            _t('更新主题包下载地址'),
            _t('填入主题更新包的下载地址')
        );

        $form->addInput($updateUrl);
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
        $themeVersion = "未知";
        try {
            if (defined('__THEME_VERSION__')) {
                $themeVersion = \__THEME_VERSION__;
            }
        } catch (Exception $exc) {
        }
        // 获取插件配置
        $options = \Typecho\Widget::widget('Widget_Options');
        $pluginOptions = $options->plugin('ThemeUpdater');
        $updateUrl = $pluginOptions->updateUrl;
        $currentPageUrl = $_SERVER['REQUEST_URI'];
        if (defined('__TYPECHO_PLUGIN_DIR__')) {
            $pluginDir = \__TYPECHO_PLUGIN_DIR__;
        }

        if (strpos(strtolower($currentPageUrl), strtolower('admin/options-theme.php')) !== false) {
            echo '<input id="updateUrl" value="' . $updateUrl . '" style="display:none;"/>';
            echo '<input id="currentVersion" value="' . $themeVersion . '" style="display:none;"/>';
            $html = '<script src="' . $pluginDir . '\ThemeUpdater\themeUpdater.js"></script>';

            echo $html;
        }


    }
}