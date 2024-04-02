$(function () {
    // $("head").append(`<meta name="referrer" content="never"/>`);
    $(".typecho-option-tabs").append("<li><a href=\"javascript:void(0);\" class=\"online\">在线更新主题</a></li>");

    $("body").append(`<style>.update-panel{width:100%;height:100%;position:fixed;left:0;top:0;background-color:rgba(0,0,0,0.3);display: flex;justify-content: center;align-items: center;}
     .panel{background-color:#ffffff;width:300px;min-height:20px;padding:10px;}
     .red{color:red;}
     .mt-10{margin-top:10px;}
     </style>`);

    $(".online").click(function () {
        var currentVersion = $("#currentVersion").val();
        $("body").append(`<section class="update-panel">
        <div class=\"panel\">
            <div>当前版本：<span class="current-version" style="color:red;">${currentVersion}</span></div>
            <div class="check-panel">正在检测主题最新版本...</div>
            <div class="mt-10">
                <button class="btn-update" source="">立即更新</button>
                <button class="btn-close">关闭</button>
            </div>
            <div class="update-log">
            </div>
        </div>
        </section>`);
        $(".btn-close").click(function () {
            $(".update-panel").remove();
        });

        $.ajax({
            url: $("#updateUrl").val(),
            type: "get",
            contentType: "application/json",
            success: function (response) {
                $(".check-panel").html(`检测到主题最新版本(<b class="red">${response.version}</b>)`);
                if(response.version == currentVersion){
                    $(".btn-update").hide();
                }
                $(".btn-update").attr("source", response.latest);
            },
            error: function (error) { }
        });
        $(".btn-update").click(function () {
            let latest = $(this).attr("source");

            // 跳转更新页面
            // window.location.href = "/index.php/themeUpdater/0?latest="+latest;
            $(".update-log").append("<div>开始更新...</div>");
            $(".update-log").append("<div>正在备份主题...</div>");
            $.ajax({
                url: "/index.php/themeUpdater/first", type: "get", contentType: "application/json", success: function (response) {
                    if (response === "1") {
                        $(".update-log").append("<div>正在下载最新版...</div>");
                        $.ajax({
                            url: "/index.php/themeUpdater/second?latest=" + latest, type: "get", contentType: "application/json", success: function (response) {
                                if (response != "0") {
                                    $(".update-log").append("<div>正在解压到临时文件夹...</div>");
                                    $.ajax({
                                        url: "/index.php/themeUpdater/third", type: "get", contentType: "application/json", success: function (response) {
                                            if (response != "0") {
                                                $(".update-log").append("<div>正在删除临时文件夹...</div>");
                                                $.ajax({
                                                    url: "/index.php/themeUpdater/fourth", type: "get", contentType: "application/json", success: function (response) {
                                                        $(".update-log").append("<div>更新完成！</div>");
                                                        $(".btn-update").hide();
                                                    }
                                                });
                                            }
                                        }
                                    });
                                }
                            }
                        });
                    } else {
                        $(".update-log").append("<div style=\"color:red\">更新失败！</div>");
                    }
                }
            });

        });
    });


})