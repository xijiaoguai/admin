$(function () {
    show_list(1);
})

function editModel(proj_id) {
    $("#id").val(proj_id);
    ajax_com({"c": "user/proj-info", "id": proj_id}, function (res) {
        if (res.code == 200) {
            $("#name").val(res.data.name);
            $("#desc").val(res.data.desc);
        } else {
            fail(res.message);
        }
    })
    $('#editModal').modal('show');
}

function delModel(proj_id) {
    $("#del_id").val(proj_id);
    $('#delModal').modal('show');
}

function proj_edit() {
    var proj_id = $("#id").val();
    var name = $("#name").val();
    var desc = $("#desc").val();
    ajax_com({"c": "user/proj-edit", "proj_id": proj_id, "name": name, "desc": desc}, function (res) {
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

function proj_del(proj_id) {
    var del_id = $("#del_id").val();
    ajax_com({"c": "user/proj-del", "proj_id": del_id}, function (res) {
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
    ajax_com({"c": "user/proj-list", "page": page, "page_size": page_size}, function (res) {
        //分页
        paginate(res.data, 1, 'show_list');
        //列表
        var str = '';
        var is_crt = res.data.is_crt;
        if (is_crt == 0) {
            $("#add_proj_modal").css('display', 'none');
            $("#back").attr('href', 'sign.html');
        }
        if (is_crt == 0 && res.data.list.length == 1) {
            window.location.href = "index.html?proj_id=" + res.data.list[0].id;
            return;
        }
        $.each(res.data.list, function (k, v) {
            var btns = '';
            if (is_crt != 0) {
                btns += "<button class=\"btn btn-primary btn-sm\" onclick=\"editModel(" + v.id + ")\">编辑</button>   <button class=\"btn btn-danger btn-sm\" onclick=\"delModel(" + v.id + ")\">删除</button>  <a href=\"index.html?proj_id=" + v.id + "\" ><button class=\"btn btn-success btn-sm\">进入</button></a>";
            } else {
                btns += "  <a href=\"index.html?proj_id=" + v.id + "\" ><button class=\"btn btn-success btn-sm\">进入</button></a>";
            }
            str += "<tr><td>" + v.id + "</td><td>" + v.name + "</td><td>" + v.desc + "</td><td>" + btns + "</td></tr>";
        })
        $("#tbody_1").html(str);
    })
}