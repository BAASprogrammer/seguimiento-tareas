<script setup>
import { computed, ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { categoriasApi, tareasApi } from '../api/cliente'
import { useAutenticacionStore } from '../stores/autenticacion'
import ModalCrearTarea from '../components/modales/ModalCrearTarea.vue'
import PanelCategorias from '../components/PanelCategorias.vue'

const auth = useAutenticacionStore()
const router = useRouter()

const tareas = ref([])
const categorias = ref([])
const cargando = ref(true)
const errorCarga = ref('')
const errorEstado = ref('')

// Modals / Forms
const nuevaTarea = ref({ titulo: '', descripcion: '', categoria_id: '', estado: 'pendiente' })
const mostrandoModalCrear = ref(false)
const tareaAEliminar = ref(null)
const eliminandoTarea = ref(false)
const errorEliminacion = ref('')
const paginaTareas = ref(1)
const tareasPorPagina = ref(5)

const cantidadPaginasTareas = computed(() =>
  Math.max(1, Math.ceil(tareas.value.length / tareasPorPagina.value))
)

const tareasVisibles = computed(() => {
  const inicio = (paginaTareas.value - 1) * tareasPorPagina.value
  return tareas.value.slice(inicio, inicio + tareasPorPagina.value)
})

const primerTareaVisible = computed(() =>
  tareas.value.length === 0 ? 0 : (paginaTareas.value - 1) * tareasPorPagina.value + 1
)

const ultimaTareaVisible = computed(() =>
  Math.min(paginaTareas.value * tareasPorPagina.value, tareas.value.length)
)

function formatearFecha(fecha) {
  if (!fecha) return '—'

  const fechaValida = new Date(fecha)
  if (Number.isNaN(fechaValida.getTime())) return '—'

  return new Intl.DateTimeFormat('es-AR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hourCycle: 'h23'
  }).format(fechaValida)
}

watch(cantidadPaginasTareas, (cantidad) => {
  if (paginaTareas.value > cantidad) paginaTareas.value = cantidad
})

watch(tareasPorPagina, () => {
  paginaTareas.value = 1
})

async function cargarDatos(mostrarCarga = true) {
  if (mostrarCarga) cargando.value = true
  errorCarga.value = ''
  try {
    const [resTareas, resCategorias] = await Promise.all([
      tareasApi.listar(),
      categoriasApi.listar()
    ])
    tareas.value = resTareas.data
    categorias.value = resCategorias.data
  } catch (e) {
    console.error(e)
    errorCarga.value = 'No se pudieron cargar las tareas. Comprueba tu conexión e inténtalo de nuevo.'
  } finally {
    if (mostrarCarga) cargando.value = false
  }
}



async function cambiarEstado(tarea, nuevoEstado) {
  errorEstado.value = ''
  try {
    const { data } = await tareasApi.actualizar(tarea.id, {
      ...tarea,
      estado: nuevoEstado
    })
    const index = tareas.value.findIndex(t => t.id === tarea.id)
    if (index !== -1) tareas.value[index] = data
  } catch (e) {
    console.error(e)
    errorEstado.value = 'No se pudo actualizar el estado de la tarea. Inténtalo de nuevo.'
  }
}

function agregarTarea(tarea) {
  tareas.value.unshift(tarea)
  paginaTareas.value = 1
}

function confirmarEliminacion(tarea) {
  tareaAEliminar.value = tarea
  errorEliminacion.value = ''
}

function cancelarEliminacion() {
  if (eliminandoTarea.value) return
  tareaAEliminar.value = null
  errorEliminacion.value = ''
}

async function eliminarTarea() {
  if (!tareaAEliminar.value || eliminandoTarea.value) return

  eliminandoTarea.value = true
  errorEliminacion.value = ''
  try {
    await tareasApi.eliminar(tareaAEliminar.value.id)
    tareas.value = tareas.value.filter((tarea) => tarea.id !== tareaAEliminar.value.id)
    tareaAEliminar.value = null
  } catch (e) {
    console.error(e)
    errorEliminacion.value = 'No se pudo eliminar la tarea. Inténtalo de nuevo.'
  } finally {
    eliminandoTarea.value = false
  }
}

async function salir() {
  await auth.cerrarSesion()
  router.push('/iniciar-sesion')
}

onMounted(cargarDatos)
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-800 selection:bg-indigo-500 selection:text-white">
    <!-- Navbar -->
    <header class="border-b border-slate-200 bg-white sticky top-0 z-40 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center shadow-md shadow-indigo-500/20">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <span class="font-bold text-lg text-slate-900 tracking-tight">Seguimiento de Tareas</span>
        </div>

        <div class="flex items-center gap-4">
          <span class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700">
            {{ auth.usuario?.nombre || auth.usuario?.name || auth.usuario?.email || 'Usuario' }}
          </span>
          <button
            @click="salir"
            class="px-3.5 py-1.5 text-xs font-semibold text-slate-700 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded-lg transition-all cursor-pointer"
          >
            Cerrar sesión
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Title & Actions Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
          <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Panel de Tareas</h1>
          <p class="text-sm text-slate-500 mt-1">Organiza tu trabajo y da seguimiento a tus objetivos</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
          <PanelCategorias
            :categorias="categorias"
            :tareas="tareas"
            @datos-actualizados="cargarDatos(false)"
          />
          <button
            @click="mostrandoModalCrear = true"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-medium text-sm rounded-xl shadow-md shadow-indigo-500/20 transition-all cursor-pointer"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nueva Tarea
          </button>
        </div>
      </div>

      <p v-if="errorEstado" role="alert" class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ errorEstado }}
      </p>

      <!-- Tareas -->
      <div v-if="cargando" class="flex justify-center py-20">
        <div class="animate-spin rounded-full h-8 w-8 border-2 border-indigo-600 border-t-transparent"></div>
      </div>

      <div v-else-if="errorCarga" role="alert" class="rounded-2xl border border-red-200 bg-white px-6 py-10 text-center shadow-sm">
        <p class="text-sm font-semibold text-red-700">{{ errorCarga }}</p>
        <button
          type="button"
          @click="cargarDatos()"
          class="mt-4 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-700"
        >
          Reintentar
        </button>
      </div>

      <div v-else-if="tareas.length === 0" class="text-center py-16 bg-white border border-slate-200 rounded-2xl shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-slate-400 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <p class="text-slate-700 text-sm font-semibold">No hay tareas pendientes</p>
        <p class="text-slate-500 text-xs mt-1">Crea una nueva tarea para comenzar</p>
      </div>

      <div v-else class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
          <table class="w-full min-w-[850px] divide-y divide-slate-200 text-left">
            <thead class="bg-slate-50">
              <tr>
                <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Tarea</th>
                <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Categoría</th>
                <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Estado</th>
                <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Fecha y hora completada</th>
                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="tarea in tareasVisibles" :key="tarea.id" class="hover:bg-slate-50">
                <td class="max-w-sm px-4 py-3">
                  <p class="truncate text-sm font-medium text-slate-900">{{ tarea.titulo }}</p>
                  <p class="mt-1 truncate text-xs text-slate-500">{{ tarea.descripcion || 'Sin descripción' }}</p>
                </td>
                <td class="px-4 py-3 text-sm text-slate-600">
                  {{ tarea.categoria?.nombre || categorias.find(categoria => Number(categoria.id) === Number(tarea.categoria_id))?.nombre || 'Sin categoría' }}
                </td>
                <td class="px-4 py-3">
                  <span
                    :class="{
                      'border-emerald-200 bg-emerald-50 text-emerald-700': tarea.estado === 'completada',
                      'border-amber-200 bg-amber-50 text-amber-700': tarea.estado === 'en_proceso' || tarea.estado === 'en progreso',
                      'border-slate-200 bg-slate-100 text-slate-600': tarea.estado === 'pendiente'
                    }"
                    class="inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-semibold capitalize"
                  >
                    {{ (tarea.estado || 'pendiente').replace('_', ' ') }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-slate-600">
                  {{ formatearFecha(tarea.fecha_completada) }}
                </td>
                <td class="px-4 py-3">
                  <div class="flex justify-end gap-2">
                    <button
                      v-if="tarea.estado !== 'completada'"
                      type="button"
                      @click="cambiarEstado(tarea, 'completada')"
                      title="Marcar completada"
                      aria-label="Marcar completada"
                      class="rounded-lg border border-emerald-200 bg-emerald-50 p-1.5 text-emerald-600 transition-colors hover:bg-emerald-100"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                    </button>
                    <button
                      v-if="tarea.estado === 'pendiente'"
                      type="button"
                      @click="cambiarEstado(tarea, 'en progreso')"
                      title="En proceso"
                      aria-label="Marcar en proceso"
                      class="rounded-lg border border-amber-200 bg-amber-50 p-1.5 text-amber-600 transition-colors hover:bg-amber-100"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </button>
                    <button
                      v-if="tarea.estado === 'completada'"
                      type="button"
                      @click="cambiarEstado(tarea, 'pendiente')"
                      title="Volver a pendiente"
                      aria-label="Volver a pendiente"
                      class="rounded-lg border border-slate-200 bg-slate-100 p-1.5 text-slate-600 transition-colors hover:bg-slate-200"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h4a5 5 0 019.9-1M21 14h-4a5 5 0 01-9.9 1M3 4v6h6m12 10v-6h-6" />
                      </svg>
                    </button>
                    <button
                      type="button"
                      @click="confirmarEliminacion(tarea)"
                      title="Eliminar tarea"
                      aria-label="Eliminar tarea"
                      class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
          <div class="flex items-center gap-2 text-xs text-slate-500">
            <label for="tareas-por-pagina">Filas por página</label>
            <select
              id="tareas-por-pagina"
              v-model.number="tareasPorPagina"
              class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
            >
              <option :value="5">5</option>
              <option :value="10">10</option>
              <option :value="20">20</option>
            </select>
            <span>{{ primerTareaVisible }}–{{ ultimaTareaVisible }} de {{ tareas.length }}</span>
          </div>

          <nav aria-label="Paginación de tareas" class="flex items-center justify-end gap-2">
            <button
              type="button"
              :disabled="paginaTareas === 1"
              @click="paginaTareas--"
              class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
            >
              Anterior
            </button>
            <span class="px-2 text-xs text-slate-600">Página {{ paginaTareas }} de {{ cantidadPaginasTareas }}</span>
            <button
              type="button"
              :disabled="paginaTareas >= cantidadPaginasTareas"
              @click="paginaTareas++"
              class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
            >
              Siguiente
            </button>
          </nav>
        </div>
      </div>
    </main>
    <ModalCrearTarea
      v-if="mostrandoModalCrear"
      :mostrandoModalCrear="mostrandoModalCrear"
      :nuevaTarea="nuevaTarea"
      :tareas="tareas"
      :categorias="categorias"
      @cerrar-modal="mostrandoModalCrear = false"
      @nueva-tarea="agregarTarea"
    />

    <div
      v-if="tareaAEliminar"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4"
      @click.self="cancelarEliminacion"
    >
      <section
        role="alertdialog"
        aria-modal="true"
        aria-labelledby="confirmar-eliminacion-titulo"
        aria-describedby="confirmar-eliminacion-descripcion"
        class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl"
      >
        <div class="mb-4 flex items-start gap-3">
          <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
            </svg>
          </span>
          <div>
            <h2 id="confirmar-eliminacion-titulo" class="text-base font-bold text-slate-900">
              Eliminar tarea
            </h2>
            <p id="confirmar-eliminacion-descripcion" class="mt-1 text-sm text-slate-600">
              ¿Seguro que deseas eliminar “{{ tareaAEliminar.titulo }}”? Esta acción no se puede deshacer.
            </p>
          </div>
        </div>

        <p v-if="errorEliminacion" role="alert" class="mb-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">
          {{ errorEliminacion }}
        </p>

        <div class="flex justify-end gap-3">
          <button
            type="button"
            :disabled="eliminandoTarea"
            @click="cancelarEliminacion"
            class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-200 disabled:cursor-not-allowed disabled:opacity-60"
          >
            Cancelar
          </button>
          <button
            type="button"
            :disabled="eliminandoTarea"
            @click="eliminarTarea"
            class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-red-700 disabled:cursor-wait disabled:opacity-60"
          >
            {{ eliminandoTarea ? 'Eliminando…' : 'Eliminar tarea' }}
          </button>
        </div>
      </section>
    </div>

  </div>
</template>
