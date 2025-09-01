<!-- Exemplo de botão simples no menu -->
<button
    type="button"
    @click="toggle()"
    class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm 
        bg-white/70 dark:bg-gray-800 border border-gray-200 dark:border-gray-700
        hover:bg-white dark:hover:bg-gray-700 transition">
    <span x-show="theme !== 'dark'">🌙</span>
    <span x-show="theme === 'dark'">☀️</span>
    <span class="border-gray-200 dark:text-gray-200 dark:focus:border-indigo-700 focus:ring-indigo-700" x-text="theme === 'dark' ? 'Claro' : 'Escuro'"></span>
</button>