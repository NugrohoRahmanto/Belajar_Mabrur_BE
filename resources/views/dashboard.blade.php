@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="p-4 bg-white rounded shadow-sm">
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <div>
            <h3 class="mb-1">Dashboard</h3>
            <p class="mb-0">Halo, {{ auth()->user()->username ?? auth()->user()->name }} 👋</p>
        </div>

        {{-- Form kecil untuk ganti jumlah per halaman --}}
        <form method="get" class="gap-2 d-flex align-items-center">
            <label for="per_page" class="mb-0 me-2">Per page</label>
            <select name="per_page" id="per_page" class="form-select form-select-sm" style="width: auto"
                    onchange="this.form.submit()">
                @foreach([10,15,25,50] as $n)
                    <option value="{{ $n }}" {{ request('per_page',10)==$n ? 'selected' : '' }}>{{ $n }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table align-middle table-sm table-hover">
            <thead class="table-light">
                <tr>
                    <th style="width:70px;">#</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $index => $u)
                    <tr>
                        <td>{{ ($users->currentPage()-1)*$users->perPage() + $loop->iteration }}</td>
                        <td>{{ $u->username }}</td>
                        <td><span class="badge text-bg-secondary">{{ $u->role }}</span></td>
                        <td>
                            @if(!empty($u->token))
                                <span class="badge text-bg-success">Aktif</span>
                            @else
                                <span class="badge text-bg-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td>{{ $u->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">Belum ada user.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination (Bootstrap 5) --}}
    <div class="d-flex justify-content-between align-items-center">
        <div class="small text-muted">
            Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} user
        </div>
        <div>
            {{ $users->onEachSide(1)->links() }}
        </div>
    </div>
</div>
@endsection
