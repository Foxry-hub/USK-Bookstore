@extends('layouts.app')

@section('content')
    <section>
        <h1 class="text-2xl font-bold text-slate-900">List User Terdaftar</h1>

        <div class="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-100 text-left text-slate-700">
                    <tr>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Terdaftar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $user->name }}</td>
                            <td class="px-4 py-3">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                @if ($user->is_admin)
                                    <span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700">Admin</span>
                                @else
                                    <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">User</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $user->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada user.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $users->links() }}</div>
    </section>
@endsection
