<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>@yield('title', 'Zelta Cars')</title>



  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">




  <!-- Favicon -->
  {{-- <link rel="icon" type="image/png" href="{{ asset('vendor/adminlte/dist/img/logo.png') }}"> --}}


</head>
<body data-bs-spy="scroll" data-bs-target="#navbarResponsive" data-bs-offset="80" tabindex="0">

  {{-- @include('frontend.partials.navbar') --}}

  <main>
    @yield('content')
  </main>
    <script src="{{ asset('js/script.js') }}"></script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</body>
</html>
