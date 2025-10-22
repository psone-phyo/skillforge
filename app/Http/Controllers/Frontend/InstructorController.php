<?php

namespace App\Http\Controllers\Frontend;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Instructor;
use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstructorController extends Controller
{
    public function send(Request $request)
    {
        $cvPath = null;
        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('cvs', 'r2'); // store in "cvs/" folder on R2
        }

        $proposal = Proposal::create([
            'proposal' => $request->proposal,
            'cv' => config('filesystem.disks.r2.url') . $cvPath,
            'instructor_id' => Auth::id(),
        ]);

        Instructor::create([
            'user_id' => Auth::id(),
            'proposal_id' => $proposal->id,
        ]);

        Auth::user()->assignRole(Role::ID_TEACHER);
        return back();
    }
}
