

** Crear en azure una nueva App

Obtener: idcliente, idtenant (inquilino), secretclient.

1. Añadir URI de redirección:
https://NOMBRE DEL DOMINIO/mod/hybridteaching/vc/teams/classes/teamsaccess.php

2. Asignar permisos de aplicación:
    Application.Read.All
    Calendars.Read
    Calendars.ReadWrite
    OnlineMeetingArtifact.Read.All
    OnlineMeetingRecording.Read.All
    OnlineMeetings.Read.All
    OnlineMeetings.ReadWrite.All
    OnlineMeetingTranscript.Read.All
    User.Read.All
    User.ReadWrite.All
    VirtualEvent.Read.All

3. Asignar permisos delegados:
    OnlineMeetings.ReadWrite
    OnlineMeetingRecording.Read.All
    Calendars.ReadWrite
    offline_access
    User.Read
    VirtualEvent.Read
