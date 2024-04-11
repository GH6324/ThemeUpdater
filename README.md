# themeUpdater
typecho主题在线更新插件

> 本开源示例用做icefox更新，其他主题可修改配置，并将内部icefox路径修改即可。
>
> 注意：需要自己搭建更新服务器站点，站点内置1个接口，返回json格式，格式如下：
```
{
"version": "0.0.1",
"latest": "https://xxx.com/latest.zip"
}
```
version是最新版本，latest是最新版本下载地址

> 注意：插件需要777权限进行文件读写和覆盖删除等操作，如果没有777权限，则更新不会成功