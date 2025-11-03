<nav class="navbar navbar-expand-lg navbar-white bg-white shadow-sm fixed-top">
    <div class="container">
        @php
            $brandImg = config('adminlte.logo_img');
            $brandAlt = config('adminlte.title', 'Rental');
        @endphp

        <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
            <img src="{{ asset($brandImg) }}" alt="{{ $brandAlt }}"
                class="img-fluid brand-image-custom"
                style="height:40px; width:auto; object-fit:contain;">
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
                    <a class="nav-link fw-semibold px-3" href="{{ url('/#category-section') }}">Category</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold px-3" href="{{ url('/#contact-section') }}">Contact</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<style>
    /* Base navbar link styles */
    .navbar .navbar-nav .nav-link {
        color: #679767; /* Light green for inactive links */
        font-weight: 500;
        transition: color 0.3s ease;
    }

    /* Hover effect */
    .navbar .navbar-nav .nav-link:hover {
        color: #000; /* Black on hover */
    }

    /* Active link color (black) */
    .navbar .navbar-nav .nav-link.active {
        color: #000 !important; /* Black for active link */
        font-weight: 600;
    }

    /* Remove underline/border animations */
    .navbar .navbar-nav .nav-link::after {
        content: none !important;
    }
</style>

<!-- JS to highlight navbar links on scroll -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

        function setActiveLink() {
            let current = sections[0].id;
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
                const linkHash = new URL(link.href).hash;
                if (linkHash === '#' + current) {
                    link.classList.add('active');
                }
            });
        }

        window.addEventListener('scroll', setActiveLink);
        window.addEventListener('resize', setActiveLink);
        setActiveLink();
    });

    // Smooth scroll to section if URL has a hash
    document.addEventListener("DOMContentLoaded", function() {
        if (window.location.hash) {
            const section = document.querySelector(window.location.hash);
            if (section) {
                section.scrollIntoView({ behavior: "smooth" });
            }
        }
    });
</script>
