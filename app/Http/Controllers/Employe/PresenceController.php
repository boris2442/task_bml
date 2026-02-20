<?php

namespace App\Http\Controllers\Employe;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArriveeRequest;
use App\Http\Requests\DepartRequest;
use App\Models\Justificatif;
use App\Models\Presence;
use Carbon\Carbon;

use Inertia\Inertia;

class PresenceController extends Controller
{
    /**
     * Afficher la page de signalement d'arrivée
     */
    public function createArrivee()
    {
        $user = auth()->user();
        // dd($user);

        // Vérifier si l'utilisateur a déjà une présence aujourd'hui
        $presenceToday = Presence::where('user_id', $user->id)
            ->whereDate('date_presence', Carbon::today())
            ->first();

        if ($presenceToday) {
            return redirect()->route('dashboard')
                ->with('error', 'Vous avez déjà signalé votre arrivée aujourd\'hui');
        }

        return Inertia::render('employe/Arrivee');
    }

    /**
     * Enregistrer l'arrivée
     */
    public function storeArrivee(ArriveeRequest $request)
    {

        $user = auth()->user();

        // Vérifier une dernière fois
        $existingPresence = Presence::where('user_id', $user->id)
            ->whereDate('date_presence', Carbon::today())
            ->first();

        if ($existingPresence) {
            return back()->with('error', 'Vous avez déjà signalé votre arrivée');
        }

        // Créer la présence
        $presence = Presence::create([
            'user_id' => $user->id,
            'date_presence' => Carbon::today(),
            'heure_arrivee' => Carbon::now(),
            'description' => $request->description,
            'statut' => 'arrivee_en_attente'
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Arrivée signalée avec succès. En attente d\'approbation.');
    }

    /**
     * Afficher la page de départ
     */
    public function createDepart()
    {
        $user = auth()->user();

        // Récupérer la présence du jour
        $presence = Presence::where('user_id', $user->id)
            ->whereDate('date_presence', Carbon::today())
            ->whereIn('statut', ['arrivee_approuvee', 'arrivee_en_attente'])
            ->first();

        if (!$presence) {
            return redirect()->route('dashboard')
                ->with('error', 'Vous n\'avez pas de présence active aujourd\'hui');
        }

        if ($presence->heure_depart) {
            return redirect()->route('dashboard')
                ->with('error', 'Vous avez déjà signalé votre départ');
        }

        return Inertia::render('employe/Depart', [
            'presence' => $presence
        ]);
    }

    /**
     * Enregistrer le départ et les justificatifs
     */
    public function storeDepart(DepartRequest $request, Presence $presence)
    {
        // Vérifier que la présence appartient bien à l'utilisateur
        // $this->authorize('update', $presence);

        if ($presence->heure_depart) {
            return back()->with('error', 'Départ déjà signalé');
        }

        // Mettre à jour la présence
        $presence->update([
            'heure_depart' => Carbon::now(),
            'description' => $request->description,
            'statut' => 'depart_en_attente'
        ]);

        // Upload des justificatifs
        foreach ($request->file('justificatifs') as $file) {
            $path = $file->store('justificatifs/' . $presence->id, 'public');

            Justificatif::create([
                'presence_id' => $presence->id,
                'nom_fichier' => $file->getClientOriginalName(),
                'chemin_fichier' => $path,
                'type_fichier' => $file->getMimeType(),
                'taille' => $file->getSize(),
            ]);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Départ signalé. En attente de validation.');
    }

    /**
     * Historique des présences de l'employé
     */
    public function historique()
    {
        $user = auth()->user();

        $presences = Presence::where('user_id', $user->id)
            ->with('justificatifs')
            ->orderBy('date_presence', 'desc')
            ->paginate(15);

        return Inertia::render('employe/Historique', [
            'presences' => $presences
        ]);
    }
}
