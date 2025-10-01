@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

<div class="container-fluid">

    @php
        $cards = [
            ['title' => 'Total Earnings', 'value' => '$'.number_format($totalEarnings,2), 'icon'=>'fa-wallet', 'color'=>'#28a745'],
            ['title' => 'Total Booking Amount', 'value' => '$'.number_format($totalBookingAmount,2), 'icon'=>'fa-book', 'color'=>'#007bff'],
            ['title' => 'Total Vehicle Sales', 'value' => '$'.number_format($totalPurchaseAmount,2), 'icon'=>'fa-car', 'color'=>'#ffc107'],
            ['title' => 'Total Customers', 'value' => $totalCustomers, 'icon'=>'fa-users', 'color'=>'#17a2b8'],
            ['title' => 'Active Bookings', 'value' => $activeBookings, 'icon'=>'fa-calendar-check', 'color'=>'#dc3545'],
            ['title' => 'Total Bookings', 'value' => $totalBookings, 'icon'=>'fa-list', 'color'=>'#343a40'],
            ['title' => 'Sale Count', 'value' => $totalPurchases, 'icon'=>'fa-shopping-cart', 'color'=>'#6c757d'],
        ];
    @endphp

    @foreach(array_chunk($cards, 3) as $rowIndex => $rowCards)
        <div class="row g-4 align-items-start {{ $rowIndex > 0 ? 'mt-2 mb-1' : '' }}"> <!-- mt-4 for margin between rows -->
            @foreach($rowCards as $card)
            <div class="col-md-4 col-sm-6 d-flex">
                <div class="glass-card h-100 text-center p-4 rounded-4 flex-fill">
                    <div class="icon-circle mb-3" style="background: {{$card['color']}}33; color: {{$card['color']}}">
                        <i class="fas {{$card['icon']}} fa-2x"></i>
                    </div>
                    <h5 class="fw-bold">{{ $card['title'] }}</h5>
                    <p class="display-6 fw-bold">{{ $card['value'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    @endforeach

</div>

<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(12px);
        box-shadow: 0 8px 32px rgba(0,0,0,0.2);
        transition: all 0.4s ease;
        border: 1px solid rgba(255,255,255,0.2);
    }
    .glass-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    }
    .icon-circle {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        width: 70px;
        height: 70px;
        border-radius: 50%;
        font-size: 1.5rem;
        margin: 0 auto;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }
    .icon-circle:hover {
        transform: scale(1.2);
    }
</style>

@endsection
