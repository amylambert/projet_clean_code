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

| #   | Problème | Catégorie | Impact |
| --- | -------- | --------- | ------ |
| 1   |          |           |        |
| 2   |          |           |        |
| 3   |          |           |        |
| 4   |          |           |        |
| 5   |          |           |        |
| 6   |          |           |        |

## 3. Nos trois priorités

1. Déplacement des gardes fous actuels pour rendre les fichiers indépendants
2. Ajout de gardes fous manquant pour éviter les crash
3. Séparation de BookingService.php pour séparer les responsabilités

## 4. Risques avant refactoring

À compléter.
