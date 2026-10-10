import axios from 'axios'

const axiosApi = axios.create({
    baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
})

axiosApi.interceptors.request.use((config) => {
    const token = localStorage.getItem('token')
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
})
// Interceptor de tareasApi para manejar CRUD
export const tareasApi = {
    listar: () => axiosApi.get('/tareas'),
    crear: (tarea) => axiosApi.post('/tareas', tarea),
    actualizar: (id, tarea) => axiosApi.put(`/tareas/${id}`, tarea),
    eliminar: (id) => axiosApi.delete(`/tareas/${id}`),
}
// Interceptor de categoriasApi para manejar CRUD
export const categoriasApi = {
    listar: () => axiosApi.get('/categorias'),
    crear: (categoria) => axiosApi.post('/categorias', categoria),
    actualizar: (id, categoria) => axiosApi.put(`/categorias/${id}`, categoria),
    eliminar: (id) => axiosApi.delete(`/categorias/${id}`),
}
// Interceptor de autenticacionApi para manejar inicio de sesión, registro y cierre de sesión
export const autenticacionApi = {
    iniciarSesion: (credenciales) => axiosApi.post('/iniciar-sesion', credenciales),
    registrar: (datos) => axiosApi.post('/registrar', datos),
    cerrarSesion: () => axiosApi.post('/cerrar-sesion'),
}