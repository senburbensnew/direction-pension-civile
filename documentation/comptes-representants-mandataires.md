# Comptes représentants légaux et mandataires

Guide opérationnel pour les agents (formalités, admin) et référence technique. Complète [`demande-creation-compte.md`](demande-creation-compte.md).

## Notions métier

| Profil | Relation (`user_beneficiaire_links.relation`) | Compte utilisateur |
|--------|-----------------------------------------------|--------------------|
| Titulaire adulte | `self` | Le pensionné |
| Représentant d’un mineur | `pere`, `mere`, `tuteur` | Le représentant (pas le mineur) |
| Mandataire | `mandataire` | La personne mandatée, rattachée au dossier du pensionné |

L’accès au portail pension (`User::hasPensionPortalAccess()`) est accordé si le compte est **actif** (`account_status = actif`, toute catégorie : pensionné, fonctionnaire, institution, externe, etc.), **ou** la catégorie `pensionne` (y compris provisoire), **ou** un lien bénéficiaire, **ou** au moins une demande de mandat (`pension_mandats`).

---

## 1. Représentant légal (pensionné mineur)

### Usager

1. **`GET /demande-compte`** — à l’étape documents, choisir le profil **« Représentant d’un pensionné mineur »**.
2. Fournir **acte de naissance** du mineur et **pièce d’identité du représentant**.
3. Indiquer le lien (`père`, `mère`, `tuteur`) et, si besoin, cocher **« Je suis aussi pensionné titulaire »**.
4. Le **code pension** est **facultatif** à l’inscription (complément possible avant le RDV). S’il est saisi, il doit être celui du **pensionné mineur** (concordance avec l’acte de naissance), jamais celui du représentant.
5. Soumission → **compte provisoire** (identité = représentant) → **prise de RDV** au Service Formalités (motif **« formalités et mandats »**, en ligne ou présentiel).
6. Sans RDV **réalisé**, le dossier ne peut pas être accepté par les agents formalités.

### Système

- Catégorie **`externe`** (sauf si représentant aussi pensionné → `externe` + `pensionne`).
- Création d’un **bénéficiaire mineur** et lien `pere` / `mere` / `tuteur`.
- Voir tests : `guest_can_submit_a_minor_pensionne_account_request` dans `tests/Feature/DemandeCreationCompteTest.php`.

### Agent formalités

1. Marquer le RDV **réalisé** (motif formalités / mandats).
2. **`formalites/comptes-demandes/{id}`** — accepter ou refuser (impossible sans RDV réalisé).
3. Acceptation → compte **ACTIF** ; le représentant garde son mot de passe.

---

## 2. Mandataire — ouverture de compte (parcours public)

Pour une personne qui **n’est pas** titulaire mais agit avec **procuration / désignation** pour un pensionné identifié par **code pension** :

1. **`GET /demande-compte`** — profil **« Mandataire d’un pensionné »**.
2. Pièce d’identité du **mandataire** + **formulaire de désignation signé** (PDF ou image).
3. **Code pension du pensionné mandant** (facultatif pour l’instant ; vérifié dans la base de référence s’il est saisi — pas le code du mandataire, même s’il est aussi titulaire).
4. Même enchaînement : compte **provisoire** → **RDV Service Formalités** (motif **« formalités et mandats »**) → acceptation du compte par les agents formalités.
5. Sans RDV **réalisé**, le dossier de compte ne peut pas être accepté.

À la soumission, un enregistrement **`pension_mandats`** est créé en statut **`en_attente`**, lié au dossier DCC via **`demande_creation_compte_id`** (sans lien bénéficiaire actif tant que le mandat n’est pas approuvé). Une demande parallèle via **`/compte/mandats`** pour le même code est bloquée tant que ce dossier est en cours.

### Agent formalités

1. RDV réalisé + contrôle des pièces (identité mandataire, désignation, adéquation code pension).
2. Accepter la **demande de création de compte** → compte **ACTIF**.
3. Approuver le **mandat** dans **`formalites/pension-mandats`** (ou hub **Mandats & relations**) — périmètre des demandes et codes autorisés.

---

## 3. Mandataire — compte déjà actif

Si le mandataire a déjà un compte **ACTIF** (toute catégorie : pensionné, fonctionnaire, institution, externe, etc.) :

1. **`GET /compte/mandats`** (menu utilisateur lorsque `hasPensionPortalAccess()`).
2. Soumettre une **nouvelle demande de mandat** (identité du pensionné, code, pièce justificative).
3. Agent formalités : **`formalites/pension-mandats`** — approuver / refuser / révoquer.

Le lien **`mandataire`** sur le bénéficiaire est créé **à l’approbation** du mandat (pas à la simple soumission).

---

## 4. Écrans administration

| Écran | Route | Rôle |
|-------|-------|------|
| Demandes création compte | `admin/comptes-demandes/*` ou `formalites/comptes-demandes/*` | Consultation ; **décision** réservée aux agents formalités |
| Validation mandats | `formalites/pension-mandats` (hub **Mandats & relations**) | Approbation, périmètre, refus, révocation — **agents formalités** uniquement |
| Utilisateur → liens pension | `admin/users/{user}/edit` | Ajout / retrait manuel de lien bénéficiaire (cas exceptionnels) |

---

## 5. Tests de référence

- `tests/Feature/DemandeCreationCompteTest.php` — titulaire, mineur, mandataire (DCC).
- `tests/Feature/DemandeCreationCompteManagementTest.php` — décision après RDV.
- `tests/Feature/PensionMandatWorkflowTest.php` — périmètre mandat, approbation formalités, blocage doublon DCC.
- `tests/Unit/Services/PensionMandatServiceTest.php` — enregistrement mandat lié DCC, refus, idempotence.

---

## Résumé

- **Mineur** : un seul parcours DCC ; le compte est celui du **représentant**.
- **Mandataire sans compte** : parcours DCC profil mandataire + RDV + acceptation DCC + approbation mandat.
- **Mandataire avec compte** : `/compte/mandats` puis validation par les **agents formalités**.
- **Navigation** : menus pension visibles dès que `hasPensionPortalAccess()` (pas seulement le rôle Spatie `pensionne`).
