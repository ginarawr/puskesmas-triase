{{-- Layout tamu (login / daftar): bilah merek di atas, konten di tengah. --}}
@props(['title' => null])
<x-layouts.app :title="$title" width="narrow">
    <x-slot:header>
        <header class="navbar navbar-plain">
            <div class="container navbar-inner"><x-ui.brand :href="url('/')" /></div>
        </header>
    </x-slot:header>
    <div class="guest-stage">{{ $slot }}</div>
</x-layouts.app>
