@extends('layouts.app')

@section('title', 'Digital Twin — Sungai Brantas, Kota Batu')

@section('content')
  <h2 class="sr-only">Dashboard Sistem Peringatan Dini Digital Twin — pemantauan real-time kondisi Sungai Brantas di 3 titik sensor hulu, tengah, dan hilir dengan klasifikasi status NORMAL, WASPADA, dan BAHAYA.</h2>

  <div class="ews-root">
    @include('dashboard.partials.topbar')
    @include('dashboard.partials.nav')
    @include('dashboard.partials.overview')
    @include('dashboard.partials.detail')
    @include('dashboard.partials.riwayat')
    @include('dashboard.partials.konfigurasi')
  </div>
@endsection

@push('scripts')
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
  <script src="{{ asset('js/script.js') }}"></script>
@endpush
