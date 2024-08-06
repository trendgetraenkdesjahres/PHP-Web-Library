var wrapper = document.getElementById('wrapper');
wrapper.addEventListener('event', function(ev) {
    ev.preventDefault();
    var xhr = new XMLHttpRequest();


    var button = document.getElementById('button');
    button.setAttribute('disabled', 'disabled');
    button.innerText = "trying to power-cycle";


    xhr.open('METHOD', window.location.origin + '/ajax');
    xhr.onload = function() {
        if (xhr.status === 200) {
            button.innerText = "power-cycling succeeded";
            button.classList.add("success");
        } else {
            button.innerText = "error. check console log";
            button.classList.add("fail");
            console.log(JSON.parse(xhr.responseText));
        }
    };
    xhr.send();
});
