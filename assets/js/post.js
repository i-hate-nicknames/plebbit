import axios from 'axios';
import qs from 'qs';

let deleteEntity = (url) => {

    axios.delete(url)
        .then((response) => { window.location = '/posts' })
        .catch((err) => { console.log(err) });
};

let submitComment = function (event) {
    event.preventDefault();
    let commentFormBox = document.getElementById("comment-form");
    let titleInput = document.getElementById('comment_title');
    let textInput = document.getElementById('comment_text');
    let tokenInput = document.getElementById('comment__token');
    let parentId = commentFormBox.getAttribute("data-parent-comment");
    let commentData = {
        comment: {
            title: titleInput.value,
            text: textInput.value,
            _token: tokenInput.value
        }
    };
    // todo: fix window.location and pass url from twig template
    axios.post(window.location + '/' + parentId, qs.stringify(commentData))
        .then((_) => window.location = window.location)
        .catch((err) => { console.log(err)})
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
    let commentForm = commentFormBox.querySelector('form');
    replyLinks.forEach(link => {
        link.addEventListener("click", event => {
            event.preventDefault();
            let commentId = link.getAttribute("data-comment-id");
            let placeholder = document.getElementById("form-container-" + commentId);
            commentFormBox.setAttribute("data-parent-comment", commentId);
            placeholder.appendChild(commentFormBox);
        });
    });
    commentForm.addEventListener('submit', submitComment);
};

export default postPage;