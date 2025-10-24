<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm fixed-top">
    <div class="container">
        @php
            $brandImg = config('adminlte.logo_img'); // e.g. 'storage/logo/img1234.png'
            $brandAlt = config('adminlte.title', 'Rental'); // your project_name
        @endphp

        <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
            <img src="{{ asset($brandImg) }}" alt="{{ $brandAlt }}" class="me-2 img-fluid brand-image-custom"
                style="height:40px; width:auto; object-fit:contain;">
            <span class="fw-bold text-dark">{{ $brandAlt }}</span>
        </a>


        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive"
            aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link fw-semibold px-3" href="{{ url('/#home-section') }}">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold px-3" href="{{ url('/#vehicles-section') }}">Vehicles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold px-3" href="{{ url('/#contact-section') }}">Contact</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
    /* Inactive links */
    .navbar-dark .navbar-nav .nav-link {
        color: #CF9B4D;
        /* yellow */
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
                const linkHash = new URL(link.href).hash; // FIX: get only the #hash
                if (linkHash === '#' + current) {
                    link.classList.add('active');
                }
            });
        }

        window.addEventListener('scroll', setActiveLink);
        window.addEventListener('resize', setActiveLink);
        setActiveLink(); // initial call
    });

    document.addEventListener("DOMContentLoaded", function() {
        if (window.location.hash) {
            const section = document.querySelector(window.location.hash);
            if (section) {
                section.scrollIntoView({
                    behavior: "smooth"
                });
            }
        }
    });
</script>
