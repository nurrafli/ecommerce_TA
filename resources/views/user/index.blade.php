@extends('layouts.app')

@section('content')
<style>
    
   
    @media (max-width: 768px) {
        .page-title {
            font-size: 1.5rem;
        }
    }
</style>
<main class="pt-150">
    <section class="my-account container">
        <h2 class="page-title mb-4"></h2>
        <div class="row gy-4">
            <!-- Sidebar -->
            <div class="col-12 col-lg-3">
                @include('user.account-nav')
            </div>

            <!-- Content -->
            <div class="col-12 col-lg-9">
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

                            <div class="form-floating mb-3">
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       id="name" value="{{ old('name', auth()->user()->name) }}" required>
                                <label for="name">Full Name</label>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" value="{{ old('email', auth()->user()->email) }}" required>
                                <label for="email">Email Address</label>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                                <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror"
                                       id="mobile" value="{{ old('mobile', auth()->user()->mobile) }}" required>
                                <label for="mobile">Mobile Number</label>
                                @error('mobile')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <hr class="my-4">

                            <h5 class="mb-3">Change Password <small class="text-muted">(optional)</small></h5>

                            <div class="form-floating mb-3">
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                                       id="password" autocomplete="new-password">
                                <label for="password">New Password</label>
                                @error('password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                                <input type="password" name="password_confirmation" class="form-control"
                                       id="password_confirmation" autocomplete="new-password">
                                <label for="password_confirmation">Confirm New Password</label>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary text-uppercase">
                                    Save Changes
                                </button>
                            </div>
                            @if (session('success'))
                                <div class="alert alert-success">
                                    {{ session('success') }}
                                </div>
                            @endif
                        </form>
                    </div> <!-- end card-body -->
                </div> <!-- end card -->
            </div>
        </div>
    </section>
</main>
@endsection
