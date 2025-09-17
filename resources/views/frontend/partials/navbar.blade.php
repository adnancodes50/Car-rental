<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
            <img src="{{ asset('vendor/adminlte/dist/img/logo.png') }}" alt="Zelta Cars Logo" width="100" height="40" class="me-2">
            <span class="fw-bold text-dark">Zelta Cars</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive"
            aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link text-warning fw-semibold px-3" href="#home-section">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-warning fw-semibold px-3" href="#vehicles-section">Vehicles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-warning fw-semibold px-3" href="#contact-section">Contact</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Inline style to force active link white -->
<style>
.navbar-dark .navbar-nav .nav-link.active {
    color: #fff !important;
}
</style>
