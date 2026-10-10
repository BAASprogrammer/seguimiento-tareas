<template>
  <div class="flex min-h-screen flex-col bg-white text-slate-800 selection:bg-indigo-500 selection:text-white">
    <header class="border-b border-slate-200 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center shadow-md shadow-indigo-500/20">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <span class="font-bold text-lg text-slate-900 tracking-tight">Seguimiento de Tareas</span>
        </div>

        <nav aria-label="Navegación principal" class="flex items-center gap-2 sm:gap-3">
          <router-link
            to="/iniciar-sesion"
            class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:text-slate-900 sm:px-4"
          >
            Iniciar sesión
          </router-link>
          <router-link
            to="/registro"
            class="rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-700 sm:px-4"
          >
            Registrarme
          </router-link>
        </nav>
      </div>
    </header>

    <main class="flex flex-1 items-center bg-slate-50 px-4 py-14 sm:px-6 lg:px-8">
      <div class="mx-auto grid w-full max-w-6xl items-center gap-12 lg:grid-cols-[1.1fr_0.9fr]">
        <section class="max-w-xl">
          <h1 class="mt-4 text-4xl font-bold leading-tight tracking-tight text-indigo-600 sm:text-5xl">
            ¿Qué tienes pendiente?
          </h1>

          <p class="mt-5 max-w-lg text-base leading-relaxed text-slate-600">
            Anota tus tareas, agrúpalas por categoría y marca lo que ya terminaste. Todo en una lista, sin perder de vista lo que sigue.
          </p>

          <div class="mt-7 flex flex-col gap-3 sm:flex-row">
            <router-link
              to="/registro"
              class="inline-flex min-h-11 items-center justify-center rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-indigo-700"
            >
              Crear mi cuenta
            </router-link>
            <router-link
              to="/iniciar-sesion"
              class="inline-flex min-h-11 items-center justify-center rounded-lg border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-100"
            >
              Ya tengo cuenta
            </router-link>
          </div>
        </section>

        <section aria-label="Ejemplo de lista de tareas" class="mx-auto w-full max-w-md">
          <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
              <div>
                <h2 class="text-sm font-semibold text-slate-900">Mi lista</h2>
                <p class="mt-1 text-xs text-slate-500">Un ejemplo de cómo organizarte</p>
              </div>
              <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700">Hoy</span>
            </div>

            <ul class="divide-y divide-slate-100 px-5">
              <li class="flex items-center gap-3 py-4">
                <span class="h-4 w-4 rounded border-2 border-slate-300"></span>
                <span class="flex-1 text-sm text-slate-700">Enviar el presupuesto</span>
                <span class="rounded-full bg-amber-50 px-2 py-1 text-[11px] font-medium text-amber-700">Trabajo</span>
              </li>
              <li class="flex items-center gap-3 py-4">
                <span class="h-4 w-4 rounded border-2 border-slate-300"></span>
                <span class="flex-1 text-sm text-slate-700">Comprar para la cena</span>
                <span class="rounded-full bg-emerald-50 px-2 py-1 text-[11px] font-medium text-emerald-700">Personal</span>
              </li>
              <li class="flex items-center gap-3 py-4">
                <span class="flex h-4 w-4 items-center justify-center rounded border border-indigo-600 bg-indigo-600 text-white">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                  </svg>
                </span>
                <span class="flex-1 text-sm text-slate-400 line-through">Pedir turno médico</span>
                <span class="rounded-full bg-emerald-50 px-2 py-1 text-[11px] font-medium text-emerald-700">Personal</span>
              </li>
            </ul>
          </div>
        </section>
      </div>
    </main>
  </div>
</template>