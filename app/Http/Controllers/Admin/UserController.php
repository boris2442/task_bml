<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Presence;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Afficher la liste de tous les utilisateurs avec leurs heures travaillées
     */
    public function index(Request $request)
    {
        $query = User::query()
            ->orderBy('name');

        // Recherche par nom, email ou matricule
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('matricule', 'like', "%$search%");
            });
        }

        // Filtrer par rôle
        if ($request->role) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate(15);

        // Ajouter les heures totales pour chaque utilisateur
        $users->getCollection()->transform(function ($user) {
            $user->total_heures = $this->calculateUserHours($user->id);
            return $user;
        });

        return Inertia::render('admin/Users', [
            'users' => $users,
            'filters' => $request->only(['search', 'role']),
        ]);
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(User $user)
    {
        $totalHeures = $this->calculateUserHours($user->id);

        return Inertia::render('admin/UserEdit', [
            'user' => $user,
            'totalHeures' => $totalHeures,
        ]);
    }

    /**
     * Mettre à jour l'utilisateur
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,employe',
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur modifié avec succès');
    }

    /**
     * Supprimer un utilisateur
     */
    public function destroy(User $user)
    {
        // Vérifier qu'on ne supprime pas le dernier admin
        if ($user->role === 'admin' && User::where('role', 'admin')->count() === 1) {
            return back()->with('error', 'Impossible de supprimer le dernier administrateur');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé avec succès');
    }

    /**
     * Calculer les heures totales travaillées (validées et approuvées)
     */
    private function calculateUserHours($userId): float
    {
        $presences = Presence::where('user_id', $userId)
            ->where('statut', 'validee')
            ->get();

        $totalHeures = 0;
        foreach ($presences as $presence) {
            if ($presence->heure_depart) {
                $totalHeures += $presence->heure_depart->diffInHours($presence->heure_arrivee);
            }
        }

        return $totalHeures;
    }
}
