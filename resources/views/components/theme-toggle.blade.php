<div class="flex items-center justify-center">
  <div
    class="flex rounded-md overflow-hidden border transition-colors duration-300"
    :class="theme === 'dark' ? 'border-gray-900' : 'border-gray-300'"
  >
    <!-- Botão Claro -->
    <button
      type="button"
      @click="toggle()"
      :aria-pressed="theme !== 'dark'"
      :class="theme !== 'dark'
                ? 'bg-indigo-600 text-white'
                : 'bg-gray-800 text-gray-400 hover:bg-gray-700'"
      class="px-1 py-0.5 inline-flex items-center gap-3 transition-all duration-300 ease-in-out"
      title="Ativar tema claro"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
      </svg>
      <span>Claro</span>
    </button>

    <!-- Botão Escuro -->
    <button
      type="button"
      @click="toggle()"
      :aria-pressed="theme === 'dark'"
      :class="theme === 'dark'
                ? 'bg-indigo-600 text-white'
                : 'bg-gray-200 text-gray-800 hover:bg-gray-300'"
      class="px-1 py-0.5 inline-flex items-center gap-2 transition-all duration-300 ease-in-out"
      title="Ativar tema escuro"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
      </svg>
      <span>Escuro</span>
    </button>
  </div>
</div>
