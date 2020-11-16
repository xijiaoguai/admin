var arr = new Array();
var click = 0;
$(function () {
    menu();
})

function open_url(url, id = 0, name = '') {
    // if (id) {
    //     $("#menu_id").val(id);
    //     $("#token").val(getCookie('token'));
    // }
    var proj_id = get_proj_id();
    if (id != 0) {
        if (arr.indexOf(id) > -1) {
            open_ifa(id);
            return;
        }
        var token = getCookie('token');
        var ifa = '<iframe src="' + url + '&token=' + token + '*' + id+'*' + proj_id + '"\n' +
            '                class="ifa"\n' +
            '                frameborder="0"\n' +
            '                id="ifa_' + id + '"\n' +
            '        ></iframe>';
        $(".ifa").css('display', 'none');
        $("#ifa").before(ifa);

        var tab = '<div class="right-top-tab" id="tab_' + id + '">\n' +
            '                <div class="open" onclick="open_ifa(' + id + ')">' + name + '</div>\n' +
            '                <div class="close" onclick="close_ifa(' + id + ')">x</div>\n' +
            '            </div>';
        $(".right-top").append(tab);
        add_one(id);
    }
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
            $("#proj_name").html(res.data.proj_name);
            var str = '<li class="item"><a class="btn" onclick="open_url(\'home.html\')">首页</a></li>'
            $.each(res.data.menus, function (k, v) {
                var child_str = '';
                // var url = "#id_" + v.id;
                var url = 'href="#id_' + v.id + '"';
                if (v.child.length !== 0) {
                    child_str = '<div class="smenu">';
                    $.each(v.child, function (k1, v1) {
                        child_str += '<a onclick="open_url(\'' + v1.url + '\',' + v1.id + ',\'' + v1.name + '\')">' + v1.name + '</a>';
                    })
                    child_str += '</div>';
                } else {
                    if (v.url) {
                        url = 'onclick="open_url(\'' + v.url + '\',' + v.id + ',\'' + v.name + '\')"';
                    }
                }
                str += '<li class="item" id="id_' + v.id + '"><a ' + url + ' class="btn">' + v.name + '</a>' + child_str + '</li>';
            })
            str += '<li class="item"><a class="btn" onclick="destroy()">退出</a></li>'
            $("#menu").html(str);
            var hash = '';
            var click = 0;
            const handler = function (e) {
                hash = click ? location.hash : '';
                click++;
                console.log(hash);
                if (this.getAttribute('href')) {
                    if (hash === this.getAttribute('href')) {
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

function open_ifa(id) {
    add_one(id);
    $(".ifa").css('display', 'none');
    $("#ifa_" + id).css('display', 'block');
}

function close_ifa(id) {
    $("#tab_" + id).remove();
    $("#ifa_" + id).remove();
    var prev_id = get_prev(id);
    $("#ifa_" + prev_id).css('display', 'block');
    $(".right-top-tab").css('border', '0');
    $("#tab_" + prev_id).css('border', '1px solid black');
}

function add_one(id) {
    var index = arr.indexOf(id);
    if (index > -1) {
        arr.splice(index, 1);
    }
    arr.push(id);
    $(".right-top-tab").css('border', '0');
    $("#tab_" + id).css('border', '1px solid black');
}

function get_prev(id) {
    var index = arr.indexOf(id);
    if (index > -1) {
        arr.splice(index, 1);
    }
    return arr[arr.length - 1];
}
