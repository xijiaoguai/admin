$(function () {
    show_list(1);
})

function editModel(id) {
    $("#id").val(id);
    console.log(id,$("#id").val());
    ajax_com({"c": "user/role-info", "id": id}, function (res) {
        if (res.code == 200) {
            $("#name").val(res.data.name);
        } else {
            fail(res.message);
        }
    })
    var proj_id = get_proj_id();
    ajax_com({"c": "user/role-menus", "role_id": id, "proj_id": proj_id}, function (res) {
        if (res.code == 200) {
            var str = '';
            $.each(res.data, function (k, v) {
                var c_str = '<div class="form-check c_div">';
                $.each(v.child, function (k1, v1) {
                    var v1_checked = v1.check ? 'checked' : '';
                    c_str += '<label class="form-check-label c_lab"><input type="checkbox" class="form-check-input pid_' + v1.pid + '" pid="' + v1.pid + '" value="' + v1.id + '" ' + v1_checked + '>' + v1.name + '</label>';
                })
                c_str += '</div>';
                var v_checked = v.check ? 'checked' : '';
                str += '<div class="form-check p_div"><label class="form-check-label"><input type="checkbox" class="form-check-input id_' + v.id + '" pid="' + v.pid + '" value="' + v.id + '" ' + v_checked + '>' + v.name + '</label>' + c_str + '</div>';
            })
            $("#menus").html(str);

            $(":checkbox").on("change", function () {
                var id = $(this).val();
                var pid = $(this).attr("pid")
                var is_check = $(this).is(":checked");
                if (pid == 0 && is_check) {
                    $(".pid_" + id).prop("checked", true);
                }
                if (pid == 0 && !is_check) {
                    $(".pid_" + id).prop("checked", false);
                }
                if (pid !== 0 && is_check) {
                    console.log(".id_" + pid);
                    $(".id_" + pid).prop("checked", true);
                }
            });
        } else {
            fail(res.message);
        }
    })
    $('#editModal').modal('show');
}

function delModel(id) {
    $("#del_id").val(id);
    $('#delModal').modal('show');
}

function role_edit() {
    var id = $("#id").val();
    var proj_id = get_proj_id();
    var name = $("#name").val();
    var auth_str = "";
    $("input:checkbox:checked").each(function (i) {
        if (0 == i) {
            auth_str = $(this).val();
        } else {
            auth_str += ("," + $(this).val());
        }
    });
    console.log(proj_id,name,id,auth_str);
    ajax_com({
        "c": "user/role-edit",
        "proj_id": proj_id,
        "name": name,
        "role_id": id,
        'auth_str': auth_str
    }, function (res) {
        if (res.code == 200) {
            $('#editModal').modal('hide');
            success();
        } else {
            fail(res.message);
        }
    })
    var page = $("#page").val();
    show_list(page);
}

function role_del() {
    var del_id = $("#del_id").val();
    ajax_com({"c": "user/role-del", "role_id": del_id}, function (res) {
        if (res.code == 200) {
            $('#delModal').modal('hide');
            success();
        } else {
            fail(res.message);
        }
    })
    var page = $("#page").val();
    show_list(page);
}

function show_list(page) {
    var page_size = 10;
    $("#page").val(page);
    var proj_id = get_proj_id();
    ajax_com({"c": "user/role-list", "proj_id": proj_id, "page": page, "page_size": page_size}, function (res) {
        //分页
        paginate(res.data, 1, 'show_list');
        //列表
        var str = '';
        $.each(res.data.list, function (k, v) {
            str += "<tr><td>" + v.id + "</td><td>" + v.name + "</td><td>" + v.create + "</td><td><button class=\"btn btn-primary btn-sm\" onclick=\"editModel(" + v.id + ")\">编辑</button>   <button class=\"btn btn-danger btn-sm\" onclick=\"delModel(" + v.id + ")\">删除</button></td></tr>";
        })
        $("#tbody_1").html(str);
    })
}