# Google meet install #

Requiere instalación de composer para meet, bajo la carpeta principal de meet composer require google/apiclient:^2.0

# Google calendar API #

1. Se necesita una cuenta google
2. Crear credenciales a través de la consola de google: console.cloud.google.com 
3. Crear un nuevo proyecto en Google Developers Console y obtener las credenciales de autorización para realizar solicitudes api.
4. Habilitar apis y servicios.
5. Añadir api de "Google calendar"
6. Crear credenciales, con claves de api
7. Asignar los permisos siguientes:
	- .../auth/calendar.events     Ver y editar eventos en tus calendarios
	- .../auth/calendar.events.owned		Consultar, crear, modificar y borrar eventos en los calendarios de Google de los que seas propietario


# Authorized URLS #

Añadir las urls autorizadas (uris) siguientes:
	https://DOMINIO-MOODLE/mod/hybridteaching/vc/meet/meetaccess.php



