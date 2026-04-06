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
    $valuationUrl = route('valuation.index');
    $sellUrl = auth()->check() && auth()->user()->hasRole('seller', 'dealer', 'admin') ? route('seller.dashboard') : route('seller.onboarding.create');
    $accountUrl = auth()->check()
        ? (auth()->user()->hasRole('admin')
            ? route('admin.dashboard')
            : (auth()->user()->hasRole('seller', 'dealer') ? route('seller.dashboard') : route('buyer.dashboard')))
        : route('login');
    $firstName = auth()->check() ? trim(strtok((string) auth()->user()->name, ' ')) : null;
    $authUser = auth()->check() ? [
        'authenticated' => true,
        'firstName' => $firstName ?: 'Cuenta',
        'dashboardUrl' => $accountUrl,
        'buyerUrl' => route('buyer.dashboard'),
    ] : [
        'authenticated' => false,
    ];

    $homeProps = [
        'homeUrl' => route('home'),
        'buyUrl' => $catalogUrl,
        'catalogUrl' => $catalogUrl,
        'valuationUrl' => $valuationUrl,
        'sellUrl' => $sellUrl,
        'accountUrl' => $accountUrl,
        'loginUrl' => route('login'),
        'authUser' => $authUser,
        'publicTheme' => $publicTheme ?? 'light',
        'featuredPaidVehicles' => $featuredPaidVehicles,
        'recentVehicles' => $recentVehicles,
        'catalogMakes' => $catalogMakes,
        'catalogCities' => $catalogCities,
        'catalogPriceCeiling' => $catalogPriceCeiling,
        'footerLinks' => [
            'termsUrl' => route('legal.terms'),
            'privacyUrl' => route('legal.privacy'),
            'cookiesUrl' => route('legal.cookies'),
        ],
    ];
@endphp
<div id="home-react" data-props='@json($homeProps)'></div>
@endsection
