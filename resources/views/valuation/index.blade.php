@extends('layouts.marketing')

@section('title', 'Tasador | Movikaa')
@section('meta_description', 'Calcula una estimación de mercado para tu vehículo en Costa Rica con datos como año, kilometraje, condición y configuración.')

@section('head')
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
@vite(['resources/css/home.css', 'resources/js/valuation.jsx'])
@endsection

@section('content')
<div id="valuation-react" data-props='@json($valuationProps)'></div>
@endsection


