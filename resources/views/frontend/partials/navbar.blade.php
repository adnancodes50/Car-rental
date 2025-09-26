<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
            <img src="{{ asset('vendor/adminlte/dist/img/logo.png') }}" alt="Rental Logo" class="me-2 img-fluid"
                style="height:40px; width:auto; object-fit:contain;">
            <span class="fw-bold text-dark"></span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive"
            aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0" >
                <li class="nav-item">
                    <a class="nav-link fw-semibold px-3" href="#home-section">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold px-3" href="#vehicles-section">Vehicles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold px-3" href="#contact-section">Contact</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
/* Inactive links */
.navbar-dark .navbar-nav .nav-link {
    color: #CF9B4D; /* yellow */
    position: relative;
    font-weight: 500;
    transition: color 0.3s, transform 0.3s;
}




.navbar-dark .navbar-nav .nav-link:hover::after {
    width: 100%;
}

/* Active link */
.navbar-dark .navbar-nav .nav-link.active {
    color: #fff !important;
}




</style>


<!-- JS to highlight navbar links on scroll -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

    function setActiveLink() {
        let current = sections[0].id; // default to first section
        const scrollMiddle = window.scrollY + window.innerHeight / 2;

        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionBottom = sectionTop + section.offsetHeight;
            if (scrollMiddle >= sectionTop && scrollMiddle <= sectionBottom) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
            }
        });
    }

    window.addEventListener('scroll', setActiveLink);
    window.addEventListener('resize', setActiveLink);
    setActiveLink(); // initial call
});
</script>

