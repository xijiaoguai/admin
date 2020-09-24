function ajax_com(post_data, callback) {
    if (typeof (url) == "undefined") {
        alert("请先添加conf.js文件，并var url = ***");
        return;
    }
    var token = getCookie('token');
    if (token != null) {
        post_data.token = token;
    }
    $.ajax({
        url: url,
        type: 'post',
        data: post_data,
        dataType: 'json',
        success: function (data) {
            if (data.code == 1001) {
                fail("请重新登录");
                var setTime = setTimeout(function () {
                    window.location.href = "/sign.html";
                }, 1200);
                return;
            }
            callback(data);
        },
        error: function () {
            $(".modal").modal('hide');
            error();
        }
    })
}

function paginate(data, tab_id, func) {
    var total_page = data.cnt_page;
    var curr_page = data.curr_page;
    var pre_page = curr_page - 1 < 1 ? 1 : curr_page - 1;
    var next_page = curr_page + 1 > total_page ? total_page : curr_page + 1;
    var pages = "<div class=\"d-flex mb-2\" id=\"page_tab_" + tab_id + "\">\n" +
        "<p class=\"p-2 mr-auto\">总共有" + total_page + "页</p>\n" +
        "<ul class=\"pagination pagination-sm p-2\">" +
        "<li class=\"page-item\"><span class=\"page-link\" onclick=\"" + func + "(" + pre_page + ")\">上一页</span></li>";
    var first_page = total_page < 5 ? 1 : (total_page - 4 > curr_page ? curr_page : total_page - 4);
    for (var i = first_page; i <= first_page + 4; i++) {
        if (i > total_page) {
            break;
        }
        var cla = "page-link ";
        if (i == curr_page) {
            cla += "current_page";
        }
        pages += "<li class=\"page-item\"><span class=\"" + cla + "\" onclick=\"show_list(" + i + ")\">" + i + "</span></li>";
    }
    pages += "<li class=\"page-item\"><span class=\"page-link\" onclick=\"" + func + "(" + next_page + ")\">下一页</span></li>" +
        "<li class=\"page-item\"><input type=\"text\" class='page_ipt' id=\"page_ipt_" + tab_id + "\"></li>" +
        "<li class=\"page-item\"><span class=\"page-link\" onclick=\"" + func + "(get_ipt_val(" + tab_id + "))\">确定</span></li></ul>\n" +
        "</div>";
    $("#page_tab_" + tab_id).remove();
    $("#tab_" + tab_id).after(pages);
}

function get_ipt_val(tab_id) {
    return $("#page_ipt_" + tab_id).val();
}

function isJSON(str) {
    console.log(typeof str);
    if (typeof str == 'string') {
        try {
            JSON.parse(str);
            return true;
        } catch (e) {
            console.log(e);
            return false;
        }
    }
}

function success() {
    $("#success").addClass("index");
    $("#success").addClass("show");
    setTimeout(function () {
        $("#success").removeClass("index");
        $("#success").removeClass("show");
    }, 1000)
}

function fail(message) {
    $("#fail").html(message);
    $("#fail").addClass("index");
    $("#fail").addClass("show");
    setTimeout(function () {
        $("#fail").removeClass("index");
        $("#fail").removeClass("show");
    }, 1000)
}

function error() {
    $("#error").addClass("index");
    $("#error").addClass("show");
    setTimeout(function () {
        $("#error").removeClass("index");
        $("#error").removeClass("show");
    }, 1000)
}

function setCookie(key, value, t) {
    var oDate = new Date();
    oDate.setTime(oDate.getTime() + t * 1000);
    document.cookie = key + "=" + value + "; expires=" + oDate.toUTCString();
}

/**
 * [getCookie 获取cookie]
 */
function getCookie(key) {
    var arr1 = document.cookie.split("; ");//由于cookie是通过一个分号+空格的形式串联起来的，所以这里需要先按分号空格截断,变成[name=Jack,pwd=123456,age=22]数组类型；
    for (var i = 0; i < arr1.length; i++) {
        var arr2 = arr1[i].split("=");//通过=截断，把name=Jack截断成[name,Jack]数组；
        if (arr2[0] == key) {
            return decodeURI(arr2[1]);
        }
    }
}

function delCookie(name) {
    var exp = new Date();
    exp.setTime(exp.getTime() - 1);
    var cval = getCookie(name);
    if (cval != null)
        document.cookie = name + "=" + cval + ";expires=" + exp.toGMTString();
}


function getQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if (r != null) return unescape(r[2]);
    return null;
}


function get_proj_id() {
    return $(window.parent.document).find("#proj_id").val();
}