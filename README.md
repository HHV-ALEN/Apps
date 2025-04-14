# Apps

✨ Un sistema modular integrado para optimizar procesos empresariales

📌 Descripción General
Alen Apps es una plataforma unificada desarrollada en PHP + MySQL que centraliza múltiples módulos de negocio, eliminando redundancias y mejorando la eficiencia operacional.

🔹 Objetivo Principal: Consolidar sistemas independientes en una sola plataforma con:

✅ Gestión por roles y permisos

✅ Flujos de trabajo personalizados

✅ Información centralizada

🏗️ Estructura del Proyecto
📂 Módulos Implementados
Módulo	Estado	Descripción
🔹 Autenticación	✔️ Stable	Login con roles (Admin, Empleado, etc.)
🔹 Supply Chain	🚧 Beta	Gestión de almacén (entradas/salidas)
🔹 RRHH	Planned	Vacaciones y solicitudes
🧩 Arquitectura Técnica

AlenApps/
├── Back/           # Lógica PHP (controladores)
├── Front/          # Vistas (HTML/CSS/JS)
├── Assets/         # Imágenes/estilos
├── Database/       # Scripts SQL
└── Config/         # Conexiones y settings

⚙️ Configuración
🔧 Requisitos
PHP 7.4+

MySQL 5.7+

Apache/Nginx

🛠️ Instalación
git clone https://github.com/HHV-ALEN/Apps

mysql -u usuario -p < Database/alenapps.sql

define('DB_HOST', 'localhost');
define('DB_USER', 'usuario');

👨‍💻 Roles de Usuario
Rol	Permisos
👑 Admin	Acceso total + gestión de usuarios
📦 Almacén	Registrar entradas/salidas
👨‍💼 Empleado	Solicitar vacaciones/ver datos
🖥️ Capturas de Pantalla
(Incluir imágenes aquí con breve descripción)

🚀 Roadmap
Q3 2024: Módulo de Facturación

Q4 2024: Integración con API de SAP

🤝 Contribución
¡Bienvenidos PRs! Sigue estos pasos:

Haz fork del proyecto

Crea una rama: git checkout -b feature/nueva-funcion

Envía tu PR con una descripción clara

📜 Licencia
MIT License © 2025 [Heriberto Hurtado]

🔗 ¿Preguntas?
Contacto: hhurtado@alenintelligent.com

📝 Notas Adicionales
✨ En desarrollo activo

🔄 Actualizado el 12/Jul/2024
