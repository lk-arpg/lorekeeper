<a id="backToTop" data-bs-toggle="tooltip" data-bs-title="Scroll to Top" title="Scroll to Top"><i class="fas fa-angle-double-up"></i></a>

<script>
    $(document).ready(function() {
        // Code from https://stackoverflow.com/questions/8218159/javascript-check-if-page-is-at-the-top
        var goToTop = document.querySelector('#backToTop');
        goToTop.addEventListener("click", function(e) {
            window.scroll({
                top: 0,
                left: 0,
                behavior: 'smooth'
            });
            //scroll smoothly back to the top of the page
        });
        window.addEventListener("scroll", function() {
            if (window.scrollY == 0) {
                //user is at the top of the page; no need to show the back to top button
                goToTop.style.display = "";
            } else {
                goToTop.style.display = "block";
            }
        });
    });
</script>

<style>
    #backToTop {
        color: #eee;
        cursor: pointer;
        padding: 0.25em 0.75em;
        position: fixed;
        bottom: 1em;
        right: 0.75em;
        display: none;
        transition: all 0.5s ease;
        opacity: 0.75;
        background-color: rgba(0, 0, 0, .5);
        font-size: 1.5em;
        border-radius: 50%;
    }

    #backToTop:hover {
        color: #fff;
        opacity: 1;
        transition: all 0.5s ease;
    }
</style>
