# Pepsico - Tasty Rewards 2022

## Requisitos

Los requerimientos necesarios para que el proyecto funcione son los siguientes:

- Linux, distibución utilizada por nosotros Ubuntu 20.04.4 LTS
- MySQL 5.7
- Redis >= 5.0
- PHP >= 7.4
- Composer para poder emplear la carpeta vendor (https://getcomposer.org/download/)
- Apache
  - Tener activo modo rewrite

Ahora para hacer funcionar el proyecto cuando es un dominio se puede hacer apuntando a la carpeta raiz (/html)

## Configuración del Proyecto

Para ejecutar el proyecto es necesario ocupar composer de la siguiente manera:

- Posicionarse en la carpeta raíz del proyecto (/html)

- Ejecutar

```

composer install


```

- Editar en el archivo settings.php (html/sites/default/settings.php), agregando en las lineas 757 a la 766 el string de conection de la base datos:

```
$databases['default']['default'] = array (
	'database' => '',
	'username' => '',
	'password' => '',
	'prefix' => '',
	'host' => '',
	'port' => '3306',
	'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
	'driver' => 'mysql',
);
```

> Nota: este paso solo se hace una unica vez


## Instrucciones para correr de nuevo migraciones en el proyecto

Correr el siguiente comando:

```
drush cim -y

```


## Instrucciones para actualizar composer

Correr el siguiente comando:

```
composer update
```
