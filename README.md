# Vesuvio Scootershop

mijn project voor het realisatie examen

## hoe te starten

- importeer database_complete.sql in phpmyadmin of mysql workbench
- check config.php of de database settings kloppen (standaard root/root)
- open terminal in de map en doe: php -S localhost:8080
- ga naar localhost:8080 in browser

## test accounts

jan / password123 - dit is management account
piet / password123 - magazijn medewerker
marie / password123 - verzending

## over de database

in de opdracht stonden alleen orders, parts en orderrules tabellen. maar voor onderdeel E had ik personeel nodig met login en rollen.

het probleem was dat er staat "een of meerdere rollen per persoon". als je gewoon een role kolom in personnel zet kan je maar 1 rol opslaan. daarom heb ik een aparte personnel_roles tabel gemaakt, zo kan iemand meerdere rollen hebben

## git repo

https://github.com/MihailNaumoski/scooter_winkel_php
