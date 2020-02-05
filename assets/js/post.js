let deleteEntity = (url) => {
    // todo: srsly at least rewrite this 1999 shit in fetch/axios
    console.log(url);
    let xhr = new XMLHttpRequest();
    xhr.open("DELETE", url, true);
    xhr.onload = function (e) {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                console.log(xhr.responseText);
                window.location = '/posts';
            } else {
                console.error(xhr.statusText);
            }
        }
    };
    xhr.onerror = function (e) {
        console.error(xhr.statusText);
    };
    xhr.send(null);
};

let handleForm = function (event) {
    // todo: add parent id to the url here, submit programmatically
    event.preventDefault();
    console.log("hahaha");
};

let postPage = () => {
    // init delete
    // todo: fix for posts that you do not own
    let removeBtn = document.getElementById('btn-remove');
    let deletePostUrl = removeBtn.attributes.getNamedItem("data-delete-url").nodeValue;

    removeBtn.onclick = () => {
        deleteEntity(deletePostUrl)
    };

    // init comment form
    let commentFormBox = document.getElementById("comment-form");
    let replyLinks = Array.from(document.getElementsByClassName("reply-form"));
    replyLinks.forEach(link => {
        link.addEventListener("click", event => {
            event.preventDefault();
            let commentId = link.getAttribute("data-comment-id");
            let placeholder = document.getElementById("form-container-" + commentId);
            placeholder.appendChild(commentFormBox);
        });
    });

    let commentForm = commentFormBox.querySelector('form');
    commentForm.addEventListener('submit', handleForm);
};

export default postPage;
