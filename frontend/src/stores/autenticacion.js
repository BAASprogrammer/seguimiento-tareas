import { defineStore } from 'pinia'
import { autenticacionApi } from '../api/cliente'

function obtenerUsuarioGuardado() {
    const usuarioGuardado = localStorage.getItem('usuario')
    if (!usuarioGuardado) return null

    try {
        return JSON.parse(usuarioGuardado)
    } catch (error) {
        console.error('No se pudo restaurar el usuario guardado', error)
        localStorage.removeItem('usuario')
        return null
    }
}

export const useAutenticacionStore = defineStore('autenticacion', {
    state: () => ({
        usuario: obtenerUsuarioGuardado(),
        token: localStorage.getItem('token') || null,
    }),

    actions: {
        guardarSesion(data) {
            this.token = data.token
            this.usuario = data.usuario
            localStorage.setItem('token', data.token)
            localStorage.setItem('usuario', JSON.stringify(data.usuario))
        },

        async iniciarSesion(credenciales) {
            const { data } = await autenticacionApi.iniciarSesion(credenciales)
            this.guardarSesion(data)
        },

        async registrar(datos) {
            const { data } = await autenticacionApi.registrar(datos)
            this.guardarSesion(data)
        },

        async cerrarSesion() {
            await autenticacionApi.cerrarSesion()
            this.token = null
            this.usuario = null
            localStorage.removeItem('token')
            localStorage.removeItem('usuario')
        },
    },
})