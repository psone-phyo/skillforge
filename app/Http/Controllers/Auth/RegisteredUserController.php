<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Instructor;
use App\Models\Proposal;
use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        $categories = Category::all();
        return Inertia::render('auth/Register', compact('categories'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'interest_id' => 'required',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 1
        ]);
        if ($request->role == Role::ID_STUDENT) {
            $student = Student::create([
                'user_id' => $user->id,
                'interest_id' => $request->interest_id
            ]);

            if ($student) {
                $user->assignRole(Role::ID_STUDENT);
            }
        } else {
            $instructor = Instructor::create([
                'user_id' => $user->id,
            ]);
            if ($instructor) {
                $cvPath = null;
                if ($request->hasFile('cv')) {
                    $cvPath = $request->file('cv')->store('cvs', 'r2'); // store in "cvs/" folder on R2
                }

                Proposal::create([
                    'proposal' => $request->proposal,
                    'cv' => config('filesystem.disks.r2.url').$cvPath,
                    'instructor_id' => $user->id, // optional
                ]);
            }
        }

        event(new Registered($user));

        // Auth::login($user);

        return redirect('/send/otp/'.$user->id);
    }
}
