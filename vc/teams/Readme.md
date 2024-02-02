

** Crear en azure una nueva App

Obtener: idcliente, idtenant (inquilino), secretclient.

1. Añadir URI de redirección:
https://NOMBRE DEL DOMINIO/mod/hybridteaching/vc/teams/classes/teamsaccess.php
https://NOMBRE DEL DOMINIO/mod/hybridteaching/vc/teams/classes/teamsaccessapp.php


2. Asignar permisos de aplicación, si se van a crear configuraciones para aplicación:
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
    Chat.ReadBasic.All

3. Asignar permisos delegados, si se van a crear configuraciones en nombre de un usuario (behalf user):
    OnlineMeetings.ReadWrite
    OnlineMeetingRecording.Read.All
    Calendars.ReadWrite
    offline_access
    User.Read
    VirtualEvent.Read
    Chat.ReadBasic

4. Si se crea la configuración para la conexión a Teams con permisos de aplicación, crear políticas de acceso a la aplicación. Se puede seguir estas documentaciones oficiales de Teams:
CONFIGURACION DE AZURE CON POWERSHELL:    
    https://learn.microsoft.com/en-us/graph/cloud-communication-online-meeting-application-access-policy#configure-application-access-policy

    https://learn.microsoft.com/en-us/microsoftteams/teams-powershell-install    

Directivas a poder utilizar:
1. Install-Module -Name PowerShellGet -Force -AllowClobber
2. Install-Module -Name MicrosoftTeams -Force -AllowClobber
3. Connect-MicrosoftTeams
4. New-CsApplicationAccessPolicy -Identity hybridteaching -AppIds "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxx528de"
5. Grant-CsApplicationAccessPolicy -PolicyName hybridteaching -Identity "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxx3ef3"
6. Grant-CsApplicationAccessPolicy -PolicyName hybridteaching -Global
7. Se puede también configurar la poítica de vida útil del token de acceso (no puede exceder de 24 h, por defecto es de 3599 segs)
