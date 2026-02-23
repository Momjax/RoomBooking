# 📋 RoomBooking - Kanban Notion

> Copier-coller dans une base de données Notion avec les colonnes : **À faire | En cours | Terminé**
> Propriétés suggérées : Phase, Priorité, Jour prévu, Jalon

---

## 🟣 Phase 0 — Diagrammes & Conception

| Tâche | Jalon | Jour | Priorité |
|---|---|---|---|
| Diagramme UML Use Case | Phase 0 | Dim 23 | 🔴 Haute |
| MCD / MLD (Modèle de données) | Phase 0 | Dim 23 | 🔴 Haute |
| Gantt + Kanban (gestion de projet) | Phase 0 | Dim 23 | 🔴 Haute |
| Choix technologiques documentés | Phase 0 | Dim 23 | 🟡 Moyenne |

---

## 🔵 Phase 1 — Initialisation technique (Dim 23 Fév)

| Tâche | Jalon | Jour | Priorité |
|---|---|---|---|
| Créer le projet Symfony (`symfony new`) | J1 | Dim 23 | 🔴 Haute |
| Installer bundles : doctrine, security, twig, maker | J1 | Dim 23 | 🔴 Haute |
| Installer et configurer Tailwind CSS | J1 | Dim 23 | 🔴 Haute |
| Configurer MySQL dans `.env` | J2 | Dim 23 | 🔴 Haute |
| Tester la connexion BDD | J2 | Dim 23 | 🔴 Haute |
| Créer entité `User` (email, mdp, rôle, nom, prénom) | J3 | Dim 23 | 🔴 Haute |
| Créer entité `Room` (nom, capacité, description) | J3 | Dim 23 | 🔴 Haute |
| Créer entité `Equipement` (nom) | J3 | Dim 23 | 🟡 Moyenne |
| Créer entité `Classe` (nom, année) | J3 | Dim 23 | 🔴 Haute |
| Créer entité `Reservation` (date, heure, statut) | J3 | Dim 23 | 🔴 Haute |
| Configurer relations Doctrine (ManyToOne, etc.) | J3 | Dim 23 | 🔴 Haute |
| Init Git + `.gitignore` + branche `main` | J4 | Dim 23 | 🟡 Moyenne |
| Générer et exécuter les migrations | J5 | Dim 23 | 🔴 Haute |
| Créer les fixtures (users, salles, réservations test) | J5 | Dim 23 | 🟡 Moyenne |

---

## 🟢 Phase 2 — Authentification & Sécurité (Lun 24 Fév)

| Tâche | Jalon | Jour | Priorité |
|---|---|---|---|
| Configurer le security.yaml (provider, firewall) | J6 | Lun 24 | 🔴 Haute |
| Créer le formulaire de login (Twig) | J6 | Lun 24 | 🔴 Haute |
| Implémenter le logout | J6 | Lun 24 | 🔴 Haute |
| Configurer `access_control` par rôle | J7 | Lun 24 | 🔴 Haute |
| Définir les rôles : ROLE_ADMIN, ROLE_PROF, ROLE_USER | J7 | Lun 24 | 🔴 Haute |
| Restreindre les routes admin (/admin/*) | J7 | Lun 24 | 🔴 Haute |
| Activer la protection CSRF sur les formulaires | J8 | Lun 24 | 🟡 Moyenne |
| Validation des inputs (Assert Symfony) | J8 | Lun 24 | 🟡 Moyenne |
| Protection XSS (escape Twig auto) | J8 | Lun 24 | 🟡 Moyenne |

---

## 🟡 Phase 3 — Fonctions principales (Mar 25 Fév)

| Tâche | Jalon | Jour | Priorité |
|---|---|---|---|
| CRUD Salles (créer, lister, modifier, supprimer) | J9 | Mar 25 | 🔴 Haute |
| CRUD Classes (créer, lister, détail, supprimer) | J9 | Mar 25 | 🔴 Haute |
| CRUD Utilisateurs (créer étudiant, prof, supprimer) | J9 | Mar 25 | 🔴 Haute |
| Reset mot de passe (admin → étudiant/prof) | J9 | Mar 25 | 🟡 Moyenne |
| Ajouter/retirer étudiant d'une classe | J9 | Mar 25 | 🟡 Moyenne |
| Formulaire de réservation (date + créneau) | J10 | Mar 25 | 🔴 Haute |
| Vérification des disponibilités avant réservation | J10 | Mar 25 | 🔴 Haute |
| Empêcher les doubles réservations (même salle/créneau) | J10 | Mar 25 | 🔴 Haute |
| Page accueil Twig | J11 | Mar 25 | 🟡 Moyenne |
| Page dashboard Twig | J11 | Mar 25 | 🔴 Haute |
| Page profil utilisateur Twig | J11 | Mar 25 | 🟡 Moyenne |
| Flash messages (succès, erreur, info) | J12 | Mar 25 | 🟡 Moyenne |

---

## 🟠 Phase 4 — Interface & UX (Mer 26 Fév)

| Tâche | Jalon | Jour | Priorité |
|---|---|---|---|
| Intégration Tailwind CSS sur toutes les vues | J13 | Mer 26 | 🔴 Haute |
| Layout responsive (mobile + desktop) | J13 | Mer 26 | 🔴 Haute |
| Design de la navbar selon le rôle | J13 | Mer 26 | 🟡 Moyenne |
| Calendrier interactif JS (vue semaine/jour) | J14 | Mer 26 | 🔴 Haute |
| Sélecteur de créneaux horaires JS | J14 | Mer 26 | 🔴 Haute |
| Validation côté client (JS) | J14 | Mer 26 | 🟡 Moyenne |
| Dashboard : liste des réservations de l'utilisateur | J15 | Mer 26 | 🔴 Haute |
| Dashboard : bouton annuler réservation | J15 | Mer 26 | 🔴 Haute |
| Dashboard : éditer si permis | J15 | Mer 26 | 🟡 Moyenne |

---

## 🔴 Phase 5 — Tests & Qualité (Jeu 27 Fév)

| Tâche | Jalon | Jour | Priorité |
|---|---|---|---|
| Tests fonctionnels : parcours login | J16 | Jeu 27 | 🟡 Moyenne |
| Tests fonctionnels : réserver + annuler | J16 | Jeu 27 | 🟡 Moyenne |
| Tests fonctionnels : visualiser disponibilités | J16 | Jeu 27 | 🟡 Moyenne |
| Génération swagger.json / swagger.yaml | J16 | Jeu 27 | 🟢 Basse |
| Config staging (APP_ENV, logs, variables) | J17 | Jeu 27 | 🟡 Moyenne |

---

## ⚫ Phase 6 — Déploiement (Post semaine / Bonus)

| Tâche | Jalon | Jour | Priorité |
|---|---|---|---|
| Config environnement production | J18 | Post | 🟢 Basse |
| Déploiement (`composer install --no-dev`, migrations) | J19 | Post | 🟢 Basse |
| Config serveur web (Apache/Nginx) | J19 | Post | 🟢 Basse |
| Recette finale bout en bout | J20 | Post | 🟢 Basse |
| Monitoring, logs, sauvegardes | J20 | Post | 🟢 Basse |

---

## 📝 Livrables à ne pas oublier

| Livrable | Statut |
|---|---|
| Cahier des charges / Expression du besoin | ⬜ |
| MCD / MLD + Dictionnaire de données | ⬜ |
| Code source sur Git | ⬜ |
| Dump SQL de la BDD | ⬜ |
| Documentation d'installation | ⬜ |
| Guide utilisateur | ⬜ |
| Cahier de recette (tests) | ⬜ |
| Users + MDP de démo | ⬜ |
| Bilan de projet | ⬜ |
