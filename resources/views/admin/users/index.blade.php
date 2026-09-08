@extends('layouts.admin')

@section('page-title', 'Kelola Pengguna')

@section('content')
<div class="space-y-6 w-full">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-800">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Kelola Pengguna</h1>
            <p class="text-sm text-slate-300 mt-1">Kelola akun administrator dan pengguna aplikasi</p>
        </div>
        <div>
            <button type="button" 
                    onclick="openCreateModal()" 
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-500 active:bg-blue-700 transition-colors shadow-sm">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Tambah Pengguna</span>
            </button>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if (session('success'))
        <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm font-medium text-emerald-400 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-300">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm font-medium text-rose-400 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-300">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm font-medium text-rose-400 space-y-1">
            @foreach ($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- Search & Filter Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full max-w-2xl">
            <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </div>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari nama, username, atau email..." 
                       class="block w-full h-11 rounded-xl border border-slate-800 bg-slate-900 pl-11 pr-4 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
            </div>

            <div class="w-full sm:w-44">
                <select name="role" 
                        onchange="this.form.submit()" 
                        class="block w-full h-11 rounded-xl border border-slate-800 bg-slate-900 px-3.5 text-sm text-white focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                    <option value="">Semua Peran</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}" {{ request('role') === $role->value ? 'selected' : '' }}>
                            {{ ucfirst($role->value) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="sm:hidden rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-500 transition-colors">
                Cari
            </button>
        </form>

        @if(request('search') || request('role'))
            <a href="{{ route('admin.users.index') }}" class="text-sm text-slate-400 hover:text-slate-200 underline shrink-0">
                Reset Filter
            </a>
        @endif
    </div>

    <!-- Data Table Container -->
    <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-950/60 text-xs font-semibold uppercase tracking-wider text-slate-400 border-b border-slate-800">
                    <tr>
                        <th scope="col" class="px-6 py-4 w-16">ID</th>
                        <th scope="col" class="px-6 py-4">Pengguna</th>
                        <th scope="col" class="px-6 py-4">Email</th>
                        <th scope="col" class="px-6 py-4 text-center">Peran</th>
                        <th scope="col" class="px-6 py-4">Terdaftar</th>
                        <th scope="col" class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="px-6 py-4 font-mono text-slate-400">
                                #{{ $user->id }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-800 border border-slate-700 font-semibold text-sm text-blue-400">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-white flex items-center gap-1.5">
                                            {{ $user->name }}
                                            @if($user->id === auth()->id())
                                                <span class="text-[10px] bg-blue-600/20 text-blue-300 border border-blue-500/30 px-1.5 py-0.5 rounded font-mono">Anda</span>
                                            @endif
                                        </p>
                                        <p class="text-xs text-slate-400 font-mono">@<span>{{ $user->username }}</span></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-300">
                                {{ $user->email }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($user->isAdmin())
                                    <span class="inline-flex items-center rounded-md bg-blue-600/10 px-2.5 py-1 text-xs font-semibold text-blue-400 border border-blue-500/20">
                                        Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-slate-800 px-2.5 py-1 text-xs font-medium text-slate-300 border border-slate-700">
                                        User
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-400 font-mono">
                                {{ $user->created_at ? $user->created_at->format('d M Y, H:i') : '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <!-- Edit Button -->
                                    <button type="button" 
                                            onclick='openEditModal(@json($user))'
                                            class="p-2 rounded-lg text-slate-400 hover:text-blue-400 hover:bg-slate-800 transition-colors"
                                            title="Edit Pengguna">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                    </button>

                                    <!-- Delete Button -->
                                    @if ($user->id === auth()->id())
                                        <button type="button" 
                                                disabled 
                                                class="p-2 rounded-lg text-slate-600 cursor-not-allowed"
                                                title="Tidak dapat menghapus akun Anda sendiri">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    @else
                                        <button type="button" 
                                                onclick="openDeleteModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                class="p-2 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition-colors"
                                                title="Hapus Pengguna">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <svg class="h-8 w-8 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                    </svg>
                                    <p class="text-sm font-medium">Tidak ada data pengguna</p>
                                    @if(request('search') || request('role'))
                                        <p class="text-xs text-slate-500">Tidak ada pengguna yang cocok dengan kriteria pencarian saat ini</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="border-t border-slate-800 p-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Create User Modal -->
<div id="createModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900 p-5 sm:p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-semibold text-white">Tambah Pengguna Baru</h3>
            <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf

            <div>
                <label for="create_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Nama Lengkap <span class="text-rose-400">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="create_name" 
                       required 
                       placeholder="Contoh: John Doe"
                       class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="create_username" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Username <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" 
                           name="username" 
                           id="create_username" 
                           required 
                           placeholder="johndoe"
                           class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>

                <div>
                    <label for="create_role" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Peran <span class="text-rose-400">*</span>
                    </label>
                    <select name="role" 
                            id="create_role" 
                            required 
                            class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}">{{ ucfirst($role->value) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="create_email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Alamat Email <span class="text-rose-400">*</span>
                </label>
                <input type="email" 
                       name="email" 
                       id="create_email" 
                       required 
                       placeholder="johndoe@example.com"
                       class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="create_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Password <span class="text-rose-400">*</span>
                    </label>
                    <input type="password" 
                           name="password" 
                           id="create_password" 
                           required 
                           minlength="8"
                           placeholder="Min. 8 karakter"
                           class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>

                <div>
                    <label for="create_password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Ulangi Password <span class="text-rose-400">*</span>
                    </label>
                    <input type="password" 
                           name="password_confirmation" 
                           id="create_password_confirmation" 
                           required 
                           minlength="8"
                           placeholder="Ulangi password"
                           class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" 
                        onclick="closeCreateModal()" 
                        class="rounded-xl border border-slate-800 px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-500 active:bg-blue-700 transition-colors">
                    Simpan Pengguna
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900 p-5 sm:p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-semibold text-white">Edit Data Pengguna</h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="editForm" method="POST" action="" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="edit_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Nama Lengkap <span class="text-rose-400">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="edit_name" 
                       required 
                       class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="edit_username" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Username <span class="text-rose-400">*</span>
                    </label>
                    <input type="text" 
                           name="username" 
                           id="edit_username" 
                           required 
                           class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                </div>

                <div>
                    <label for="edit_role" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                        Peran <span class="text-rose-400">*</span>
                    </label>
                    <select name="role" 
                            id="edit_role" 
                            required 
                            class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}">{{ ucfirst($role->value) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="edit_email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                    Alamat Email <span class="text-rose-400">*</span>
                </label>
                <input type="email" 
                       name="email" 
                       id="edit_email" 
                       required 
                       class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
            </div>

            <div class="border-t border-slate-800/80 pt-3">
                <p class="text-xs text-slate-400 mb-3">Ganti Password (kosongkan jika tidak ingin mengubah password):</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Password Baru
                        </label>
                        <input type="password" 
                               name="password" 
                               id="edit_password" 
                               minlength="8"
                               placeholder="Min. 8 karakter"
                               class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                    </div>

                    <div>
                        <label for="edit_password_confirmation" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">
                            Ulangi Password
                        </label>
                        <input type="password" 
                               name="password_confirmation" 
                               id="edit_password_confirmation" 
                               minlength="8"
                               placeholder="Ulangi password"
                               class="block w-full rounded-xl border border-slate-800 bg-slate-950 px-3.5 py-2.5 text-sm text-white placeholder-slate-400 focus:border-blue-500 focus:outline-hidden focus:ring-1 focus:ring-blue-500 transition-colors">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" 
                        onclick="closeEditModal()" 
                        class="rounded-xl border border-slate-800 px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-500 active:bg-blue-700 transition-colors">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-xs flex items-center justify-center p-4 hidden">
    <div class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900 p-5 sm:p-6 shadow-xl space-y-4">
        <div class="flex items-center gap-3 text-rose-400">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-500/10 border border-rose-500/20">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-semibold text-white">Hapus Pengguna</h3>
                <p class="text-xs text-slate-400">Tindakan ini tidak dapat dibatalkan</p>
            </div>
        </div>

        <p class="text-sm text-slate-300">
            Apakah Anda yakin ingin menghapus akun pengguna <span id="deleteUserName" class="font-semibold text-white"></span>? Pengguna ini tidak akan dapat masuk ke dalam sistem lagi.
        </p>

        <form id="deleteForm" method="POST" action="" class="flex items-center justify-end gap-3 pt-2">
            @csrf
            @method('DELETE')
            <button type="button" 
                    onclick="closeDeleteModal()" 
                    class="rounded-xl border border-slate-800 px-4 py-2.5 text-sm font-medium text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                Batal
            </button>
            <button type="submit" 
                    class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-rose-500 active:bg-rose-700 transition-colors">
                Hapus Pengguna
            </button>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('createModal').classList.remove('hidden');
        document.getElementById('create_name').focus();
    }

    function closeCreateModal() {
        document.getElementById('createModal').classList.add('hidden');
    }

    function openEditModal(user) {
        const form = document.getElementById('editForm');
        form.action = `/admin/users/${user.id}`;
        
        document.getElementById('edit_name').value = user.name || '';
        document.getElementById('edit_username').value = user.username || '';
        document.getElementById('edit_email').value = user.email || '';
        const roleValue = (typeof user.role === 'object' && user.role !== null) ? user.role.value : user.role;
        document.getElementById('edit_role').value = roleValue || 'user';
        document.getElementById('edit_password').value = '';
        document.getElementById('edit_password_confirmation').value = '';

        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('edit_name').focus();
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function openDeleteModal(id, name) {
        const form = document.getElementById('deleteForm');
        form.action = `/admin/users/${id}`;
        document.getElementById('deleteUserName').textContent = name;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
            closeDeleteModal();
        }
    });
</script>
@endsection
