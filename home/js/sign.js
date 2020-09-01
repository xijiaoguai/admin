function login() {
    var acc = $("#login_acc").val();
    var pwd = $("#login_pwd").val();
    ajax_com({"c": "user/user-login", "acc": acc, "pwd": pwd}, function (res) {
        if (res.code == 200) {
            //如果没有团队，选择创建或加入
            //如果有团队，创建者则进入团队管理界面，其他则直接进入项目管理界面
            setCookie('token', res.data.token, 3600 * 24 * 7);
            if (res.data.have_team == 1) {
                if (res.data.is_creator == 1) {
                    window.location.href = "/team_manager.html";
                } else {
                    window.location.href = "/proj.html";
                }
            } else {
                window.location.href = "/team_choice.html";
            }
        } else {
            fail(res.message);
        }
    })
}

function sign_up() {
    var acc = $("#sign_acc").val();
    var pwd = $("#sign_pwd").val();
    var check_pwd = $("#sign_check_pwd").val();
    if (pwd !== check_pwd) {
        fail("密码和确认密码不一致");
    }
    ajax_com({"c": "user/user-register", "acc": acc, "pwd": pwd}, function (res) {
        if (res.code == 200) {
            setCookie('token', res.data.token, 3600 * 24 * 7);
            window.location.href = "/team_choice.html";
        } else {
            fail(res.message);
        }
    })
}