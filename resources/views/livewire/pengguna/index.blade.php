<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public $name, $username, $email, $password, $role = 'Pedagang', $editId;
    public $isOpen = false;
    public $search = '';

    public function render(): mixed
    {
        $data = User::where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('username', 'like', '%' . $this->search . '%')
                    ->paginate(10);
        return view('livewire.pengguna.index', compact('data'));
    }

    public function create()
    {
        $this->reset('name', 'username', 'email', 'password', 'role', 'editId');
        $this->isOpen = true;
    }

    public function store()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $this->editId,
            'email' => 'nullable|email|max:255',
            'role' => 'required|string',
        ];

        if (!$this->editId || $this->password) {
            $rules['password'] = 'required|string|min:6';
        }

        $this->validate($rules);

        $userData = [
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
        ];

        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }

        User::updateOrCreate(['id' => $this->editId], $userData);
        
        session()->flash('message', $this->editId ? 'User diupdate.' : 'User ditambahkan.');
        $this->isOpen = false;
        $this->reset();
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->editId = $id;
        $this->name = $user->name;
        $this->username = $user->username;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->password = '';
        $this->isOpen = true;
    }

    public function delete($id)
    {
        if(auth()->id() == $id) {
            session()->flash('error', 'Tidak dapat menghapus akun sendiri.');
            return;
        }
        User::findOrFail($id)->delete();
        session()->flash('message', 'User dihapus.');
    }
};
?>
<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Manajemen Pengguna</h2>
        <button wire:click="create" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i> Tambah
        </button>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded-lg">{{ session('message') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 px-4 py-2 bg-red-100 text-red-700 rounded-lg">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100">
            <input type="text" wire:model.live="search" placeholder="Cari..." class="w-full md:w-1/3 rounded-lg border-gray-300">
        </div>
        
        <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                    <th class="px-6 py-3">Nama</th>
                    <th class="px-6 py-3">Username</th>
                    <th class="px-6 py-3">Role</th>
                    <th class="px-6 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $item)
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $item->name }}</td>
                    <td class="px-6 py-4">{{ $item->username }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">{{ $item->role }}</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <button wire:click="edit({{ $item->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Edit</button>
                        <button wire:click="delete({{ $item->id }})" wire:confirm="Yakin?" class="text-red-600 hover:text-red-900">Hapus</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4">{{ $data->links() }}</div>
    </div>

    @if($isOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6">
            <h3 class="text-lg font-bold mb-4">{{ $editId ? 'Edit' : 'Tambah' }} Pengguna</h3>
            <form wire:submit="store">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama</label>
                    <input type="text" wire:model="name" class="w-full rounded border-gray-300">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                    <input type="text" wire:model="username" class="w-full rounded border-gray-300">
                    @error('username') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                    <input type="email" wire:model="email" class="w-full rounded border-gray-300">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Role</label>
                    <select wire:model="role" class="w-full rounded border-gray-300">
                        <option value="Pedagang">Pedagang</option>
                        <option value="Staff Penagihan">Staff Penagihan</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Password {{ $editId ? '(Kosongkan jika tidak diubah)' : '' }}</label>
                    <input type="password" wire:model="password" class="w-full rounded border-gray-300">
                    @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="$set('isOpen', false)" class="px-4 py-2 bg-gray-200 rounded">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
