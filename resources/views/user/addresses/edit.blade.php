@extends('layouts.app')
@section('content')
<main class="pt-100">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                @include('user.account-nav')
            </div>

            <!-- Form Edit -->
            <div class="col-lg-9">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">Edit Alamat</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('user.addresses.update', $address) }}">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Penerima</label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $address->name) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Nomor Telepon</label>
                                <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $address->phone) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Alamat Lengkap</label>
                                <textarea name="address" id="address" rows="3" class="form-control" required>{{ old('address', $address->address) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="locality" class="form-label">Kelurahan/Desa</label>
                                <input type="text" name="locality" id="locality" class="form-control" value="{{ old('locality', $address->locality) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="city" class="form-label">Kota</label>
                                <input type="text" name="city" id="city" class="form-control" value="{{ old('city', $address->city) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="state" class="form-label">Provinsi</label>
                                <input type="text" name="state" id="state" class="form-control" value="{{ old('state', $address->state) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="zip" class="form-label">Kode Pos</label>
                                <input type="text" name="zip" id="zip" class="form-control" value="{{ old('zip', $address->zip) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="landmark" class="form-label">Patokan (Landmark)</label>
                                <input type="text" name="landmark" id="landmark" class="form-control" value="{{ old('landmark', $address->landmark) }}">
                            </div>

                            <div class="mb-3">
                                <label for="country" class="form-label">Negara</label>
                                <input type="text" name="country" id="country" class="form-control" value="{{ old('country', $address->country ?? 'Indonesia') }}" required>
                            </div>

                            <div class="form-check mb-3">
                                <input type="hidden" name="isdefault" value="0">
                                <input class="form-check-input" type="checkbox" name="isdefault" id="isdefault" value="1" {{ $address->isdefault ? 'checked' : '' }}>
                                <label class="form-check-label" for="isdefault">
                                    Jadikan sebagai alamat utama
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            <a href="{{ route('user.addresses') }}" class="btn btn-outline-secondary ms-2">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
