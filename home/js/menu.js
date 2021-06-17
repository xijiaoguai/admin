$(function () {
    show_list();
})

function show_list() {
    var proj_id = get_proj_id();
    console.log(proj_id);
    ajax_com({"c": "user/menu-list", "proj_id": proj_id}, function (res) {
        if (res.code == 200) {
            var str = '';
            var option_str = "<option value='0'>无上级</option>";
            $.each(res.data.menus, function (k, v) {
                str += "<tr><td>" + v.name + "</td><td></td><td>"+v.sort+"</td><td><button class=\"btn btn-primary btn-sm\" onclick=\"editModel(" + v.id + ")\">编辑</button>  <button class=\"btn btn-danger btn-sm\" onclick=\"delModel(" + v.id + ")\">删除</button></td></tr>";
                option_str += "<option value='" + v.id + "'>" + v.name + "</option>";
                $.each(v.child, function (k1, v1) {
                    str += "<tr><td></td><td>" + v1.name + "</td><td>"+v1.sort+"</td><td><button class=\"btn btn-primary btn-sm\" onclick=\"editModel(" + v1.id + ")\">编辑</button>   <button class=\"btn btn-danger btn-sm\" onclick=\"delModel(" + v1.id + ")\">删除</button></td></tr>";
                })
            })
            $("#pid").html(option_str);
            $("#tbody_1").html(str);
        } else {
            fail(res.message);
        }
    })
}

function editModel(menu_id) {
    $("#id").val(menu_id);
    ajax_com({"c": "user/menu-info", "menu_id": menu_id}, function (res) {
        if (res.code == 200) {
            $("#name").val(res.data.name);
            $("#url").val(res.data.url);
            var pid = res.data.pid ?? 0;
            $("#pid").val(pid);
        } else {
            fail(res.message);
        }
    })
    $('#editModal').modal('show');
}

function menu_edit() {
    var proj_id = get_proj_id();
    var name = $("#name").val();
    var url = $("#url").val();
    var pid = $("#pid").val();
    var id = $("#id").val();
    var weight = $("#weight").val();
    ajax_com({
        "c": "user/menu-edit",
        "proj_id": proj_id,
        "name": name,
        "url": url,
        "pid": pid,
        "id": id,
        "weight": weight
    }, function (res) {
        if (res.code == 200) {
            $('#editModal').modal('hide');
            success();
        } else {
            $('#editModal').modal('hide');
            fail(res.message);
        }
    })
    show_list();
}

function delModel(id) {
    $("#del_id").val(id);
    $('#delModal').modal('show');
}

function menu_del() {
    var del_id = $("#del_id").val();
    ajax_com({"c": "user/menu-del", "id": del_id}, function (res) {
        if (res.code == 200) {
            $('#delModal').modal('hide');
            success();
        } else {
            $('#delModal').modal('hide');
            fail(res.message);
        }
    })
    var page = $("#page").val();
    show_list(page);
}