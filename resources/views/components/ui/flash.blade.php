{{-- Pesan sesudah aksi (status / error). 'announce' memicu suara panggilan. --}}
@if (session('status') || session('error') || $errors->has('forbidden'))
    <div class="toast {{ session('error') ? 'toast-error' : 'toast-ok' }}" role="status" data-toast
         @if (session('announce')) data-announce="{{ session('announce') }}" @endif>
        <x-ui.icon :name="session('error') ? 'alert-circle' : 'check-circle'" :size="20" />
        <p>{{ session('error') ?? session('status') }}</p>
        @if (session('announce'))
            <button type="button" class="toast-action" data-speak aria-label="Putar ulang suara panggilan">
                <x-ui.icon name="volume" :size="16" /> Putar suara
            </button>
        @endif
        <button type="button" class="toast-close" data-toast-close aria-label="Tutup pesan"><x-ui.icon name="x" :size="16" /></button>
    </div>
@endif
