@extends('layouts.marketing')

@section('title', 'Movikaa | Encuentre su proximo auto en Costa Rica')

@section('head')
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
@vite(['resources/css/home.css', 'resources/js/home.jsx'])
@endsection

@section('content')
@php
    $catalogUrl = route('catalog.index');
    $sellUrl = auth()->check() && auth()->user()->hasRole('seller', 'dealer', 'admin') ? route('seller.dashboard') : route('seller.onboarding.create');
    $accountUrl = auth()->check()
        ? (auth()->user()->hasRole('admin')
            ? route('admin.dashboard')
            : (auth()->user()->hasRole('seller', 'dealer') ? route('seller.dashboard') : route('buyer.dashboard')))
        : route('login');

    $homeProps = [
        'homeUrl' => route('home'),
        'buyUrl' => $catalogUrl,
        'valuationUrl' => route('valuation.index'),
        'sellUrl' => $sellUrl,
        'accountUrl' => $accountUrl,
        'publicTheme' => $publicTheme ?? 'light',
        'featuredPaidVehicles' => $featuredPaidVehicles,
        'recentVehicles' => $recentVehicles,
    ];
@endphp
<div id="home-react" data-props='@json($homeProps)'></div>
@endsection


