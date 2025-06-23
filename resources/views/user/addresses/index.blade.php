@extends('layouts.app')
@section('content')

<main class="pt-100">
    <div class="container">
        <h4 class="mb-4">My Addresses</h4>

        <div class="row">
            <!-- Sidebar -->
            <div class="col-12 col-lg-3 mb-4 mb-lg-0">
                @include('user.account-nav')
            </div>

            <!-- Konten Utama -->
            <div class="col-12 col-lg-9">
                @forelse ($addresses as $address)
                    <div class="card mb-3 shadow-sm {{ $address->isdefault ? 'border-primary' : '' }}">
                        <div class="card-body">
                            <h5 class="card-title">{{ $address->name }}</h5>
                            <p class="card-text mb-1"><strong>Phone:</strong> {{ $address->phone }}</p>
                            <p class="card-text mb-1"><strong>Address:</strong> {{ $address->address }}, {{ $address->locality }}, {{ $address->city }}, {{ $address->state }}, {{ $address->zip }}</p>
                            <p class="card-text mb-2"><strong>Country:</strong> {{ $address->country }}</p>
                            @if($address->isdefault)
                                <span class="badge bg-primary">Default</span>
                            @endif

                            <div class="d-flex justify-content-end mt-2">
                                <a href="{{ route('user.addresses.edit', $address->id) }}" class="btn btn-sm btn-outline-secondary me-2">Edit</a>

                                <form method="POST" action="{{ route('user.addresses.destroy', $address->id) }}" onsubmit="return confirm('Yakin ingin menghapus alamat ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">Belum ada alamat yang ditambahkan.</p>
                @endforelse
            </div>
        </div>
    </div>
</main>

@endsection
