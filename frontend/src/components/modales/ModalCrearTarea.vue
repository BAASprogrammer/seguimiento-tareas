<script setup>
import { ref } from 'vue'
import { tareasApi } from '../../api/cliente'

const errorTareas = ref('')
const guardando = ref(false)
const props = defineProps({
  mostrandoModalCrear: {
    type: Boolean,
    default: false
  },
  categorias: {
    type: Array,
    default: () => []
  }
})
const emit = defineEmits(['cerrar-modal', 'nueva-tarea'])
const nuevaTarea = ref({ titulo: '', descripcion: '', categoria_id: '', estado: 'pendiente' })


async function crearTarea() {
  const tarea = {
    ...nuevaTarea.value,
    titulo: nuevaTarea.value.titulo.trim(),
    descripcion: nuevaTarea.value.descripcion.trim()
  }

  if (!tarea.titulo || !tarea.descripcion || !tarea.categoria_id) {
    errorTareas.value = 'Completa el título, la descripción y la categoría.'
    return
  }

  guardando.value = true
  errorTareas.value = ''
  try {
    const { data } = await tareasApi.crear(tarea)
    emit('nueva-tarea', data)
    nuevaTarea.value = { titulo: '', descripcion: '', categoria_id: '', estado: 'pendiente' }
    emit('cerrar-modal')
  } catch (e) {
    console.error(e)
    errorTareas.value = e.response?.data?.message || 'No se pudo crear la tarea. Inténtalo de nuevo.'
  } finally {
    guardando.value = false
  }
}
</script>
<template>
    <!-- Modal Crear Tarea -->
    <div v-if="mostrandoModalCrear" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40">
      <div class="bg-white border border-slate-200 rounded-2xl p-6 w-full max-w-md shadow-2xl space-y-4">
        <h3 class="text-lg font-bold text-slate-900">Nueva Tarea</h3>

        <form @submit.prevent="crearTarea" class="space-y-4" novalidate>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Título</label>
                <input
                v-model="nuevaTarea.titulo"
                type="text"
                required
                placeholder="Ej: Diseñar prototipo de la app"
                class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 text-sm"
                />
            </div>
            <div class="relative">
              <label class="block text-xs font-semibold text-slate-700 mb-1">Categoría</label>
              <select
              v-model="nuevaTarea.categoria_id"
              required
              class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 text-sm appearance-none pr-10"
              >
              <option disabled value="">Selecciona una categoría</option>
              <option 
                  v-for="cat in categorias" 
                  :key="cat.id" 
                  :value="cat.id"
                  class="text-slate-700"
              >
                  {{ cat.nombre }}
              </option>
              </select>
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute right-3 top-2.5 pointer-events-none text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Descripción</label>
            <textarea
              v-model="nuevaTarea.descripcion"
              rows="3"
              placeholder="Detalles sobre esta tarea..."
              class="w-full px-3.5 py-2 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 text-sm"
            ></textarea>
          </div>


          <div v-if="errorTareas" class="text-xs text-red-500 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ errorTareas }}
          </div>

          <div class="flex items-center justify-end gap-3 pt-2">
            <button
              type="button"
                :disabled="guardando"
                @click="emit('cerrar-modal')"
              class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all cursor-pointer"
            >
              Cancelar
            </button>
            <button
              type="submit"
              :disabled="guardando"
              class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md shadow-indigo-500/20 transition-all cursor-pointer"
            >
              {{ guardando ? 'Guardando…' : 'Guardar tarea' }}
            </button>
          </div>
        </form>
      </div>
    </div>
</template>