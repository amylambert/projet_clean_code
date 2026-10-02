# Note de conception

## 1. Choix principaux

Ajout d'adapteur pour chaque moyen de paiement (./paymentAdapters)
Ajout d'une interface pour chaque adapteur de moyen de paiement (./paymentAdapters/IPaymentAdapter)
Ajout d'une classe qui gère le moyen de paiement et entamme la transaction (PaymentProcessor)
Ajout d'un Timer pour chronometrer le temps de la transaction (Timer)
Ajout de logs pour afficher le fonctionnement des transactions (PaymentProcessor)

## 2. Principes SOLID mobilisés

Pour chaque principe réellement utilisé :
- problème initial ;
- classes concernées ;
- bénéfice obtenu.

Single Responsability Principle:
- Besoin d'un timer pour chronométrer les transactions
- Timer, PaymentProcessor
- Timer réutilisable pour autres besoins de chronomètre

Open Close Principle:
- Besoin d'adapters pour adapter l'input de PaymentProcessor à chaque moyen de paiement
- PaymentProcessor, PayFastSdk, PayFastAdapter, StripeClient, StripeAdapter
- Architecture prête pour tout nouveau moyen de paiement

Interface Segregation Principle:
- Besoin de créer un guide pour les futurs ajouts de moyens de paiement et les adapteurs
- IPaymentAdapter, PayFastAdapter, StripeAdapter
- Interface permettant d'intégrer facilement des moyens de paiement compatibles avec le code actuel

## 3. Design Patterns éventuellement utilisés

Pour chaque pattern :
- problème rencontré ;
- solution retenue ;
- pourquoi une solution plus simple ne suffisait pas.

- Besoin d'envoyer un mail et un message quand une transaction est réalisée
- Observer
- Besoin de réaction suite à l'évènement de transaction réussie

- Besoin d'adapter une même donnée pour tous les moyens de paiement en données adaptées au moyen
- Adapter
- Impossibilité d'automatiser dans une boucle avec les entrées différentes de chaque moyen de paiement

- Besoin d'utiliser le bon moyen de paiement en fonction du nom
- Strategy
- Tout autre moyen rendrait l'ajout d'un nouveau moyen de paiement plus compliqué

- Besoin d'ajouter un chronomètre sans modifier PaymentProcessor
- Decorator
- Tout autre moyen serait plus compliqué ou modifierait PaymentProcessor

- Besoin d'instancier tous les observer pour après la transaction
- Simple factory
- Il serait plus compliqué de changer les observer sans le factory qui les instancient

Si aucun pattern n'est utilisé sur une partie du projet, expliquez pourquoi.

## 4. Solutions envisagées puis écartées

À compléter.

## 5. Ce que nous améliorerions avec plus de temps

Séparation et simplification de la class BookingService
Clarification de certains noms de fonction notamment "confirm" dans BookingService
Ajout de l'object Money pour les tarifs
Annulation de la réservation et remboursement
Organisation des fichier dans des dossiers
