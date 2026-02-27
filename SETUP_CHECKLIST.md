# ✅ CHECKLIST - MISE EN PLACE DU SYSTÈME

## 1️⃣ EXÉCUTER LES MIGRATIONS

```bash
cd c:\laragon\www\task_gestion_bml

# Exécuter toutes les migrations
php artisan migrate

# OU si vous voulez revenir en arrière
php artisan migrate:rollback
```

Cela va créer:

- ✅ Table `holidays` (jours fériés)
- ✅ Table `work_schedules` (horaires par employé)
- ✅ Table `work_alerts` (alertes)
- ✅ Colonnes dans table `presences`: `heures_travaillees`, `heures_supplementaires`

---

## 2️⃣ EXÉCUTER LES SEEDERS (OPTIONNEL MAIS RECOMMANDÉ)

```bash
php artisan db:seed --class=HolidaySeeder
php artisan db:seed --class=WorkScheduleSeeder
```

Cela va:

- ✅ Initialiser les dimanches comme jours non-travaillés
- ✅ Créer horaires par défaut (4h/jour, Lun-Ven) pour chaque employé existant

---

## 3️⃣ VÉRIFIER LE SYSTÈME

### Tests en base de données

```bash
# Voir les migrations exécutées
php artisan migrate:status

# Voir les tables créées
# Dans phpMyAdmin ou DB client: vérifez presence table a colonnes heures_*
```

### Tests en ligne

1. **Admin Dashboard**

    ```
    http://localhost/admin/dashboard
    ```

    - Voir KPIs, employés en retard, top productifs

2. **Admin Rapports**

    ```
    http://localhost/admin/rapports
    ```

    - Voir tous employés avec stats

3. **Employé Dashboard**
    ```
    http://localhost/employe/dashboard
    ```

    - Voir stats personnelles

---

## 4️⃣ DONNÉES DE TEST

### Créer une présence de test avec heures supp

```php
// Artisan tinker
php artisan tinker

// Dans tinker:
$user = App\Models\User::where('role', 'employe')->first();
$presence = App\Models\Presence::create([
    'user_id' => $user->id,
    'date_presence' => now()->toDateString(),
    'heure_arrivee' => now()->setHours(8, 0),
    'heure_depart' => now()->setHours(14, 30), // 6.5 heures
    'statut' => 'validee',
    'description' => 'Test avec heures supp',
]);

// Verifier:
$presence->heures_travaillees  // Devrait être 4.0
$presence->heures_supplementaires  // Devrait être 2.5
exit
```

---

## 5️⃣ COMMANDES UTILES

```bash
# Voir logs
tail -f storage/logs/laravel.log

# Nettoyer cache
php artisan cache:clear
php artisan view:clear

# Réinitialiser complètement (ATTENTION!)
php artisan migrate:fresh --seed

# Vider jobs queue
php artisan queue:flush

# Test unitaires (quand prêt)
php artisan test
```

---

## 6️⃣ PROBLÈMES COURANTS

### "Class not found: WorkHoursCalculationService"

→ Vérifier que le fichier existe: `app/Services/WorkHoursCalculationService.php`
→ Faire `php artisan config:clear` + `php artisan cache:clear`

### "Table 'holidays' doesn't exist"

→ Exécuter: `php artisan migrate`

### Heures non calculées

→ Vérifier que `Presence` model a le `boot()` method
→ Vérifier que presences ont `heure_depart` not null

### Stats à 0

→ Vérifier qu'il y a des presences avec `statut = 'validee'`
→ Vérifier que les presences ont les 2 heures (arrivée et départ)

---

## 7️⃣ STRUCTURE DE FICHIERS

```
app/
├── Http/Controllers/Admin/
│   ├── DashboardController.php  ✅ NOUVEAU
│   ├── UserController.php       ✅ EXISTANT
│   └── ApprobationController.php ✅ EXISTANT
│
├── Models/
│   ├── User.php                 ✅ MIS À JOUR (+ workSchedule relation)
│   ├── Presence.php             ✅ MIS À JOUR (+ calcul auto heures)
│   ├── Holiday.php              ✅ NOUVEAU
│   ├── WorkSchedule.php         ✅ NOUVEAU
│   └── WorkAlert.php            ✅ NOUVEAU
│
└── Services/
    └── WorkHoursCalculationService.php  ✅ NOUVEAU

database/
├── migrations/
│   ├── 2026_02_27_120000_add_heures_columns_to_presences_table.php  ✅
│   ├── 2026_02_27_120100_create_holidays_table.php                  ✅
│   ├── 2026_02_27_120200_create_work_schedules_table.php             ✅
│   └── 2026_02_27_120300_create_work_alerts_table.php                ✅
│
└── seeders/
    ├── HolidaySeeder.php        ✅ NOUVEAU
    └── WorkScheduleSeeder.php   ✅ NOUVEAU

resources/js/pages/
├── admin/
│   ├── Dashboard.vue            ✅ NOUVEAU
│   ├── Rapports.vue             ✅ NOUVEAU
│   ├── DetailEmploye.vue        ✅ NOUVEAU
│   └── Users.vue                ✅ EXISTANT
│
└── employe/
    └── DashboardEmploye.vue     ✅ MIS À JOUR

routes/
└── web.php                       ✅ MIS À JOUR (+ routes dashboard admin)
```

---

## 📊 DIAGRAMME FLUX DONNÉES

```
User (date_inscription)
  ↓
WorkSchedule (4h/jour, Lun-Ven)
  ↓
Workdays Calculation
  ↓ Calcule depuis date_inscription
  ↓ Exclut dimanches + Holidays
Heures Attendues
  ↓
  ├─→ Admin Dashboard (voit alertes < 80%)
  ├─→ Admin Rapports (voit tous employés)
  └─→ Employé Dashboard (voit sa progression)

Presence (heure_arrivee, heure_depart)
  ↓ Boot::saving calcule auto
  ↓ heures_travaillees = min(diff, 4)
  ↓ heures_supplementaires = max(0, diff - 4)
  ↓
  ├ Stat = sum(heures_travaillees) où statut = 'validee'
  ├ Supp = sum(heures_supplementaires) où statut = 'validee'
  └─→ Affichage partout
```

---

## ✅ VALIDATION FINALE

Avant de dire "C'est bon!":

- [ ] Migrations exécutées sans erreur
- [ ] Tables créées en DB
- [ ] `/admin/dashboard` affiche des KPIs
- [ ] `/admin/rapports` affiche des employés
- [ ] `/employe/dashboard` affiche stats
- [ ] Au moins 1 presence avec statut 'validee' existe
- [ ] Heures travaillées calculées correctement
- [ ] Heures supp détectées si > 4h
- [ ] Admin peut filtrer employés en retard
- [ ] Employé peut voir sa progression

---

**READY?** 🚀 C'est bon d'aller en prod!
