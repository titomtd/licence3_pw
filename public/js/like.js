var nblike = document.getElementsByTagName('h2')

function onClickBtnLike(event){
    event.preventDefault();

    const url = this.href;
    const spanCount = this.querySelector('span.js-likes');

    const icon = this.querySelector('i');

    axios.get(url).then(function(reponse){
        spanCount.textContent = reponse.data.likes;
        nblike.textContent = reponse.data.likes;

        if(icon.classList.contains('fas')) icon.classList.replace('fas', 'far');
        else icon.classList.replace('far', 'fas');
    }).catch(function(error){
        if(error.response.status === 403){
            $('#myModal').modal('show')
        } else {
            window.alert("Une erreur s'est produite, veuillez réessayer plus tard");
        }
    });
}

document.querySelectorAll('a.js-like').forEach(function(link){
    link.addEventListener('click', onClickBtnLike)
});