<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Medecin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ConsultationController extends Controller
{
    /**
     * Liste de toutes les consultations
     */
    public function index(int $id)
    {
        $consultations = Consultation::where("patient_id", "=", $id)->get();

        return response()->json([
            'status' => 200,
            'consultations' => $consultations
        ], 200);
    }

    /**
     * Détails d'une consultation
     */
    public function show(int $id)
    {
        $consultation = Consultation::find($id);

        if (!$consultation) {
            return response()->json([
                'status' => 404,
                'message' => 'Consultation introuvable'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'consultation' => $consultation
        ], 200);
    }

    /**
     * Enregistrer une consultation
     */
    public function store(Request $request)
    {
        $medecin = Medecin::where('id', Auth::id())->first();

        if (!$medecin) {
            return response()->json([
                'status' => 404,
                'message' => 'Médecin introuvable'
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'date_consultation' => 'required|date',
            'type_consultation' => 'required|string|max:100',
            'motif_consultation' => 'nullable|string',
            'symptomes' => 'nullable|string',

            'temperature' => 'nullable|numeric',
            'tension_arterielle' => 'nullable|string|max:20',
            'poids' => 'nullable|numeric',
            'frequence_cardiaque' => 'nullable|integer',
            'saruration_oxygene' => 'nullable|numeric',

            'examens_demandes' => 'nullable|string',
            'ordonnance' => 'nullable|file|mimes:pdf|max:5120',
            'resultats_examens' => 'nullable|file|mimes:pdf|max:5120',
            'diagnostic_principal' => 'nullable|string|max:255',
            'observations_medicales' => 'nullable|string',

            'niveau_urgence' => [
                'required',
                Rule::in(['Faible', 'Moyen', 'Elevé'])
            ],

            'prochain_rdv' => 'nullable|date',
            'localisation_dossier' => 'nullable|string|max:255'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors()
            ], 400);
        }

        $ordonnance = null;
        $resultatsExamens = null;

        if ($request->hasFile('ordonnance')) {
            $ordonnance = $request->file('ordonnance')
                ->store('consultations/ordonnances', 'public');
        }

        if ($request->hasFile('resultats_examens')) {
            $resultatsExamens = $request->file('resultats_examens')
                ->store('consultations/resultats', 'public');
        }

        Consultation::create([
            'patient_id' => $request->patient_id,
            'medecin_id' => $medecin->id,

            'date_consultation' => $request->date_consultation,
            'type_consultation' => $request->type_consultation,
            'motif_consultation' => $request->motif_consultation,
            'symptomes' => $request->symptomes,

            'temperature' => $request->temperature,
            'tension_arterielle' => $request->tension_arterielle,
            'poids' => $request->poids,
            'frequence_cardiaque' => $request->frequence_cardiaque,
            'saruration_oxygene' => $request->saruration_oxygene,

            'examens_demandes' => $request->examens_demandes,
            'resultats_examens' => $resultatsExamens,
            'diagnostic_principal' => $request->diagnostic_principal,
            'ordonnance' => $ordonnance,
            'observations_medicales' => $request->observations_medicales,

            'niveau_urgence' => $request->niveau_urgence ?? 'Moyen',
            'prochain_rdv' => $request->prochain_rdv,
            'localisation_dossier' => $request->localisation_dossier,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Consultation enregistrée'
        ], 200);
    }

    /**
     * Modifier une consultation
     */
    public function update(Request $request, int $id)
    {
        $medecin = Medecin::where('id', Auth::id())->first();

        if (!$medecin) {
            return response()->json([
                'status' => 404,
                'message' => 'Médecin introuvable'
            ], 404);
        }

        $consultation = Consultation::find($id);

        if (!$consultation) {
            return response()->json([
                'status' => 404,
                'message' => 'Consultation introuvable'
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'date_consultation' => 'required|date',
            'type_consultation' => 'required|string|max:100',
            'motif_consultation' => 'nullable|string',
            'symptomes' => 'nullable|string',

            'temperature' => 'nullable|numeric',
            'tension_arterielle' => 'nullable|string|max:20',
            'poids' => 'nullable|numeric',
            'frequence_cardiaque' => 'nullable|integer',
            'saruration_oxygene' => 'nullable|numeric',

            'examens_demandes' => 'nullable|string',
            'ordonnance' => 'nullable|file|mimes:pdf|max:5120',
            'resultats_examens' => 'nullable|file|mimes:pdf|max:5120',
            'diagnostic_principal' => 'nullable|string|max:255',
            'observations_medicales' => 'nullable|string',

            'niveau_urgence' => [
                'required',
                Rule::in(['Faible', 'Moyen', 'Elevé'])
            ],

            'prochain_rdv' => 'nullable|date',
            'localisation_dossier' => 'nullable|string|max:255'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors()
            ], 400);
        }

        // Remplacement de l'ordonnance
        if ($request->hasFile('ordonnance')) {

            if (
                $consultation->ordonnance &&
                Storage::disk('public')->exists($consultation->ordonnance)
            ) {
                Storage::disk('public')->delete($consultation->ordonnance);
            }

            $consultation->ordonnance = $request->file('ordonnance')
                ->store('consultations/ordonnances', 'public');
        }

        // Remplacement des résultats d'examens
        if ($request->hasFile('resultats_examens')) {

            if (
                $consultation->resultats_examens &&
                Storage::disk('public')->exists($consultation->resultats_examens)
            ) {
                Storage::disk('public')->delete($consultation->resultats_examens);
            }

            $consultation->resultats_examens = $request->file('resultats_examens')
                ->store('consultations/resultats', 'public');
        }

        $consultation->patient_id = $request->patient_id;
        $consultation->medecin_id = $medecin->id;

        $consultation->date_consultation = $request->date_consultation;
        $consultation->type_consultation = $request->type_consultation;
        $consultation->motif_consultation = $request->motif_consultation;
        $consultation->symptomes = $request->symptomes;

        $consultation->temperature = $request->temperature;
        $consultation->tension_arterielle = $request->tension_arterielle;
        $consultation->poids = $request->poids;
        $consultation->frequence_cardiaque = $request->frequence_cardiaque;
        $consultation->saruration_oxygene = $request->saruration_oxygene;

        $consultation->examens_demandes = $request->examens_demandes;
        $consultation->diagnostic_principal = $request->diagnostic_principal;
        $consultation->observations_medicales = $request->observations_medicales;

        $consultation->niveau_urgence = $request->niveau_urgence;
        $consultation->prochain_rdv = $request->prochain_rdv;
        $consultation->localisation_dossier = $request->localisation_dossier;

        $consultation->save();

        return response()->json([
            'status' => 200,
            'message' => 'Consultation modifiée avec succès',
            'data' => $consultation
        ], 200);
    }

    /**
     * Supprimer une consultation
     */
    public function destroy(int $id)
    {
        $consultation = Consultation::find($id);

        if (!$consultation) {
            return response()->json([
                'status' => 404,
                'message' => 'Consultation introuvable'
            ], 404);
        }

        $consultation->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Consultation supprimée'
        ], 200);
    }
}
