var SloginTelegram = {
    url: '',
    auth: function (data){
        var json = JSON.stringify(data);
        var form = document.createElement('form');
        form.setAttribute("id", "telegramAuthForm");
        form.setAttribute("method", "post");
        form.setAttribute("action", SloginTelegram.url);
        var input = document.createElement('input');
        input.setAttribute("type", "hidden");
        input.setAttribute("name", "data");
        input.setAttribute("value", json);
        form.appendChild(input);
        document.getElementsByTagName("body")[0].appendChild(form);
        document.getElementById('telegramAuthForm').submit();
    }
};