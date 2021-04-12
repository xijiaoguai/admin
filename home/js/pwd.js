function pwd_change() {
    var acc = $("#acc").val();
    var pwd = $("#pwd").val();
    var pwd_new = $("#pwd_new").val();
    ajax_com({"c": "user/user-pwd_change", "acc": acc, "pwd": pwd, "pwd_new": pwd_new}, function (res) {
        if (res.code == 200) {
            window.location.href = "/sign.html";
        } else {
            fail(res.message);
        }
    })
}