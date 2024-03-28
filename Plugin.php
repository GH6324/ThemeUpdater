<?php

namespace TypechoPlugin\ThemeUpdater;

use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Utils\Helper;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * 一款自动更新主题的插件，不用再手动从FTP上传文件更新了
 *
 * @package 主题更新插件
 * @author xiaopanglian
 * @version 0.1
 * @link https://xiaopanglian.com/themeupdater.html
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
            null,
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
        // 获取插件配置
        $options = \Typecho\Widget::widget('Widget_Options');
        $pluginOptions = $options->plugin('ThemeUpdater');
        $updateUrl = $pluginOptions->updateUrl;
        $currentPageUrl = $_SERVER['REQUEST_URI'];

        if (strpos(strtolower($currentPageUrl), strtolower('admin/options-theme.php')) !== false) {
            $html = '<script>
   $(function(){
       $("head").append(`<meta name="referrer" content="never"/>`);
        $(".typecho-option-tabs").append("<li><a href=\"javascript:void(0);\" class=\"online\">在线更新主题</a></li>");
        
        $("body").append(`<style>.update-panel{width:100%;height:100%;position:fixed;left:0;top:0;background-color:rgba(0,0,0,0.3);display: flex;justify-content: center;align-items: center;}
        .panel{background-color:#ffffff;width:300px;min-height:20px;padding:10px;}
        .red{color:red;}
        .mt-10{margin-top:10px;}
        </style>`);
        
        $(".online").click(function(){
            $("body").append(`<section class="update-panel">
        <div class=\"panel\">
            <div class="check-panel">正在检测主题最新版本...</div>
            <div class="mt-10">
                <button class="btn-update" source="">立即更新</button>
                <button class="btn-close">关闭</button>
                <span class="red">如果最新版本号与当前版本号一致，请不要更新</span>
            </div>
            <div class="update-log">
            </div>
        </div>
        </section>`);
            $(".btn-close").click(function(){
                $(".update-panel").remove();
            });
            
            $.ajax({
                url:"'.$updateUrl.'",
                type:"get",
                contentType: "application/json",
                success: function(response){
                    $(".check-panel").html(`检测到主题最新版本(<b class="red">${response.version}</b>)`);
                    $(".btn-update").attr("source",response.latest);
                },
                error:function(error){}
                });
                $(".btn-update").click(function(){
                    let latest = $(this).attr("source");

                    // 跳转更新页面
                    // window.location.href = "/index.php/themeUpdater/0?latest="+latest;
                    $(".update-log").append("<div>开始更新...</div>");                    
                    $(".update-log").append("<div>正在备份主题...</div>");
                    $.ajax({url:"/index.php/themeUpdater/first",type:"get",contentType:"application/json",success:function(response){
                        if(response==="1"){
                            $(".update-log").append("<div>正在下载最新版...</div>");
                            $.ajax({url:"/index.php/themeUpdater/second?latest="+latest,type:"get",contentType:"application/json",success:function(response){
                                if(response!="0"){
                                    $(".update-log").append("<div>正在解压到临时文件夹...</div>");
                                    $.ajax({url:"/index.php/themeUpdater/third",type:"get",contentType:"application/json",success:function(response){
                                        if(response!="0"){
                                            $(".update-log").append("<div>正在删除临时文件夹...</div>");
                                            $.ajax({url:"/index.php/themeUpdater/fourth",type:"get",contentType:"application/json",success:function(response){
                                                $(".update-log").append("<div>更新完成！</div>");
                                            }});
                                        }
                                    }});                                                
                                }
                            }});
                        }else{
                            $(".update-log").append("<div style=\"color:red\">更新失败！</div>");
                        }
                    }});
                    
                });
        });
        
        
   })
</script>';

            echo $html;
        }


    }
}