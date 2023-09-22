
AZURE CONFIG:

** Crear en azure una nueva App, o bien se permite utilizar la misma app que con el subplugin teams de hybridteaching.

Obtener: idcliente, idtenant (inquilino), secretclient.

En la configuración de AZURE:

1. Añadir URI de redirección:
https://NOMBRE DEL DOMINIO/mod/hybridteaching/store/sharepoint/classes/sharepointaccess.php

2. Asignar permisos de aplicación:
permisos de aplicación: Sharepoint / Sites.FullControl.All

3. Asignar permisos delegados:
permisos delegados: Sharepoint / AllSites.FullControl



DOCUMENTACIÓN PARA CONFIGURACIÓN PLUGIN:

guardar:
- clientid, tenantid, secretclient
- subdominio de sharepoint. 
Ejemplo: si el dominio sharepoint es https://nhb7.sharepoint.com, almacenar el valor "nhb7" en el campo subdominio de sharepoint de la configuración.