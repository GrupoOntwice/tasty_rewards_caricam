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

Ahora para hacer funcionar el proyecto cuando es un dominio se puede hacer apuntando a la carpeta raiz (/html)

## Configuración del Proyecto

1. Si la carpeta vendor no existe se debe:

- Posicionarse en la carpeta raíz del proyecto (/html)
- Eliminar el composer.lock
- Ejecutar

```

composer install


```

2. Editar en el archivo settings.php (html/sites/default/settings.php), agregando en las lineas 757 a la 766 el string de conection de la base datos:

```
$databases['default']['default'] = array (
  'database' => definido_por_DPM_PepsiCo,
  'username' => definido_por_DPM_PepsiCo,
  'password' => definido_por_DPM_PepsiCo,
  'host' => definido_por_DPM_PepsiCo,
  'port' => '3306',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
```

> NOTA: este paso solo se hace una unica vez


3. Instrucciones para correr de nuevo migraciones en el proyecto en caso de no contar con una base de datos valida:

Correr el siguiente comando:

```
drush cim -y

```
> NOTA: Si se tiene una de datos valida no este paso no es necesario

## Instrucciones para actualizar composer

Correr el siguiente comando:

```
composer update
```
