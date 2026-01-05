# Vesuvio Scootershop

## Git

https://github.com/MihailNaumoski/scooter_winkel_php

## Setup

1. Importeer `database_complete.sql` in phpMyAdmin
2. Pas `config.php` aan met je database gegevens
3. Start server: `php -S localhost:8080`

## Inloggen

| User | Wachtwoord | Rol |
|------|------------|-----|
| jan | password123 | Management |
| piet | password123 | Magazijn |
| marie | password123 | Verzending |

## Database uitbreiding

De originele opdracht had alleen `orders`, `parts` en `orderrules`.

Ik heb `personnel` en `personnel_roles` toegevoegd omdat:
- Onderdeel E vraagt om personeel met login
- Onderdeel E zegt "een of meerdere rollen per persoon"
- Met 1 `role` kolom kan je maar 1 rol opslaan
- Daarom aparte `personnel_roles` tabel voor meerdere rollen
