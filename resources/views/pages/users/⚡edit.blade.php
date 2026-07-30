<?php

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Livewire\Forms\UserForm;
use App\Models\User;
new #[Title('Edit User')] class extends Component {
    public UserForm $form;
 

    public function mount(User $user)
    {
        $this->form->setUser($user);
    }
    public function save()
    {
        $this->form->update();
        session()->flash('message', 'نم تعديل العميل بنحاح.');
        $this->redirect('/users');
    }

};
?>

<div class="px-4 sm:px-6 lg:px-8">
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                تعديل عميل
            </h2>
        </div>
    </div>

    <div class="mt-8 max-w-3xl">
        <form wire:submit="save">
            <div class="space-y-6">
                {{-- Personal Information --}}
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
                            معلومات شخصية
                        </h3>
                        
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            {{-- First Name --}}
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">
                                    الاسم 
                                </label>
                                <input 
                                    wire:model.blur="form.name"
                                    type="text" 
                                    id="name"
                                    class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                                >
                                @error('form.name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
 

                            {{-- Email --}}
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">
                                    الايميل
                                </label>
                                <input 
                                    wire:model.blur="form.email"
                                    type="email" 
                                    id="email"
                                    class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                                >
                                @error('form.email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- password --}}
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">
                                    الرقم السري
                                </label>
                                <input 
                                    wire:model.blur="form.password"
                                    type="password" 
                                    id="password"
                                    class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                                >
                                @error('form.password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Phone --}}
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700">
                                    رقم الهاتف
                                </label>
                                <input 
                                    wire:model="form.phone"
                                    type="text" 
                                    id="phone"
                                    class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                                >
                                @error('form.phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                          
                        </div>
                    </div>
                </div>

           

                {{-- Actions --}}
                <div class="flex justify-end gap-3">
                    <a 
                        href="{{ route('users.index') }}"
                        class="rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        الغاء
                    </a>
                    <button 
                        type="submit"
                        class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                      تعديل العميل
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>