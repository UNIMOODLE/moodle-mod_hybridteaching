
CONFIGURACIÓN PREVIA EN MICROSOFT AZURE:

Necesitará una suscripción a Microsoft Azure. Si no tiene una, peude crear una visitando:
    https://azure.microsoft.com/en-us/free/

Debe configurar Microsoft Azure para administrar su Microsoft 365 Azure Active Directory. Una gúí está disponible aquí:
   https://learn.microsoft.com/en-us/entra/identity-platform/quickstart-create-new-tenant 


Registrar una nueva aplicación en la plataforma Microsoft Azure, o bien si se utiliza el subplugin Teams de Hybridteaching se puede reutilizar la misma aplicación creada para ello.
El registro de la aplicación establece una relación de confianza entre la aplicació y la plataforma de identidad de Microsoft: la confianza es unidreccional: la aplicación confía en la plataforma de identidad de Microsoft y no al revés. 
Puede seguir los pasos desde esta guía:
    https://learn.microsoft.com/es-es/entra/identity-platform/quickstart-register-app?tabs=certificate

Una vez creada/disponible la aplicación, puede obtener sus valores de ClientID, TenantID y Secreto.
Si se utiliza la app creada con Teams, puede reutilizar también los mismos valores de ClientID, TenantID y Secreto para añadir la configuración moodle en la sección de OneDrive. Sino, utilice los nuevos valores creados con la nueva aplicación.


Los permisos a asignar en Azure son:
- Permisos de aplicación:
    Directory.ReadWrite.All
    Files.ReadWrite.All
    Sites.FullControl.All

- Permisos delegados:
    Directory.AccessAsUser.All
    Directory.ReadWrite.All
    Sites.ReadWrite.All
    Files.ReadWrite.All
    offline_access

Se deben añadir urls de redirección en la aplicación de Azure. En Azure, desde el menú de Autenticación, añadiendo una plataforma web. La url de redirección a añadir es: 

https://NOMBRE DEL DOMINIO/mod/hybridteaching/store/onedrive/classes/onedriveaccess.php

