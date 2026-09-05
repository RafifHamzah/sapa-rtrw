@extends('errors.layout')
@section('code', '403')
@section('title', 'Akses Ditolak')
@section('message', $exception?->getMessage() ?: 'Kamu tidak punya izin membuka halaman ini. Panel pengurus hanya untuk admin & pengurus RT.')
@section('secondary')
    <a class="btn ghost" href="{{ url('/admin') }}">Masuk sebagai Pengurus</a>
@endsection
