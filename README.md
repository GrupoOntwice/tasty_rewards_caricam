# Pepsico - Tasty Rewards Caricam 2022

## Requisitos

Los requerimientos necesarios para que el proyecto funcione son los siguientes:

- Linux, distibución utilizada por nosotros Ubuntu 20.04.4 LTS
- MySQL 5.7
- Redis >= 5.0
- PHP >= 7.4
- Drupal 9.X
- Composer para poder emplear la carpeta vendor (https://getcomposer.org/download/)
- Apache
  - Tener activo modo rewrite
  - Verificar:
    - El servidor APACHE debe estar direccionado a la carpeta  **/html**
    - Desde esta misma carpeta (/html) se encontrara el archivo **index.php** el cual permite la carga del sitio.

> NOTA: Esta instalacion no corresponde a un multisite, por lo que el index siempre estara en **/html** 

## Configuración del Proyecto

1. Si la carpeta vendor **NO** existe se debe:

- Posicionarse en la carpeta raíz del proyecto (/html)
- Eliminar el composer.lock
- Ejecutar el siguiente comando:

```

composer install


```

2. Editar en el archivo settings.php (html/sites/default/settings.php), agregando en las lineas 757 a la 766 el string de conexion de la base datos:

```
  $databases['default']['default'] = array (
    'database' => definido_por_DPM_PepsiCo,
    'username' => definido_por_DPM_PepsiCo,
    'password' => definido_por_DPM_PepsiCo,
    'host' => definido_por_DPM_PepsiCo,
    'port' => definido_por_DPM_PepsiCo,
    'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
    'driver' => 'mysql',
  );
```

> NOTA: Estos cambios deben ser implementados cada vez que se vaya a realizar una nueva instalación.
>       La referencia a la carpeta /html/sites/default/ en este README es unicamente para la instaciona de la base de datos.


3. Dirigirse a la **carpeta /db** donde se debe tomar el archivo  **sql.zip** llamado  **dump.sql.zip** para levantar la base de datos que debe tener el mismo nombre que fue definido en el paso anterior al momento de una nueva instalación o actualización del sitio.

- Despues de la importación ejecutar el comando:

```
drush cr

```

**NOTA:** En caso de generarse un error cuando se ejecuta un comando que incluya **drush**, se solicita enviar la respuesta via ticket implementando el comando de la siguiente manera:

```
drush [Comando] --debug
ej:
drush cr --debug

```
