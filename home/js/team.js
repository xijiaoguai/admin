$(function () {
    ajax_com({"c": "user/team-info"}, function (res) {
        if (res.code == 200) {
            $("#mark").html(res.data.mark);
            $("#msg").html(res.data.apply_num);
        } else {
            fail(res.message);
        }
    })
    show_list(1);
})

function team_crt() {
    var name = $("#name").val();
    var desc = $("#desc").val();
    ajax_com({"c": "user/team-crt", "name": name, "desc": desc}, function (res) {
        if (res.code == 200) {
            window.location.href = "/team_manager.html";
        } else {
            fail(res.message);
        }
    })
}

function team_join() {
    var mark = $("#mark").val();
    var remarks = $("#remarks").val();
    ajax_com({"c": "user/team-join_apply", "mark": mark, "remarks": remarks}, function (res) {
        if (res.code == 200) {
            window.location.href = "/proj.html";
        } else {
            fail(res.message);
        }
    })
}

/**
 * 成员列表
 * @param page
 */
function show_list(page) {
    var page_size = 10;
    $("#page").val(page);
    ajax_com({"c": "user/team-members", "page": page, "page_size": page_size}, function (res) {
        //分页
        paginate(res.data, 1, 'show_list');
        //列表
        var str = '';
        $.each(res.data.list, function (k, v) {
            var options = "";
            if (v.role_info.length == 0) {
                options = '暂无权限';
            } else {
                $.each(v.role_info, function (k1, v1) {
                    if (k1 > 0) {
                        options += ', ';
                    }
                    options += "在“" + v1.proj_name + "”项目担任“" + v1.role_name + "”"
                })
            }
            str += "<tr><td>" + v.id + "</td><td>" + v.acc + "</td><td>" + options + "</td><td>" + v.create_time + "</td><td>" +
                "<button class=\"btn btn-primary btn-sm btn_proj_" + v.id + "\" onclick=\"del_modal(" + v.id + ")\">踢出</button></td></tr>";
        })
        $("#tbody_1").html(str);
    })
}

function del_modal(id) {
    $("#del_id").val(id);
    $('#delModal').modal('show');
}

function kick() {
    var kick_id = $("#del_id").val();
    ajax_com({"c": "user/team-kick", "uid": kick_id}, function (res) {
        if (res.code == 200) {
            success();
        } else {
            fail(res.message);
        }
    })
    var page = $("#page").val();
    show_list(page);
}

function showModel() {
    apply_list(1);
    $('#applyModel').modal('show');
}

function apply_list(page) {
    var page_size = 10;
    $("#apply_page").val(page);
    ajax_com({"c": "user/team-apply_list", "page": page, "page_size": page_size}, function (res) {
        //分页
        paginate(res.data, 2, 'apply_list');
        //列表
        var str = '';
        $.each(res.data.list, function (k, v) {
            str += "<tr><td>" + v.id + "</td><td>" + v.uid + "</td><td>" + v.acc + "</td><td>" + v.remarks + "</td><td><button class=\"btn btn-success btn-sm\" onclick=\"apply_handle(" + v.id + ",1)\">通过</button>  <button class=\"btn btn-danger btn-sm\" onclick=\"apply_handle(" + v.id + ",0)\">拒绝</button></td></tr>";
        })
        $("#tbody_2").html(str);
    })
}

function apply_handle(apply_id, agree) {
    ajax_com({"c": "user/team-apply_handle", "apply_id": apply_id, "agree": agree}, function (res) {
        //分页
        if (res.code == 200) {
            var page = $("#apply_page").val();
            apply_list(page);
            var page1 = $("#page").val();
            show_list(page1);
        } else {
            fail(res.message);
        }
    })
}