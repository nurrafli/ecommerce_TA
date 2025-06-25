@extends('layouts.app')

@section('content')
<style>
    
   
    @media (max-width: 768px) {
        .page-title {
            font-size: 1.5rem;
        }
    }
</style>
<main class="pt-5 pb-5">
    <section class="container my-account">
        <h2 class="page-title mb-4 text-center">Account Settings</h2>
        <div class="row">
            
            <!-- Sidebar -->
            <aside class="col-md-3 mb-4">
                @include('user.account-nav')
            </aside>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success mb-4">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form action="{{ route('user.account.update') }}" method="POST" class="needs-validation" novalidate>
                            @csrf
                            @method('PUT')

                            <!-- Name -->
                            <div class="form-floating mb-3">
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       id="name" value="{{ old('name', auth()->user()->name) }}" required>
                                <label for="name">Full Name</label>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="form-floating mb-3">
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" value="{{ old('email', auth()->user()->email) }}" required>
                                <label for="email">Email Address</label>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Mobile -->
                            <div class="form-floating mb-3">
                                <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror"
                                       id="mobile" value="{{ old('mobile', auth()->user()->mobile) }}" required>
                                <label for="mobile">Mobile Number</label>
                                @error('mobile')
                                    <span class="invalid-feedback">{{ $message }}</span>

@endsection
