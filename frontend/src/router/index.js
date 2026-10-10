import { createRouter, createWebHistory } from 'vue-router'
import { useAutenticacionStore } from '../stores/autenticacion'
import IniciarSesion from '../vistas/IniciarSesion.vue'
import Home from '../vistas/Home.vue'
import Registro from '../vistas/Registro.vue'
import Tareas from '../vistas/Tareas.vue'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', component: Home },
        { path: '/iniciar-sesion', component: IniciarSesion },
        { path: '/registro', component: Registro },
        { path: '/tareas', component: Tareas, meta: { requiereAuth: true } },
    ],
})

router.beforeEach((to) => {
    const auth = useAutenticacionStore()

    if (to.meta.requiereAuth && !auth.token) {
        return '/iniciar-sesion'
    }

    return true
})

export default router