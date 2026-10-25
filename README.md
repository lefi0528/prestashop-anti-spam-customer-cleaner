# SpamCustomerCleaner : supprimer les faux comptes clients PrestaShop (édition gratuite)

[![PrestaShop](https://img.shields.io/badge/PrestaShop-1.7%20%7C%208%20%7C%209-blue.svg)](https://shop.genisoft.fr/)
[![PHP](https://img.shields.io/badge/PHP-5.6%20--%208.3-777BB4.svg)](https://shop.genisoft.fr/)
[![Licence](https://img.shields.io/badge/Licence-gratuite%20(50%20suppressions)-brightgreen.svg)](LICENSE)

Module PrestaShop qui détecte les faux comptes clients créés par des robots (noms illisibles, liens dans les noms, e-mails jetables) et les supprime avec leurs adresses et leurs paniers vides. Les clients qui ont déjà passé commande ne sont jamais détectés ni supprimés.

*English: PrestaShop module that detects and removes fake customer accounts created by bots. Customers with at least one order are never flagged or deleted. This free edition deletes up to 50 accounts; the unlimited [SpamCustomerCleaner PRO edition](https://shop.genisoft.fr/7-supprimer-faux-comptes-clients-prestashop-spam-customer-cleaner.html) is sold on shop.genisoft.fr.*

## Ce que fait l'édition gratuite

- Détection sur 10 critères, avec le motif affiché pour chaque compte signalé
- Scan en lecture seule : vous voyez la liste avant toute suppression et décochez ce que vous gardez
- Sauvegarde CSV de la liste avant suppression
- Liste blanche (e-mails, noms, domaines protégés)
- Blocage en direct des inscriptions de robots (option, désactivable)
- Suppression des comptes signalés, de leurs adresses et de leurs paniers : **jusqu'à 50 comptes** au total
- Analyse des messages publicitaires du formulaire de contact (lecture seule)
- Interface en français et en anglais

## Édition PRO (payante, sur la boutique)

| | Gratuite | PRO |
|---|:---:|:---:|
| Détection (10 critères), scan, CSV, liste blanche, blocage à l'inscription | oui | oui |
| Suppression de comptes | 50 au total | illimitée |
| Nettoyage des messages de contact | analyse seule | oui |
| Pilote automatique par cron | non | oui |
| Support et mises à jour par e-mail | non | oui |

**[SpamCustomerCleaner PRO : nettoyeur de faux comptes clients PrestaShop, 19 €](https://shop.genisoft.fr/7-supprimer-faux-comptes-clients-prestashop-spam-customer-cleaner.html)** : suppression illimitée, nettoyage des messages de contact, pilote automatique par cron. Vous recevez le module PRO et une clé de licence liée au nom de domaine de votre boutique. Elle s'installe par-dessus l'édition gratuite, vos réglages sont conservés.

## Installation

1. Téléchargez le zip (onglet *Releases*) ou le dépôt compressé.
2. Back-office : **Modules > Gestionnaire de modules > Ajouter un module**, choisissez le zip.
3. Ouvrez **Clients > Scanner Anti-Spam** et cliquez sur *Lancer le Scan Complet*.

Compatibilité : PrestaShop 1.7.0 à 9.x, PHP 5.6 à 8.3.

## Avant de supprimer

- **La suppression est définitive.** Faites une sauvegarde complète de la base de données avant tout nettoyage.
- La détection est heuristique : un nom très inhabituel peut être signalé à tort. Contrôlez la liste, utilisez la liste blanche.

## Licence

Gratuit pour vos propres boutiques, partage autorisé sans modification. Voir [LICENSE](LICENSE).

## Auteur

[Genisoft Web](https://shop.genisoft.fr/) · [Version PRO](https://shop.genisoft.fr/7-supprimer-faux-comptes-clients-prestashop-spam-customer-cleaner.html) · contact@genisoft.fr
