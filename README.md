# Servicio-de-autenticaci-n-para-gesti-n-de-piezas
🏗️ Pieces Management System

Sistema profesional de gestión de piezas metálicas construido con arquitectura de microservicios, utilizando React + TypeScript en el frontend y Laravel + PostgreSQL (Supabase) en el backend.

🔗 Repositorios del Proyecto

Este sistema está dividido en 3 microservicios independientes:

Servicio	Descripción	Link
🔐 Auth Service	Autenticación JWT	https://github.com/AndresFGA26/Servicio-de-autenticaci-n-para-gesti-n-de-piezas.git

📦 Pieces Service	Gestión de piezas	https://github.com/AndresFGA26/Servicio-de-Gesti-n-de-Piezas-.git

🎨 Frontend	Interfaz React	https://github.com/AndresFGA26/gestion-de-piezas-front.git

📋 Descripción General

Sistema empresarial orientado a la gestión industrial de piezas metálicas:

🔐 Autenticación segura con JWT + Refresh Token

📊 Estructura jerárquica: Proyectos → Bloques → Piezas

⚡ Cálculo automático de peso y estado
📈 Reportes con métricas y visualización
🔄 CRUD completo en todas las entidades
📡 API REST versionada (/api/v1)

🏗️ Arquitectura
	
	Frontend (React)
	
     │
     ├───────────────► Auth Service (Laravel)
     │                   │ 
     │                   ▼
     │               JWT Token 
     │
     └───────────────► Pieces Service (Laravel)
                         │
                         ▼
                 Supabase (PostgreSQL)


🧩 Componentes

🔐 Auth Service

	Login con JWT
	Refresh Token
	Logout
	Middleware de autenticación
	Manejo de errores HTTP

📦 Pieces Service

	CRUD completo:
	Proyectos
	Bloques
	Piezas
	Relaciones jerárquicas
	Validaciones backend
	Paginación y filtros
	Reportes globales

🎨 Frontend

	React 19 + TypeScript
	Zustand (estado global)
	React Query (cache y sincronización)
	Axios con interceptores JWT
	TailwindCSS (UI moderna)
	Manejo de errores y loading states

🛠️ Stack Tecnológico
	
	Frontend
	React 19
	TypeScript
	TailwindCSS
	React Query
	Zustand
	Axios
	Backend
	Laravel 11+
	PostgreSQL (Supabase)
	JWT Authentication
	Eloquent ORM
🔐 Autenticación

	JWT (Access Token)
	Refresh Token	
	Middleware de protección en backend	
	Interceptores automáticos en frontend

🔑 Credenciales de Prueba

	Email:    admin@test.com
	
	Password: 12345678

🚀 Instalación

	Cada repositorio contiene su propia configuración.
	Orden de ejecución:
	Iniciar Auth Service (puerto 8000)
	Iniciar Pieces Service (puerto 8001)
	Iniciar Frontend (puerto 5173)

	📡 Endpoints Principales
	🔐 Auth Service
	POST /api/v1/login
	POST /api/v1/refresh
	POST /api/v1/logout
	
📦 Pieces Service

	GET    /api/v1/projects
	POST   /api/v1/projects
	GET    /api/v1/projects/{id}/blocks
	POST   /api/v1/blocks/{id}/pieces
	GET    /api/v1/pieces
	GET    /api/v1/reports/pieces
📊 Estado del Proyecto

	Área	Estado
	Auth Service	✅ Completo
	Pieces Service	✅ Completo
	Frontend	✅ Completo
	Reportes	✅ Implementado
	Arquitectura	⚠️ Parcial
	Seguridad	⚠️ Básica
	Testing	❌ No implementado

🚀 Fortalezas
	
	✔ Arquitectura desacoplada
	✔ CRUD completo funcional
	✔ UI moderna y profesional
	✔ Manejo de estado eficiente
	✔ Código limpio y estructurado
	⚠️ Limitaciones
	Base de datos compartida
	No hay API Gateway
	No hay testing automatizado
	Seguridad básica (sin rate limiting)
	Sin CI/CD ni despliegue automatizado
	
🎯 Evaluación Técnica
	
	✔ Proyecto defendible para nivel Junior/Mid
	
	Demuestra conocimientos en:
	
	Arquitectura de microservicios
	APIs REST
	Frontend moderno
	Backend con Laravel

📝 Conclusión

El sistema cumple con los requisitos funcionales y técnicos de la prueba, presentando una solución completa, organizada y lista para evaluación técnica.

👨‍💻 Autor

Andrés Felipe Guzmán Arrieta
