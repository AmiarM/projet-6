window.onload = () => {
    // Gestion des boutons "Supprimer"
    let links = document.querySelectorAll("[data-delete]")

    // On boucle sur links
    for (link of links) {
        // On écoute le clic
        link.addEventListener("click", function(e) {
            // On empêche la navigation
            e.preventDefault()

            // On demande confirmation
            if (confirm("Voulez-vous supprimer cette image ?")) {
                // On envoie une requête Ajax vers le href du lien avec la méthode DELETE
                fetch(this.getAttribute("href"), {
                    method: "DELETE",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ "_token": this.dataset.token })
                }).then(
                    // On récupère la réponse en JSON
                    response => response.json()
                ).then(data => {
                    if (data.success)
                        this.parentElement.remove()
                    else
                        alert(data.error)
                }).catch(e => alert(e))
            }
        })
    }
    $(document).ready(function() {
        console.log(tmpl);
        $('#add-video').on('click', function(e) {
            const index = +$('#video-counter').val();
            const tmpl = $('.videos').data('prototype').replace(/__name__/g, index);
            console.log(tmpl);
            $('#video-counter').val(index + 1);
            $('#videos').append(tmpl);
        });
    });
}