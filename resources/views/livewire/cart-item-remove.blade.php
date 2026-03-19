<div>
    <button wire:click="remove()" class="text-red-500 flex items-center cursor-pointer">
        Hapus
        <div wire:loading
            class="animate-spin inline-block size-4 border-3 border-current border-t-transparent rounded-[999px] text-primary"
            role="status" aria-label="loading">
            <span class="sr-only">Loading...</span>
        </div>
    </button>
</div>
