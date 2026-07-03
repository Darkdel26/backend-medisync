<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    public function login(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required|email:dns,rfc',
            'mot_de_passe' => 'required',
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validation->errors()
            ], 400);
        }

        $patient = Patient::where('email', $request->email)->first();

        if (!$patient || !Hash::check($request->mot_de_passe, $patient->mot_de_passe)) {
            return response()->json([
                'status' => 401,
                'errors' => 'Les identifiants fournis sont incorrects'
            ], 401);
        }

        if ($patient->statut == 'Inactif') {
            return response()->json([
                'status' => 401,
                'errors' => 'Votre compte est inactif'
            ], 401);
        }

        $token = $patient->createToken('patient-token', ['patient'])->plainTextToken;

        return response()->json([
            'status' => 200,
            'patient' => $patient,
            'token' => $token
        ], 200);
    }

    public function logout()
    {
        $patient = Patient::where('id', Auth::id())->first();
        if ($patient) {
            $patient->tokens()->delete();
        }
    }

    public function info()
    {
        $patient = Patient::where('id', Auth::id())->first();
        if (!$patient) {
            return response()->json([
                'status' => 404,
                'message' => "Patient introuvable"
            ], 200);
        }

        return response()->json([
            'status' => 200,
            'patient' => $patient
        ], 200);
    }

    public function rdvs()
    {
        $patient = Patient::where('id', Auth::id())->first();
        if (!$patient) {
            return response()->json([
                'status' => 404,
                'message' => "Patient introuvable"
            ], 200);
        }

        $rdvs = Consultation::with([
            'medecin:id,nom,prenom,specialite'
        ])
            ->where('patient_id', $patient->id)
            ->whereNotNull('prochain_rdv')
            ->where('prochain_rdv', '>=', Carbon::now())
            ->orderBy('prochain_rdv')
            ->get([
                'id',
                'medecin_id',
                'prochain_rdv'
            ]);

        return response()->json([
            'status' => 200,
            'data' => $rdvs
        ], 200);
    }

    /**
     * Génère un matricule au format PAT-001, PAT-002...
     */
    private function genererMatricule(): string
    {
        do {
            $dernierPatient = Patient::latest('id')->first();

            $numero = $dernierPatient
                ? ((int) substr($dernierPatient->num_matricule, 4)) + 1
                : 1;

            $matricule = 'PAT-' . str_pad($numero, 3, '0', STR_PAD_LEFT);
        } while (Patient::where('num_matricule', $matricule)->exists());

        return $matricule;
    }

    /**
     * GET ALL
     */
    public function index()
    {
        $patients = Patient::orderBy('nom')->get();
        return response()->json([
            'status' => 200,
            'data' => $patients
        ], 200);
    }

    /**
     * GET SINGLE
     */
    public function show(int $id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return response()->json([
                'status' => 404,
                'message' => 'Patient introuvable.'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'patient' => $patient
        ], 200);
    }

    /**
     * STORE
     */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'sexe' => 'required|in:M,F',
            'email' => 'required|email:rfc,dns|unique:patients,email',
            'mot_de_passe' => 'required|min:6',
            'date_naissance' => 'required|date',
            'telephone' => 'required|string|max:20',
            'adresse' => 'required|string|max:175',
            'statut' => 'required|in:Actif,Inactif'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors()
            ], 400);
        }

        Patient::create([
            'num_matricule' => $this->genererMatricule(),
            'nom' => trim($request->nom),
            'prenom' => trim($request->prenom),
            'sexe' => trim($request->sexe),
            'email' => trim($request->email),
            'mot_de_passe' => Hash::make(trim($request->mot_de_passe)),
            'date_naissance' => trim($request->date_naissance),
            'telephone' => trim($request->telephone),
            'adresse' => trim($request->adresse),
            'statut' => trim($request->statut) ?? 'Actif'
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Patient enregistré'
        ], 200);
    }

    /**
     * UPDATE
     */
    public function update(Request $request, int $id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return response()->json([
                'status' => 404,
                'message' => 'Patient introuvable.'
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'sexe' => 'required|in:M,F',
            'email' => [
                'required',
                'email:rfc,dns',
                Rule::unique('patients')->ignore($patient->id)
            ],
            'date_naissance' => 'required|date',
            'telephone' => 'required|string|max:20',
            'adresse' => 'required|string|max:175',
            'statut' => 'required|in:Actif,Inactif'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors()
            ], 400);
        }

        $patient->update($request->only([
            'nom',
            'prenom',
            'sexe',
            'email',
            'date_naissance',
            'telephone',
            'adresse',
            'statut'
        ]));

        if ($request->filled('mot_de_passe') && $request->mot_de_passe < 6) {
            $patient->mot_de_passe = Hash::make($request->mot_de_passe);
            $patient->save();
        }

        return response()->json([
            'message' => 'Patient mis à jour',
            'status' => 200
        ], 200);
    }

    /**
     * DELETE
     */
    public function destroy(int $id)
    {
        $patient = Patient::find($id);

        if (!$patient) {
            return response()->json([
                'message' => 'Patient introuvable',
                'status' => 404
            ], 404);
        }

        $patient->delete();

        return response()->json([
            'message' => 'Patient supprimé',
            'status' => 200
        ], 200);
    }
}
