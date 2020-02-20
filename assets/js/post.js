import axios from 'axios';
import qs from 'qs';

let deleteEntity = (url) => {

    axios.delete(url)
        .then(response => window.location = '/posts')
        .catch(err =>  console.log(err));
};

let makeVoteCallback = function (voteUrl, voteValue) {
    return event => {
        event.preventDefault();
        axios.post(voteUrl, {'value': parseInt(voteValue)})
            .then(_ => console.log('sraka'))
            .catch(err => console.log(err));
    };
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
    let postDataBox = document.getElementById('post-data');
    let postId = postDataBox.getAttribute('data-post-id');
    let voteUrl = postDataBox.getAttribute('data-vote-url');

    // init delete
    let removeBtn = document.getElementById('btn-remove');
    if (null !== removeBtn) {
        let deletePostUrl = removeBtn.attributes.getNamedItem("data-delete-url").nodeValue;

        removeBtn.onclick = () => {
            deleteEntity(deletePostUrl)
        };
    }

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

    //init votes
    let voteBoxes = Array.from(document.getElementsByClassName('vote'));
    voteBoxes.forEach(box => {
        let link = box.querySelector('a');
        let voteValue = link.getAttribute("data-vote-value");
        link.addEventListener("click", makeVoteCallback(voteUrl, voteValue));
    })
};

export default postPage;
