<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800">Kelola Pengguna</h2>
  </x-slot>

  <div class="py-8">
    <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
      <x-flash />

      <div class="overflow-x-auto rounded-lg bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50 text-left text-gray-500">
            <tr>
              <th class="px-4 py-3 font-medium">Nama</th>
              <th class="px-4 py-3 font-medium">Email</th>
              <th class="px-4 py-3 font-medium">Role</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @foreach ($users as $user)
              <tr>
                <td class="px-4 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                <td class="px-4 py-3">
                  @if ($user->is(auth()->user()))
                    <span class="text-gray-500">{{ $user->role }} (akun Anda)</span>
                  @else
                    <form method="POST" action="{{ route('admin.users.role', $user) }}" class="flex items-center gap-2">
                      @csrf
                      @method('PATCH')
                      <select name="role" class="rounded-md border-gray-300 py-1 text-sm">
                        @foreach (App\Models\User::ROLES as $role)
                          <option value="{{ $role }}" @selected($user->role === $role)>{{ $role }}</option>
                        @endforeach
                      </select>
                      <button class="rounded-md border border-gray-300 px-3 py-1 hover:bg-gray-50">Simpan</button>
                    </form>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="mt-6">{{ $users->links() }}</div>
    </div>
  </div>
</x-app-layout>
