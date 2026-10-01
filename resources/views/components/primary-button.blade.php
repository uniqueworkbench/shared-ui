<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-bt_primary-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-bt_primary-700 focus:bg-bt_primary-700 active:bg-bt_primary-800 focus:outline-none focus:ring-2 focus:ring-bt_primary-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
