# Seguimiento de Tareas

> **Estado: en desarrollo.** La aplicación continúa evolucionando y puede recibir cambios en sus funcionalidades y configuración.

Aplicación web para organizar tareas personales. Permite crear tareas, agruparlas por categorías, consultar su estado y marcar tareas como completadas o volver a dejarlas pendientes.

## Funcionalidades

- Registro e inicio de sesión.
- Creación, edición y eliminación de tareas.
- Estados pendiente, en progreso y completada.
- Registro de fecha y hora de finalización.
- Gestión de categorías.
- Listados en tablas con paginación.

## Tecnologías

- **Frontend:** Vue 3, Vite, Vue Router, Pinia, Axios y Tailwind CSS.
- **Backend:** Laravel 13, PHP 8.4 y Laravel Sanctum.

## Estructura

```text
.
├── app/       # API REST y backend Laravel
├── frontend/  # Interfaz Vue
└── docker-compose.yml
```

## Requisitos

- Docker Engine o Docker Desktop.
- Docker Compose v2 (`docker compose`).

PHP, Node.js y PostgreSQL se ejecutan en contenedores; no es necesario instalarlos directamente en la máquina.

## Iniciar el proyecto

Desde la raíz del repositorio, Docker Compose toma `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` del archivo `.env` de la raíz para configurar PostgreSQL. La configuración de Laravel en `app/.env` debe usar los mismos valores y conectarse al host `postgres` por el puerto `5432`. Luego inicia los servicios:

```bash
docker compose up -d
```

Docker Compose levanta:

- **`app`**: API Laravel con PHP 8.4, en `http://localhost:8000`.
- **`frontend`**: servidor de desarrollo Vue/Vite con Node.js 20, en `http://localhost:5173`.
- **`postgres`**: PostgreSQL 16. El puerto `5436` del equipo se redirige al puerto `5432` del contenedor.

La primera vez que se prepara el entorno, instala las dependencias del backend usando el contenedor oficial de Composer:

```bash
docker run --rm -v "$PWD/app:/app" -w /app composer:2 install
```

Ejecuta las migraciones dentro del contenedor de Laravel:

```bash
docker compose exec app php artisan migrate
```

Abre `http://localhost:5173` para usar la aplicación. El frontend utiliza por defecto la API en `http://localhost:8000/api`.

## Comandos útiles

Ver el estado y los logs de los contenedores:

```bash
docker compose ps
docker compose logs -f
```

Ejecutar las pruebas del backend:

```bash
docker compose exec app php artisan test
```

Compilar el frontend dentro de su contenedor:

```bash
docker compose exec frontend npm run build
```

Detener los contenedores sin borrar los datos de PostgreSQL:

```bash
docker compose down
```

Los datos de PostgreSQL se conservan en el volumen `postgres-data`. Para eliminar también ese volumen y sus datos, Docker Compose permite ejecutar `docker compose down -v`.

## Contribuciones

Las mejoras y reportes de errores son bienvenidos. Como el proyecto está en desarrollo, revisa el comportamiento actual y valida los cambios antes de utilizarlos en un entorno de producción.
