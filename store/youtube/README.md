

** Requiere instalación de composer para youtube, bajo la carpeta principal de youtube
    composer require google/apiclient:^2.0

**
INICIALMENTE SEGUIR ESTE TUTORIAL PARA LA CONFIGURACIÓN DE APIS DE YOUTUBE: 
(CAMBIARLO POR UNO OFICIAL EN LUGAR DE ESTA PÁGINA)
https://blog.hubspot.com/website/how-to-get-youtube-api-key


Se necesita una cuenta google
1.Crear credenciales a través de la consola de google: console.cloud.google.com 
2.Crear un nuevo proyecto en Google Developers Console y obtener las credenciales de autorización para realizar solicitudes api.
3.Habilitar apis y servicios.
4.Añadir api de "Youtube data api v3"
5.Crear credenciales, con claves de api
6.Ir en el menú a Pantalla de Consentimiento de OAuth. (Después enlace "Editar app" si ya está creada, y continuar abajo):
7.Botón Agretar o quitar permisos.
8.Asignar los permisos siguienTes:
			- .../auth/youtube.readonly     Ve tu cuenta de YouTube
			- .../auth/youtube				Administrar tu cuenta de YouTube
			- .../auth/youtubepartner		Permite ver y administrar tus elementos y el contenido asociado en YouTube.
			- .../auth/youtube.upload   	Administra tus videos de YouTube


Añadir las urls autorizadas (uris) siguientes:
https://DOMINIO-MOODLE/mod/hybridteaching/store/youtube    
https://DOMINIO-MOODLE/mod/hybridteaching/store/youtube/classes/youtube_handler.php
https://DOMINIO-MOODLE/admin/tool/task/scheduledtasks.php
https://DOMINIO-MOODLE/mod/hybridteaching/store/youtube/classes/youtubeaccess.php



** INFO ABOUT DOWNLOAD RECORDINGS:
No hay opciones en la API para poder descargar vídeos, hay opciones para visualizarlos pero la API no tiene métodos para descargarlos.
API: https://developers.google.com/youtube/v3
API DOC: https://developers.google.com/youtube/v3/docs/videos
https://developers.google.com/youtube/terms/api-services-terms-of-service

-  Crear canal de YouTube.