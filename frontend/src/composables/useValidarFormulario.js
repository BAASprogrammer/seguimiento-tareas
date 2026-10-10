import { ref } from 'vue'

export function useValidarFormulario() {
  const error = ref('')

  function validarFormulario({ email, password, nombre, confirmarPassword }) {
    error.value = ''

    if (nombre !== undefined && !nombre.trim()) {
      error.value = 'El nombre es obligatorio'
      return false
    }

    if (!email.trim()) {
      error.value = 'El correo electrónico es obligatorio'
      return false
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim())) {
      error.value = 'Correo electrónico inválido'
      return false
    }

    if (!password) {
      error.value = 'La contraseña es obligatoria'
      return false
    }

    if (
      confirmarPassword !== undefined
      && (password.length < 8 || !/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d])/.test(password))
    ) {
      error.value = 'La contraseña debe tener al menos 8 caracteres y contener una mayúscula, una minúscula, un número y un símbolo'
      return false
    }

    if (confirmarPassword !== undefined && !confirmarPassword) {
      error.value = 'La confirmación de la contraseña es obligatoria'
      return false
    }

    if (confirmarPassword !== undefined && password !== confirmarPassword) {
      error.value = 'Las contraseñas no coinciden'
      return false
    }

    return true
  }

  return { error, validarFormulario }
}
