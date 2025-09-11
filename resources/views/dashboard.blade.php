@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="p-4 bg-white rounded shadow-sm">
    <h3 class="mb-2">Dashboard</h3>
    <p>Halo, {{ auth()->user()->username ?? auth()->user()->name }} 👋</p>
    <p>Selamat datang di dashboard.</p>
</div>
@endsection
