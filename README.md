# SOMOS Experiences
## _Job test_

### Requisitos: 
- Crear formulario HTML
- CRUD API Películas (Crear, leer, actualizar y eliminar)

### Descripción:
Hay que crear un formulario HTML para dar de alta películas con los campos **título**, **descripción** y **año**.
Tambíen un formulario para modificar una película ya creada con un botón de **ELIMINAR**.
Todas las acciones (menos la de leer), tienen que ser llamadas por Ajax a una API.

### Aplicación y métodos:

La aplicación es MVC.
- **Controladores**: Los controladores de la aplicación están en _src/Controllers/_ (_hay un ejemplo den src/Controllers/UserController.php_)
- **Model**: Los modelos de la apliación están en _src/Model/_ (_hay un ejemplo den src/Model/User.php_)
- **Vistas**: Las vistas están en _src/views/_
- **Rutas**: En _src/routes.php_ están las rutas a las que la aplicación llama.
- **.env**: Para la configuración del acceso a basse de datos

## Installation

Instalar dependencias:

```sh
composer install
```

Si necesitas compilar scss:

```sh
sh style.sh
```
