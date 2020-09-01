$(function () {
    menu();
})

function open_url(url, id = 0) {
    // if (id) {
    //     $("#menu_id").val(id);
    //     $("#token").val(getCookie('token'));
    // }
    $("#ifa").attr("src", url);
}

function destroy() {
    delCookie('token');
    window.location.href = 'sign.html';
}

function menu() {
    var proj_id = getQueryString('proj_id');
    if (!proj_id) {
        window.location.href = "sign.html";
    }
    $("#proj_id").val(proj_id);
    ajax_com({"c": "user/menu-list", "proj_id": proj_id}, function (res) {
        if (res.code == 200) {
            var str = '<li class="item"><a class="btn" onclick="open_url(\'home.html\')">首页</a></li>'
            $.each(res.data, function (k, v) {
                var child_str = '';
                // var url = "#id_" + v.id;
                var url = 'href="#id_' + v.id + '"';
                if (v.child.length !== 0) {
                    child_str = '<div class="smenu">';
                    $.each(v.child, function (k1, v1) {
                        child_str += '<a onclick="open_url(\'' + v1.url + '\',' + v1.id + ')">' + v1.name + '</a>';
                    })
                    child_str += '</div>';
                } else {
                    if (v.url) {
                        url = 'onclick="open_url(\'' + v.url + '\',' + v.id + ')"';
                    }
                }
                str += '<li class="item" id="id_' + v.id + '"><a ' + url + ' class="btn">' + v.name + '</a>' + child_str + '</li>';
            })
            str += '<li class="item"><a class="btn" onclick="destroy()">退出</a></li>'
            $("#menu").html(str);
            const handler = function (e) {
                if (this.getAttribute('href')) {
                    if (location.hash === this.getAttribute('href')) {
                        e.preventDefault();
                        location.href = location.href.slice(0, -location.hash.length + 1)
                    }
                }
            }
            document.querySelectorAll('.btn').forEach(a => {
                a.addEventListener('click', handler, false);
            });
        } else {
            fail(res.message);
        }
    })
}
