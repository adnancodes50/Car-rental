<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm fixed-top">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
            <img src="{{ asset('vendor/adminlte/dist/img/logo.png') }}" alt="Zelta Cars Logo" width="100" height="40"
                class="me-2">
            <span class="fw-bold text-dark">Zelta Cars</span>
        </a>



        <!-- Toggler -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive"
            aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Links -->
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link text-warning fw-semibold px-3" href="#">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-warning fw-semibold px-3" href="#">Vehicles</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-warning fw-semibold px-3" href="#">Contact</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
