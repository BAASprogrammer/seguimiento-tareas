<script setup>
import { computed, ref, watch } from 'vue'
import { categoriasApi } from '../api/cliente'

const props = defineProps({
  categorias: {
    type: Array,
    default: () => []
  },
  tareas: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['datos-actualizados'])

const COLOR_PREDETERMINADO = '#4f46e5'

const mostrandoPanelCategorias = ref(false)
const mostrandoModal = ref(false)
const modoModal = ref('crear')
const categoriaSeleccionada = ref(null)
const formulario = ref({ nombre: '', color: COLOR_PREDETERMINADO })
const error = ref('')
const guardando = ref(false)
const eliminandoId = ref(null)
const paginaActual = ref(1)
const elementosPorPagina = ref(5)

const categoriasConCantidadTareas = computed(() =>
  props.categorias.map((categoria) => ({
    ...categoria,
    cantidadTareas: props.tareas.filter((tarea) => perteneceACategoria(tarea, categoria.id)).length
  }))
)

const cantidadPaginas = computed(() =>
  Math.max(1, Math.ceil(categoriasConCantidadTareas.value.length / elementosPorPagina.value))
)

const categoriasVisibles = computed(() => {
  const inicio = (paginaActual.value - 1) * elementosPorPagina.value
  return categoriasConCantidadTareas.value.slice(inicio, inicio + elementosPorPagina.value)
})

const primerElementoVisible = computed(() =>
  categoriasConCantidadTareas.value.length === 0
    ? 0
    : (paginaActual.value - 1) * elementosPorPagina.value + 1
)

const ultimoElementoVisible = computed(() =>
  Math.min(paginaActual.value * elementosPorPagina.value, categoriasConCantidadTareas.value.length)
)

const tareasCategoria = computed(() => {
  if (!categoriaSeleccionada.value) return []
  return props.tareas.filter((tarea) => perteneceACategoria(tarea, categoriaSeleccionada.value.id))
})

watch(cantidadPaginas, (nuevaCantidad) => {
  if (paginaActual.value > nuevaCantidad) {
    paginaActual.value = nuevaCantidad
  }
})

watch(elementosPorPagina, () => {
  paginaActual.value = 1
})

function perteneceACategoria(tarea, categoriaId) {
  return Number(tarea.categoria_id ?? tarea.categoria?.id) === Number(categoriaId)
}

function abrirCrear() {
  modoModal.value = 'crear'
  categoriaSeleccionada.value = null
  formulario.value = { nombre: '', color: COLOR_PREDETERMINADO }
  error.value = ''
  mostrandoModal.value = true
}

function abrirModalCategoria(modo, categoria) {
  modoModal.value = modo
  categoriaSeleccionada.value = categoria
  formulario.value = {
    nombre: categoria.nombre,
    color: categoria.color || COLOR_PREDETERMINADO
  }
  error.value = ''
  mostrandoModal.value = true
}

function cerrarModal() {
  mostrandoModal.value = false
  error.value = ''
}

async function guardarCategoria() {
  const categoria = {
    ...formulario.value,
    nombre: formulario.value.nombre.trim()
  }

  if (!categoria.nombre) {
    error.value = 'El nombre de la categoría es obligatorio.'
    return
  }

  guardando.value = true
  error.value = ''

  try {
    if (modoModal.value === 'crear') {
      await categoriasApi.crear(categoria)
    } else {
      await categoriasApi.actualizar(categoriaSeleccionada.value.id, categoria)
    }

    cerrarModal()
    emit('datos-actualizados')
  } catch (e) {
    console.error(e)
    error.value = 'No se pudo guardar la categoría. Inténtalo de nuevo.'
  } finally {
    guardando.value = false
  }
}

async function eliminarCategoria(categoria) {
  const cantidadTareas = props.tareas.filter((tarea) =>
    perteneceACategoria(tarea, categoria.id)
  ).length
  const avisoTareas = cantidadTareas
    ? ` Las ${cantidadTareas} tareas asociadas quedarán sin categoría.`
    : ''

  if (!confirm(`¿Eliminar la categoría "${categoria.nombre}"?${avisoTareas}`)) return

  eliminandoId.value = categoria.id
  try {
    await categoriasApi.eliminar(categoria.id)
    emit('datos-actualizados')
  } catch (e) {
    console.error(e)
    alert('No se pudo eliminar la categoría. Inténtalo de nuevo.')
  } finally {
    eliminandoId.value = null
  }
}
</script>

<template>
  <button
    type="button"
    @click="mostrandoPanelCategorias = true"
    class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-200"
  >
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z" />
    </svg>
    Ver categorías
  </button>

  <div
    v-if="mostrandoPanelCategorias"
    class="fixed inset-0 z-40 flex items-center justify-center overflow-y-auto bg-slate-900/40 p-4"
    @click.self="mostrandoPanelCategorias = false"
  >
  <section
    role="dialog"
    aria-modal="true"
    aria-labelledby="categorias-panel-titulo"
    class="my-auto max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl sm:p-6"
  >
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 id="categorias-panel-titulo" class="text-lg font-bold text-slate-900">Categorías</h2>
        <p class="mt-1 text-xs text-slate-500">Administra tus categorías y revisa las tareas asociadas.</p>
      </div>
      <div class="flex items-center gap-2">
        <button
          type="button"
          @click="abrirCrear"
          class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-indigo-700"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Agregar categoría
        </button>
        <button
          type="button"
          aria-label="Cerrar panel de categorías"
          @click="mostrandoPanelCategorias = false"
          class="rounded-lg p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <div v-if="categoriasConCantidadTareas.length" class="overflow-hidden rounded-xl border border-slate-200">
      <div class="overflow-x-auto">
        <table class="w-full min-w-[640px] divide-y divide-slate-200 text-left">
          <thead class="bg-slate-50">
            <tr>
              <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Categoría</th>
              <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Color</th>
              <th scope="col" class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Tareas</th>
              <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Acciones</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            <tr v-for="categoria in categoriasVisibles" :key="categoria.id" class="hover:bg-slate-50">
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <span
                    class="h-3.5 w-3.5 shrink-0 rounded-full border border-black/10"
                    :style="{ backgroundColor: categoria.color || COLOR_PREDETERMINADO }"
                  ></span>
                  <span class="text-sm font-medium text-slate-900">{{ categoria.nombre }}</span>
                </div>
              </td>
              <td class="px-4 py-3 text-sm text-slate-600">
                {{ categoria.color || COLOR_PREDETERMINADO }}
              </td>
              <td class="px-4 py-3 text-sm text-slate-600">
                {{ categoria.cantidadTareas }}
                {{ categoria.cantidadTareas === 1 ? 'tarea' : 'tareas' }}
              </td>
              <td class="px-4 py-3">
                <div class="flex justify-end gap-2">
                  <button
                    type="button"
                    @click="abrirModalCategoria('ver', categoria)"
                    class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-100"
                  >
                    Ver
                  </button>
                  <button
                    type="button"
                    @click="abrirModalCategoria('editar', categoria)"
                    class="rounded-lg border border-indigo-100 bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700 transition-colors hover:bg-indigo-100"
                  >
                    Editar
                  </button>
                  <button
                    type="button"
                    :disabled="eliminandoId === categoria.id"
                    @click="eliminarCategoria(categoria)"
                    class="rounded-lg border border-red-100 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 transition-colors hover:bg-red-100 disabled:cursor-wait disabled:opacity-60"
                  >
                    {{ eliminandoId === categoria.id ? 'Eliminando…' : 'Eliminar' }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex flex-col gap-3 border-t border-slate-200 bg-white px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2 text-xs text-slate-500">
          <label for="categorias-por-pagina">Filas por página</label>
          <select
            id="categorias-por-pagina"
            v-model.number="elementosPorPagina"
            class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
          >
            <option :value="5">5</option>
            <option :value="10">10</option>
            <option :value="20">20</option>
          </select>
          <span>
            {{ primerElementoVisible }}–{{ ultimoElementoVisible }} de {{ categoriasConCantidadTareas.length }}
          </span>
        </div>

        <nav aria-label="Paginación de categorías" class="flex items-center justify-end gap-2">
          <button
            type="button"
            :disabled="paginaActual === 1"
            @click="paginaActual--"
            class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
          >
            Anterior
          </button>
          <span class="px-2 text-xs text-slate-600">
            Página {{ paginaActual }} de {{ cantidadPaginas }}
          </span>
          <button
            type="button"
            :disabled="paginaActual >= cantidadPaginas"
            @click="paginaActual++"
            class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 transition-colors hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
          >
            Siguiente
          </button>
        </nav>
      </div>
    </div>

    <div v-else class="rounded-xl border border-dashed border-slate-300 px-4 py-8 text-center">
      <p class="text-sm font-medium text-slate-700">Todavía no tienes categorías.</p>
      <p class="mt-1 text-xs text-slate-500">Agrega una para organizar tus tareas.</p>
    </div>
  </section>
  </div>

  <div
    v-if="mostrandoModal"
    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/40 p-4"
    @click.self="cerrarModal"
  >
    <section
      role="dialog"
      aria-modal="true"
      aria-labelledby="categoria-modal-titulo"
      class="my-auto max-h-[90vh] w-full max-w-lg space-y-5 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl"
    >
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 id="categoria-modal-titulo" class="text-lg font-bold text-slate-900">
            {{ modoModal === 'ver' ? 'Detalle de categoría' : modoModal === 'editar' ? 'Editar categoría' : 'Nueva categoría' }}
          </h2>
          <p v-if="modoModal === 'ver'" class="mt-1 text-xs text-slate-500">Información y tareas de esta categoría.</p>
        </div>
        <button
          type="button"
          aria-label="Cerrar"
          @click="cerrarModal"
          class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <template v-if="modoModal === 'ver'">
        <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-4">
          <span
            class="h-5 w-5 rounded-full border border-black/10"
            :style="{ backgroundColor: categoriaSeleccionada.color || COLOR_PREDETERMINADO }"
          ></span>
          <div>
            <p class="text-sm font-semibold text-slate-900">{{ categoriaSeleccionada.nombre }}</p>
            <p class="text-xs text-slate-500">{{ tareasCategoria.length }} tareas asociadas</p>
          </div>
        </div>
        <div>
          <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Tareas</h3>
          <ul v-if="tareasCategoria.length" class="max-h-56 space-y-2 overflow-y-auto">
            <li
              v-for="tarea in tareasCategoria"
              :key="tarea.id"
              class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 px-3 py-2"
            >
              <span class="truncate text-sm text-slate-800">{{ tarea.titulo }}</span>
              <span class="shrink-0 text-[11px] capitalize text-slate-500">{{ tarea.estado || 'pendiente' }}</span>
            </li>
          </ul>
          <p v-else class="rounded-lg bg-slate-50 px-3 py-4 text-center text-xs text-slate-500">
            No hay tareas asociadas a esta categoría.
          </p>
        </div>
      </template>

      <form v-else @submit.prevent="guardarCategoria" class="space-y-4">
        <div>
          <label for="categoria-nombre" class="mb-1 block text-xs font-semibold text-slate-700">Nombre</label>
          <input
            id="categoria-nombre"
            v-model="formulario.nombre"
            type="text"
            maxlength="255"
            required
            placeholder="Ej: Trabajo, Personal, Estudio"
            class="w-full rounded-xl border border-slate-300 bg-slate-50 px-3.5 py-2 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
          />
        </div>
        <div>
          <label for="categoria-color" class="mb-1 block text-xs font-semibold text-slate-700">Color</label>
          <div class="flex items-center gap-3">
            <input
              id="categoria-color"
              v-model="formulario.color"
              type="color"
              class="h-10 w-14 cursor-pointer rounded-lg border border-slate-300 bg-slate-50 p-1"
            />
            <span class="text-xs text-slate-500">{{ formulario.color }}</span>
          </div>
        </div>
        <p v-if="error" role="alert" class="text-xs text-red-600">{{ error }}</p>
        <div class="flex justify-end gap-3 pt-2">
          <button
            type="button"
            @click="cerrarModal"
            class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-600 transition-colors hover:bg-slate-200"
          >
            Cancelar
          </button>
          <button
            type="submit"
            :disabled="guardando"
            class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-md shadow-indigo-500/20 transition-colors hover:bg-indigo-700 disabled:cursor-wait disabled:opacity-60"
          >
            {{ guardando ? 'Guardando…' : modoModal === 'editar' ? 'Guardar cambios' : 'Crear categoría' }}
          </button>
        </div>
      </form>

      <div v-if="modoModal === 'ver'" class="flex justify-end">
        <button
          type="button"
          @click="cerrarModal"
          class="rounded-xl bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-600 transition-colors hover:bg-slate-200"
        >
          Cerrar
        </button>
      </div>
    </section>
  </div>
</template>
