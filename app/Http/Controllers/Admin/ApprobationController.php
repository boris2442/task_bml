<?php
// app/Http/Controllers/Admin/ApprobationController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presence;
use App\Models\User;
use App\Models\Approbation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ApprobationController extends Controller
{
    /**
     * Afficher la liste des présences à approuver
     */
    public function index(Request $request)
    {
        $query = Presence::with(['user', 'justificatifs'])
            ->whereIn('statut', ['arrivee_en_attente', 'depart_en_attente'])
            ->orderBy('date_presence', 'desc')
            ->orderBy('heure_arrivee', 'desc');

        // Filtrer par type
        if ($request->type === 'arrivee') {
            $query->where('statut', 'arrivee_en_attente');
        } elseif ($request->type === 'depart') {
            $query->where('statut', 'depart_en_attente');
        }

        // Filtrer par employé
        if ($request->employe_id) {
            $query->where('user_id', $request->employe_id);
        }

        // Filtrer par date
        if ($request->date) {
            $query->whereDate('date_presence', $request->date);
        }

        $presences = $query->paginate(15);

        // Compter les totaux par statut
        $stats = [
            'arrivees_attente' => Presence::where('statut', 'arrivee_en_attente')->count(),
            'departs_attente' => Presence::where('statut', 'depart_en_attente')->count(),
            'total_attente' => Presence::whereIn('statut', ['arrivee_en_attente', 'depart_en_attente'])->count(),
        ];

        // Liste des employés pour le filtre
        $employes = User::where('role', 'employe')
            ->select('id', 'name',  'matricule')
            ->orderBy('name')
            ->get();

        return Inertia::render('Approbations', [
            'presences' => $presences,
            'stats' => $stats,
            'employes' => $employes,
            'filters' => $request->only(['type', 'employe_id', 'date']),
        ]);
    }

    /**
     * Approuver une arrivée
     */
    public function approuverArrivee(Presence $presence)
    {
        if ($presence->statut !== 'arrivee_en_attente') {
            return back()->with('error', 'Cette présence ne peut pas être approuvée');
        }

        $presence->update([
            'statut' => 'arrivee_approuvee'
        ]);

        // Enregistrer l'approbation
        Approbation::create([
            'presence_id' => $presence->id,
            'admin_id' => Auth::id(),
            'type_approbation' => 'arrivee',
            'statut_approbation' => 'approuve',
        ]);

        return back()->with('success', 'Arrivée approuvée avec succès');
    }

    /**
     * Approuver un départ + justificatifs
     */
    public function approuverDepart(Presence $presence)
    {
        if ($presence->statut !== 'depart_en_attente') {
            return back()->with('error', 'Ce départ ne peut pas être approuvé');
        }

        $presence->update([
            'statut' => 'validee'
        ]);

        Approbation::create([
            'presence_id' => $presence->id,
            'admin_id' => Auth::id(),
            'type_approbation' => 'depart_justificatifs',
            'statut_approbation' => 'approuve',
        ]);

        return back()->with('success', 'Départ validé avec succès');
    }

    /**
     * Rejeter une présence (avec motif)
     */
    public function rejeter(Request $request, Presence $presence)
    {
        $request->validate([
            'motif' => 'required|string|min:10',
        ]);

        $presence->update([
            'statut' => 'rejetee'
        ]);

        Approbation::create([
            'presence_id' => $presence->id,
            'admin_id' => Auth::id(),
            'type_approbation' => $presence->statut === 'arrivee_en_attente' ? 'arrivee' : 'depart_justificatifs',
            'statut_approbation' => 'rejete',
            'commentaire' => $request->motif,
        ]);

        return back()->with('success', 'Présence rejetée');
    }

    /**
     * Approbation en lot
     */
    public function approbationLot(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:presences,id',
            'action' => 'required|in:approuver_arrivee,approuver_depart,rejeter',
            'motif' => 'required_if:action,rejeter|string|nullable',
        ]);

        $count = 0;
        foreach ($request->ids as $id) {
            $presence = Presence::find($id);

            if ($request->action === 'approuver_arrivee' && $presence->statut === 'arrivee_en_attente') {
                $presence->update(['statut' => 'arrivee_approuvee']);
                $count++;
            } elseif ($request->action === 'approuver_depart' && $presence->statut === 'depart_en_attente') {
                $presence->update(['statut' => 'validee']);
                $count++;
            } elseif ($request->action === 'rejeter') {
                $presence->update(['statut' => 'rejetee']);
                $count++;
            }
        }

        return back()->with('success', "$count présence(s) traitées avec succès");
    }

    /**
     * Voir les détails d'une présence
     */
    public function show(Presence $presence)
    {
        $presence->load(['user', 'justificatifs', 'approbations.admin']);

        return Inertia::render('admin/PresenceDetail', [
            'presence' => $presence
        ]);
    }

    /**
     * Télécharger un justificatif de manière sécurisée
     */
    public function telechargerJustificatif(Presence $presence, $justificatifId)
    {
        // Vérifier que le justificatif appartient bien à cette présence
        $justificatif = $presence->justificatifs()->where('id', $justificatifId)->first();

        if (!$justificatif) {
            abort(404, 'Justificatif non trouvé');
        }

        $filePath = storage_path('app/public/' . $justificatif->chemin_fichier);

        if (!file_exists($filePath)) {
            abort(404, 'Fichier non trouvé');
        }

        return response()->download($filePath, $justificatif->nom_fichier);
    }
}
