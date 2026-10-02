# Audit initial

## 1. Comportement observable

Résultat du lancement de l’appli :

PAYMENT stripe_143.82
SQL INSERT booking=1001 total=143.82 status=confirmed
EMAIL lea@example.com: booking 1001 confirmed
TOTAL FINAL: 143.82

1. Précise le type de paiement puis son montant total.
2. Commande SQL ajoutant une réservation dans la base de donnée via l’id de la réservation, son coût total et son statut (‘pending’ ou ‘confirmed’).
3. Gère l’envoi d’email de confirmation de la réservation en prenant en paramètre l’adresse email du client, son id de réservation et son statut.
4. Précise le coût total de la réservation

## 2. Problèmes identifiés

```
|# | Problème | Catégorie | Impact |
|---|---|---|---|
| 1 | manque de garde-fous dans les classes | testabilité | le programme peut arrêter de fonctionner en cas de mauvais inputs. |
| 2 | BookingService.php a trop de fonctionalités | responsabilité | Tout le programme s'arrête sur ce fichier a une erreur. |
| 3 | Certains garde-fous ne sont pas dans les bons fichiers | couplage | Des fichiers dépendent d'autres fichiers pour assurer un bon fonctionnement |
| 4 | Présence de valeurs magiques dans le code non expliquées | lisibilité + maintenabilité | Compléxifie les modifications de valeurs liées à des changements de règles métier |
| 5 | Manque d'interface pour les moyens de paiement | règles métier | On ne sait pas les besoin à prendre en compte pour de futurs moyens de paiement |
| 6 | certains noms de variables sont trop flous surtout dans les fichiers tests | lisibilité | Plus difficile à comprendre et maintenir pour le futur |
| 7 | On peut comfirmer plusieurs fois la même réservation | règles métier | duplication de paiements et d'entrées dans la base de données |
| 8 | Manque de messages d'erreur personalisées | testabilité + maintenanbilité | Rend la correction de bugs plus compliqué |
```

## 3. Nos trois priorités

1. Déplacement des gardes fous actuels pour rendre les fichiers indépendants
2. Ajout de gardes fous manquant pour éviter les crash
3. Séparation de BookingService.php pour séparer les responsabilités

## 4. Risques avant refactoring

- Le programme s'arrête à cause de mauvaises valeurs entrées
- Tout le programme s'arrête à cause d'une erreur dans BookingService.php
- Ne pas trouver où changer le code suite à changement de règles métier
- Incapacité à maintenir et ajouter dans le code à cause du manque de lisibilité et d'interfaces
- Incapacité à tester le code correctement avant sa mise en production.
- Toute nouvelle personne aura beaucoup de mal à comprendre la logique du code.
