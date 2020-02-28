import axios from 'axios';
import qs from 'qs';

let deleteEntity = (url) => {

    axios.delete(url)
        .then(response => window.location = '/posts')
        .catch(err =>  console.log(err));
};

let makeVoteCallback = function (voteUrl, voteValue, ratingBox) {
    return event => {
        event.preventDefault();
        let voteState = parseInt(ratingBox.getAttribute("data-current-vote"));
        let ratingDiff;
        if (voteState === voteValue) {
            ratingDiff = -voteValue;
        } else {
            ratingDiff = voteValue - voteState;
        }
        let nextVoteState = voteState + ratingDiff;
        // update html without waiting for response
        updateRating(ratingBox, ratingDiff, nextVoteState);
        axios.post(voteUrl, {'value': voteValue})
            .then(_ => console.log('voted'))
            .catch(err => {
                console.log(err.response.data.error);
                // roll back update done to html elements
                updateRating(ratingBox, -ratingDiff, voteState);
            });
    };
};

// update frontend side of the rating: the number and the
// state of vote buttons
let updateRating = function (ratingBox, ratingDiff, nextVoteState) {
    // update numeric value with diff
    let ratingNumberElement = ratingBox.querySelector('.rating-value');
    let rating = parseInt(ratingNumberElement.textContent);
    ratingNumberElement.textContent = rating + ratingDiff;
    ratingBox.setAttribute("data-current-vote", nextVoteState);
    let downvote = ratingBox.querySelector(".downvote a");
    let upvote = ratingBox.querySelector(".upvote a");
    // update active state of buttons
    upvote.removeAttribute("class");
    downvote.removeAttribute("class");
    if (nextVoteState > 0) {
        upvote.setAttribute("class", "active");
    } else {
        downvote.setAttribute("class", "active");
    }
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
    let x = 5;
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
    if (null !== commentFormBox) {
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
    }

    // init votes
    let ratingBoxes = Array.from(document.getElementsByClassName('rating'));
    ratingBoxes.forEach(box => {
        let voteUrl = box.getAttribute("data-vote-url");
        let links = Array.from(box.querySelectorAll('a'));
        links.forEach(link => {
            let voteValue = parseInt(link.getAttribute("data-vote-value"));
            link.addEventListener("click", makeVoteCallback(voteUrl, voteValue, box));
        });
    });
};

export default postPage;
