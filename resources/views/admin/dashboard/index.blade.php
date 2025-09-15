@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="container">
        <h1>Dashboard</h1>
        <p>Welcome back, {{ auth()->user()->name }}!</p>
    </div>
@endsection
