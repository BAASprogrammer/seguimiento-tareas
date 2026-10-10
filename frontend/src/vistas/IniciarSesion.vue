<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAutenticacionStore } from '../stores/autenticacion'
import { useValidarFormulario } from '../composables/useValidarFormulario'

const email = ref('')
const password = ref('')
const cargando = ref(false)
const auth = useAutenticacionStore()
const router = useRouter()
const { error, validarFormulario } = useValidarFormulario()

async function enviarFormulario() {
  if (!validarFormulario({ email: email.value, password: password.value })) {
    return
  }

  cargando.value = true

  try {
    await auth.iniciarSesion({ email: email.value.trim(), password: password.value })
    router.push('/tareas')
  } catch (e) {
    error.value = 'Correo o contraseña incorrectos'
  } finally {
    cargando.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-slate-50/80 flex flex-col justify-center py-12 sm:px-6 lg:px-8 text-slate-800 selection:bg-indigo-500 selection:text-white">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
      <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-600 shadow-md shadow-indigo-500/20 mb-4">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
        </svg>
      </div>
      <h2 class="text-3xl font-extrabold tracking-tight text-slate-900">¡Hola de nuevo!</h2>
      <p class="mt-2 text-sm text-slate-600">
        Ingresa a tu cuenta para continuar
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-6 shadow-xl border border-slate-200 sm:rounded-2xl sm:px-10">
        <form class="space-y-6" @submit.prevent="enviarFormulario" novalidate>
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Correo electrónico</label>
            <input
              v-model="email"
              type="email"
              required
              placeholder="tu@email.com"
              class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all text-sm"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Contraseña</label>
            <input
              v-model="password"
              type="password"
              required
              placeholder="••••••••"
              class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500 transition-all text-sm"
            />
          </div>

          <div v-if="error" class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs font-medium flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ error }}
          </div>

          <button
            type="submit"
            :disabled="cargando"
            class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-xl shadow-md shadow-indigo-500/20 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 disabled:opacity-50 transition-all text-sm flex items-center justify-center gap-2 cursor-pointer"
          >
            <span v-if="cargando" class="animate-spin rounded-full h-4 w-4 border-2 border-white/20 border-t-white"></span>
            {{ cargando ? 'Ingresando...' : 'Ingresar' }}
          </button>
        </form>

        <div class="mt-6 text-center">
          <p class="text-xs text-slate-500">
            ¿No tienes cuenta?
            <router-link to="/registro" class="font-semibold text-indigo-600 hover:text-indigo-700 transition-colors ml-1">
              Regístrate gratis
            </router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>