# Documentación del Proyecto API Inventario

Este proyecto es una API desarrollada con Laravel para gestionar usuarios, productos, categorías y proveedores. A continuación se describe los pasos para la configuración, uso Swagger, despliegue y decisiones de diseño.

## 1. Instrucciones para configurar localmente

### Requisitos
- PHP >= 8.1
- Composer
- Laravel >= 10.x
- MySQL / MariaDB

### Instalación

- Abre la terminar y clona el repositorio :
   git clone https://github.com/PG210/inentario.git

### Instalación de dependencias

- composer install
- composer require laravel/sanctum
- php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

### Copiar archivo de entorno y generar llave

- cp .env.example .env
- php artisan key:generate

### Configurar la BD en .env

DB_DATABASE=inventary
DB_USERNAME=root
DB_PASSWORD=

### Migrar la Base de datos

php artisan migrate

### Ejecutar Seeder

php artisan db:seed

### Regenerar la documentación Swagger

php artisan l5-swagger:generate

### Iniciar el servidor

php artisan serve

## 2. Cómo importar y usar la colección Postman o el archivo Swagger.

En este caso se utilizó Swagger, acontinuación se decribe los pasos para utilizarlo.

- Generar la documentación con: php artisan l5-swagger:generate
- Ir al navegador en: http://localhost:8000/api/documentation

## Para usar los endpoints protegidos.

- Registrarse como usuario (Solamente puede ver y filtrar información).
- Si desea ingresar como administrador utilizar las credenciales: email: admin@gmail.com y password: 1234 permite (Crear, actualizar y eliminar).
- Obtener el token 
- Hacer clic en el botón "Authorize".
- Ingresar el token Bearer: Value <tu_token> y clic en Authorize.

## URL pública de despliegue

El proyecto se encuentra dispobible en: 
Documentación de Swagger: https://inventario.approyectos.site/

## 3. Decisiones de diseño

### Elección de enum vs tabla de roles.

Se decidió implementar enum frente a una tabla ya que los tipos de roles son pocos y constantes, además evitamos implementar una tabla adicional y su relación con la tabla users, mejorando el rendimiento en la aplicación.

### Middleware o paquete de autorización.

Se creo un middleware personalizado denominado (admin), permitiendo un manejo de simple y directo en el controlador.

### Cualquier cambio al esquema de BD o endpoints originales.

- Se creo una tabla adicional Suppliers (proveedores), con los siguientes tributos: name, email, phone, address, description, created_at, update_at

- Se agrego el atributo supplier_id a la tabla products, este atributo es una llave foranea que hace referencia a la tabla suppliers.
- Se creo un seeder que genera un usuario con el rol admin (email: admin@gmail.com y password: 1234), este usuario inicial facilita el acceso al sistema en entornos de desarrollo y pruebas.
- Se creo el middleware (admin) para proteger los endpoints (Store, Update, Destroy).
- Se ajustaron los mensajes de respuesta.

## Información de Despliegue

La aplicación se desplego en Hostinger utilizando los siguientes pasos:
- 1. Se creo el subdominio https://inventario.approyectos.site/ y la carpeta interna apuntando a public
- 2. Fue creada la base de datos con: nombre DB, usuario DB y contraseña.
- 3. Se realizó la conexión a través de SSH al servidor y se hizo copia del repositorio de github.
- 4. Fueron instaladas las dependencias requeridas para levantar el proyecto.
- 5. Se ejecutaron la migración de las tablas y se actualizó la documentación.
- 6. Modificación del archivo .env con los ajustes requeridos para producción y la base de datos
      - APP_NAME=Laravel
      - APP_ENV=production
      - APP_KEY=base64:Key
      - APP_DEBUG=false
      - APP_URL=https://inventario.approyectos.site
- 7. Modificación del archivo config/cors.php en la linea 'allowed_origins' => ['https://inventario.approyectos.site/'], esto con fin de mantener la seguridad del sitio y acepte las peticiones Http o Https.
- 8. Modificación del archivo storage/api-docs/api-docs.json en la linea:
     "url": "https://inventario.approyectos.site/",
     "description": "production"

- 9. Pruebas de funcionamiento a nivel de cada enpoint.


