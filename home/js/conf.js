var local = "http://127.0.0.1:10087/api.php";
var url = "https://admin.kxbwmedia.com:20005/api.php";

$.getJSON("js/.env", function (data) {
    console.log(data['env']);
    if (data['env'] != 'prod') {
        url = local;
    }
})
