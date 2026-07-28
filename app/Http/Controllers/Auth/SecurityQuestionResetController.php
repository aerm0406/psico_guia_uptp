<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SecurityQuestionResetController extends Controller
{
    /**
     * Display the view to enter Cedula.
     */
    public function createCedula()
    {
        return view('auth.forgot-password-cedula');
    }

    /**
     * Process Cedula and redirect to security questions.
     */
    public function storeCedula(Request $request)
    {
        $request->validate([
            'cedula' => ['required', 'string'],
        ]);

        $user = User::obtenerUsuarioPorCedula($request->cedula);

        if (!$user) {
            return back()->withErrors(['cedula' => 'No se encontró ningún usuario con esta cédula.']);
        }

        // Check if user has security questions
        if (empty($user->pregunta_seguridad_1) || empty($user->pregunta_seguridad_2) || empty($user->pregunta_seguridad_3)) {
            return back()->withErrors(['cedula' => 'El usuario no tiene preguntas de seguridad configuradas. Intente por correo electrónico.']);
        }

        // Randomly pick 2 distinct indices from 1, 2, 3
        $indices = [1, 2, 3];
        shuffle($indices);
        $selectedIndices = array_slice($indices, 0, 2);

        $questions = [
            $selectedIndices[0] => $user->{"pregunta_seguridad_" . $selectedIndices[0]},
            $selectedIndices[1] => $user->{"pregunta_seguridad_" . $selectedIndices[1]},
        ];

        // Store in session
        $request->session()->put('security_reset', [
            'user_id' => $user->id,
            'email' => $user->email,
            'cedula' => $user->cedula,
            'questions' => $questions,
            'indices' => $selectedIndices
        ]);

        return redirect()->route('password.questions');
    }

    /**
     * Display the view to answer security questions.
     */
    public function createAnswers(Request $request)
    {
        $resetData = $request->session()->get('security_reset');
        
        if (!$resetData) {
            return redirect()->route('password.request');
        }

        return view('auth.security-questions', [
            'questions' => $resetData['questions'],
            'indices' => $resetData['indices']
        ]);
    }

    /**
     * Process security answers.
     */
    public function storeAnswers(Request $request)
    {
        $resetData = $request->session()->get('security_reset');
        
        if (!$resetData) {
            return redirect()->route('password.request');
        }

        $indices = $resetData['indices'];
        
        $rules = [];
        foreach ($indices as $index) {
            $rules["respuesta_$index"] = ['required', 'string'];
        }
        
        $request->validate($rules);

        // Fetch user again to verify answers
        $user = User::obtenerUsuarioPorCedula($resetData['cedula']);

        if (!$user) {
            $request->session()->forget('security_reset');
            return redirect()->route('password.request')->withErrors(['cedula' => 'Error de usuario.']);
        }

        $allCorrect = true;
        foreach ($indices as $index) {
            $userAnswerHash = $user->{"respuesta_seguridad_" . $index};
            $inputAnswer = $request->input("respuesta_$index");
            
            if (!User::verificarRespuestaSeguridad($userAnswerHash, $inputAnswer)) {
                $allCorrect = false;
                break;
            }
        }

        if (!$allCorrect) {
            return back()->withErrors(['respuestas' => 'Una o más respuestas son incorrectas.']);
        }

        // Correct! Generate token and redirect to reset password
        $token = Str::random(60);
        User::crearTokenRecuperacion($user->email, $token);

        $request->session()->forget('security_reset');

        return redirect()->route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);
    }
}
