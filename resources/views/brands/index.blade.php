@extends('layouts.marketing')

@section('title', 'Marcas | Movikaa')

@section('head')
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
@vite(['resources/css/home.css', 'resources/js/brands.jsx'])
@endsection

@section('content')
<div id="brands-react" data-props='@json($props)'></div>
@endsection

