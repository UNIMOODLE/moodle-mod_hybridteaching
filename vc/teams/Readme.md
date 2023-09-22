

** Crear en azure una nueva App

Obtener: idcliente, idtenant (inquilino), secretclient.

1. Añadir URI de redirección:
https://NOMBRE DEL DOMINIO/mod/hybridteaching/vc/teams/classes/teamsaccess.php

2. Asignar permisos de aplicación:
    Application.Read.All
    Calendars.Read
    Aplicación
    Calendars.ReadWrite
    OnlineMeetingArtifact.Read.All
    OnlineMeetingRecording.Read.All
    OnlineMeetings.Read.All
    OnlineMeetings.ReadWrite.All
    OnlineMeetingTranscript.Read.All
    User.Read.All
    User.ReadWrite.All

3. Asignar permisos delegados:
    OnlineMeetings.ReadWrite -> delegada
    OnlineMeetingRecording.Read.All -> delegada


** comprobar el permiso de OnlineMeetingTranscript si debe añadirse también como delegado.