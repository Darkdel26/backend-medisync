<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Medecin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MedecinController extends Controller
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

        $medecin = Medecin::where('email', $request->email)->first();

        if (!$medecin || !Hash::check($request->mot_de_passe, $medecin->mot_de_passe)) {
            return response()->json([
                'status' => 401,
                'errors' => 'Les identifiants fournis sont incorrects.'
            ], 401);
        }

        if ($medecin->statut == 'Inactif') {
            return response()->json([
                'status' => 401,
                'errors' => 'Votre compte est inactif'
            ], 401);
        }

        $token = $medecin->createToken('medecin-token', ['medecin'])->plainTextToken;
        $medecin['role'] = 'medecin';

        return response()->json([
            'status' => 200,
            'medecin' => $medecin,
            'token' => $token
        ], 200);
    }

    public function logout(int $id)
    {
        $medecin = Medecin::where('id', $id)->first();
        if ($medecin) {
            $medecin->tokens()->delete();
        }
    }

    // GET ALL
    public function index()
    {
        $medecins = Medecin::orderBy('nom')->get();
        return response()->json([
            'status' => 200,
            'data' => $medecins
        ], 200);
    }

    // GET SINGLE
    public function show(int $id)
    {
        $medecin = Medecin::find($id);

        if (!$medecin) {
            return response()->json([
                'status' => 404,
                'message' => 'Médecin introuvable'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'data' => $medecin
        ], 200);
    }

    // 🟡 STORE
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email:rfc,dns|unique:medecins',
            'mot_de_passe' => 'required|min:6',
            'specialite' => 'required|string|max:150',
            'telephone' => 'required|string|max:20',
            'num_ordre' => 'required|unique:medecins',
            'statut' => 'required|in:Actif,Inactif'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors()
            ], 400);
        }

        Medecin::create([
            'nom' => trim($request->nom),
            'prenom' => trim($request->prenom),
            'email' => trim($request->email),
            'mot_de_passe' => Hash::make(trim($request->mot_de_passe)),
            'specialite' => trim($request->specialite),
            'telephone' => trim($request->telephone),
            'num_ordre' => trim($request->num_ordre),
            'statut' => trim($request->statut) ?? 'Actif'
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Médecin enregistré',
        ], 200);
    }

    // UPDATE
    public function update(Request $request, int $id)
    {
        $medecin = Medecin::find($id);

        if (!$medecin) {
            return response()->json([
                'message' => 'Médecin introuvable'
            ], 404);
        }

        $validate = Validator::make($request->all(), [
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                Rule::unique('medecins')->ignore($medecin->id),
            ],
            'specialite' => 'required|string|max:150',
            'telephone' => 'required|string|max:20',
            'num_ordre' => [
                'required',
                Rule::unique('medecins')->ignore($medecin->id),
            ],
            'statut' => 'required|in:Actif,Inactif',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validate->errors()
            ], 400);
        }

        $medecin->update([
            'nom' => trim($request->nom) ?? $medecin->nom,
            'prenom' => trim($request->prenom) ?? $medecin->prenom,
            'email' => trim($request->email) ?? $medecin->email,
            'specialite' => trim($request->specialite) ?? $medecin->specialite,
            'telephone' => trim($request->telephone) ?? $medecin->telephone,
            'num_ordre' => trim($request->num_ordre) ?? $medecin->num_ordre,
            'statut' => trim($request->statut) ?? $medecin->statut
        ]);

        if ($request->filled('mot_de_passe') && $request->mot_de_passe < 6) {
            $medecin->mot_de_passe = Hash::make(trim($request->mot_de_passe));
        }

        return response()->json([
            'message' => 'Médecin mis à jour',
            'status' => 200
        ], 200);
    }

    //DELETE
    public function destroy(int $id)
    {
        $medecin = Medecin::find($id);

        if (!$medecin) {
            return response()->json([
                'status' => 404,
                'message' => 'Médecin introuvable'
            ], 404);
        }

        $medecin->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Médecin supprimé'
        ], 200);
    }

    public function prochainsRdv()
    {
        $medecin = Medecin::find(Auth::id());

        if (!$medecin) {
            return response()->json([
                'status' => 404,
                'message' => 'Médecin introuvable'
            ], 404);
        }

        $rdvs = Consultation::select(
            'patient_id',
            'prochain_rdv'
        )
            ->with([
                'patient:id,nom,prenom,telephone,num_matricule'
            ])
            ->where('medecin_id', $medecin->id)
            ->whereNotNull('prochain_rdv')
            ->where('prochain_rdv', '>=', now())
            ->orderBy('prochain_rdv')
            ->get();

        return response()->json([
            'status' => 200,
            'rdvs' => $rdvs
        ], 200);
    }
}
