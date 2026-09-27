<div
    x-data="{ open: false, downloadNotice: false }"
    x-init="
        window.addEventListener('pageshow', e => { if (e.persisted) open = false });

        const navTarget = (target) => {
            const a = target.closest('a[href]');
            if (!a) return null;
            if (a.target === '_blank') return null;
            if (a.hasAttribute('download')) return null;
            if (a.hasAttribute('data-download')) return null;
            const href = a.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) return null;
            try {
                const url = new URL(href, window.location.href);
                if (url.origin !== window.location.origin) return null;
            } catch(e) { return null; }
            return a;
        };

        document.addEventListener('click', e => {
            if (e.defaultPrevented || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
            if (navTarget(e.target)) { open = true; return; }

            const da = e.target.closest('a[href][data-download]');
            if (!da) return;
            downloadNotice = true;
            setTimeout(() => { downloadNotice = false; }, 3000);
        });
    "
    @submit.window="if (!$event.defaultPrevented) open = true"
    @page-loading.window="open = true"
>
    <div x-show="open" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl flex flex-col items-center justify-center gap-4 p-8"
             style="width: 300px; height: 200px;">
            <svg class="animate-spin h-10 w-10 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-medium text-gray-700">Processing...</span>
        </div>
    </div>

    <div x-show="downloadNotice" x-cloak
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-[60] flex items-center gap-3 bg-gray-800 text-white text-sm font-medium px-4 py-3 rounded-lg shadow-lg">
        <svg class="w-4 h-4 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0-3-3m3 3 3-3M3 17V7a2 2 0 0 1 2-2h6l2 2h6a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
        </svg>
        Download started
    </div>
</div>
