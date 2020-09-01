$(function () {
    show_list(1);
})

function editModel(id) {
    $("#id").val(id);
    var proj_id = get_proj_id();
    ajax_com({"c": "user/members-all_role", "proj_id": proj_id, "uid": id}, function (res) {
        if (res.code == 200) {
            var str = '';
            $.each(res.data, function (k, v) {
                var selected_str = "";
                if (v.selected == 1) {
                    selected_str = "selected";
                }
                str += "<option value='" + v.id + "' " + selected_str + ">" + v.name + "</option>";
            });
            $("#role_id").html(str);
        } else {
            fail(res.message);
        }
    })
    $('#editModal').modal('show');
}

function role_ctrl() {
    var uid = $("#id").val();
    var proj_id = get_proj_id();
    var role_id = $("#role_id").val();
    ajax_com({
        "c": "user/members-role_ctrl",
        "proj_id": proj_id,
        "uid": uid,
        "role_id": role_id,
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
    ajax_com({"c": "user/members-list", "proj_id": proj_id, "page": page, "page_size": page_size}, function (res) {
        //分页
        paginate(res.data, 1, 'show_list');
        //列表
        var str = '';
        $.each(res.data.list, function (k, v) {
            var btn = '';
            if (v.is_crt == 0) {
                btn = "<button class=\"btn btn-primary btn-sm\" onclick=\"editModel(" + v.id + ")\">分配角色</button>";
            }
            str += "<tr><td>" + v.id + "</td><td>" + v.acc + "</td><td>" + v.role_name + "</td><td>" + v.create_time + "</td><td>" + btn + "</td></tr>";
        });
        $("#tbody_1").html(str);
    })
}