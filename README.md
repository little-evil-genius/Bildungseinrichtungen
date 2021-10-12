# Bildungseinrichtungen
Dieses Plugin erweitert das Board um eine interaktive Liste von Bildungseinrichtungen. Ausgewählte Usergruppen können neue Bildungseinrichtungen hinzufügen und beitreten. Um eine Bildungseinrichtung hinzufügen zu können, wird ein aussagekräftiger Titel, eine Einordnung in eine Stadt, eine Beschreibung und eine Einordnung in eine Kategorie benötigt. Bildungseinrichtungen müssen vom Team erst freigeschaltet werden im Mod-CP. Eingereichte Bildungseinrichtungen vom Team werden automatisch freigeschaltet. Nach der Freischaltung können User sich mit ihren Accounts in eine Bildungseinrichtungen eintragen. Dabei können sie die Klasse bzw. Studiengang ihres Charakters angeben. Ersteller der Bildungseinrichtungen und das Team können diese bearbeiten und löschen. Zusätzlich werden die Eintragungen in eine Bildungseinrichtungen im Profil angezeigt.

# Datenbank-Änderungen
Hinzugefügte Tabellen:
- PRÄFIX_education
- PRÄFIX_educations_user

# Neue Templates
- education	
- education_add	
- education_bit	
- education_bit_users	
- education_edit	
- education_filter	
- education_join	
- education_memberprofile
- education_modcp	
- education_modcp_bit	
- education_modcp_nav

# Template Änderungen - neue Variablen
- member_profile - {$education_memberprofile}
- header - {$new_education_alert}
- modcp_nav_users - {$nav_education}

# ACP-Einstellungen - Bildungseinrichtungen
- Erlaubte Gruppen Hinzufügen
- Erlaubte Gruppen Beitreten
- Schulart
- Städte
- Multipage-Navigation
- Multipage-Navigation
- Anzahl der Bilungseinrichtigung (Multipage-Navigation)
- Listen PHP (Navigation Ergänzung)

# Links
- euerforum.de/misc.php?action=education
- euerforum.de/modcp.php?action=education

# Demo
Bildungseinrichtungen-Übersicht
  <img src="https://www.bilder-hochladen.net/files/big/m4bn-9m-c00c.png" />
  
Maske beim Hinzufügen
  <img src="https://www.bilder-hochladen.net/files/big/m4bn-9n-78ba.png" />
  
Team-Alert auf dem Index
  <img src="https://www.bilder-hochladen.net/files/m4bn-9s-5538.png" />
  
Mod-CP
  <img src="https://www.bilder-hochladen.net/files/big/m4bn-9r-9963.png" />
  
Bildungseinrichtung beitreten
  <img src="https://www.bilder-hochladen.net/files/m4bn-9q-25ce.png" />
  
Bildungseinrichtung bearbeiten
  <img src="https://www.bilder-hochladen.net/files/big/m4bn-9p-6d4b.png" />

Alerts Beanachrichtigung

  <img src="https://www.bilder-hochladen.net/files/m4bn-9o-7698.png" />
